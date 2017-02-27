<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Archive;

use Piwik\Archive\ArchiveInvalidator\InvalidationResult;
use Piwik\CronArchive\SitesToReprocessDistributedList;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\Model;
use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\CoreAdminHome\Tasks\ArchivesToPurgeDistributedList;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Period;
use Piwik\Segment;
use Piwik\Log;

/**
 * [Thangnt 2016-10-27] Modify this class to support invalidating hour period
 * 
 * 
 * Service that can be used to invalidate archives or add archive references to a list so they will
 * be invalidated later.
 *
 * Archives are put in an "invalidated" state by setting the done flag to `ArchiveWriter::DONE_INVALIDATED`.
 * This class also adds the archive's associated site to the a distributed list and adding the archive's year month to another
 * distributed list.
 *
 * CronArchive will reprocess the archive data for all sites in the first list, and a scheduled task
 * will purge the old, invalidated data in archive tables identified by the second list.
 *
 * Until CronArchive, or browser triggered archiving, re-processes data for an invalidated archive, the invalidated
 * archive data will still be displayed in the UI and API.
 *
 * ### Deferred Invalidation
 *
 * Invalidating archives means running queries on one or more archive tables. In some situations, like during
 * tracking, this is not desired. In such cases, archive references can be added to a list via the
 * rememberToInvalidateArchivedReportsLater method, which will add the reference to a distributed list
 *
 * Later, during Piwik's normal execution, the list will be read and every archive it references will
 * be invalidated.
 */
class ArchiveInvalidator
{
    private $rememberArchivedReportIdStart = 'report_to_invalidate_';

    /**
     * @var Model
     */
    private $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function rememberToInvalidateArchivedReportsLater($idSite, Date $date)
    {
        $key   = $this->buildRememberArchivedReportId($idSite, $date->toString());
        $value = Option::get($key);

        // we do not really have to get the value first. we could simply always try to call set() and it would update or
        // insert the record if needed but we do not want to lock the table (especially since there are still some
        // MyISAM installations)

        if (false === $value) {
            Option::set($key, '1');
        }
    }

    public function getRememberedArchivedReportsThatShouldBeInvalidated()
    {
        $reports = Option::getLike($this->rememberArchivedReportIdStart . '%_%');

        $sitesPerDay = array();

        foreach ($reports as $report => $value) {
            $report = str_replace($this->rememberArchivedReportIdStart, '', $report);
            $report = explode('_', $report);
            $siteId = (int) $report[0];
            $date   = $report[1];

            if (empty($sitesPerDay[$date])) {
                $sitesPerDay[$date] = array();
            }

            $sitesPerDay[$date][] = $siteId;
        }

        return $sitesPerDay;
    }

    private function buildRememberArchivedReportId($idSite, $date)
    {
        $id  = $this->buildRememberArchivedReportIdForSite($idSite);
        $id .= '_' . trim($date);

        return $id;
    }

    private function buildRememberArchivedReportIdForSite($idSite)
    {
        return $this->rememberArchivedReportIdStart . (int) $idSite;
    }

    public function forgetRememberedArchivedReportsToInvalidateForSite($idSite)
    {
        $id = $this->buildRememberArchivedReportIdForSite($idSite) . '_%';
        Option::deleteLike($id);
    }

    /**
     * @internal
     */
    public function forgetRememberedArchivedReportsToInvalidate($idSite, Date $date)
    {
        $id = $this->buildRememberArchivedReportId($idSite, $date->toString());

        Option::delete($id);
    }

    /**
     * [Thangnt 2016-11-10]
     * The periodDates array determines which periods' archive will be invalidate
     * => Need to make it right for Hour.
     * 
     * @param $idSites int[]
     * @param $dates Date[]
     * @param $period string
     * @param $segment Segment
     * @param bool $cascadeDown
     * @return InvalidationResult
     * @throws \Exception
     */
    public function markArchivesAsInvalidated(array $idSites, array $dates, $period, Segment $segment = null, $cascadeDown = false)
    {
        $invalidationInfo = new InvalidationResult();

        // @Thangnt: remove the dates that log data has been purged,
        // this *seldom* (never??) occurs in our system. Instead need to find out
        // how to retrieve the rotated log to re-calculate the archive
        $datesToInvalidate = $this->removeDatesThatHaveBeenPurged($dates, $invalidationInfo);

        if (empty($period)) {
            // if the period is empty, we don't need to cascade in any way, since we'll remove all periods
            $periodDates = $this->getDatesByYearMonthAndPeriodType($dates);
        } else {
            $periods = $this->getPeriodsToInvalidate($datesToInvalidate, $period, $cascadeDown);
            
            // get Year Month for archive table name
            $periodDates = $this->getPeriodDatesByYearMonthAndPeriodType($periods);
        }

        $periodDates = $this->getUniqueDates($periodDates);
        
        $pD = serialize($periodDates);
//        $pD = implode (', ', $pD);
        Log::debug("ArchiveInvalidator::markArchivesAsInvalidated: periodDates to invalidate: $pD.");
        // @Thangnt: the real function that invalides archive
        $this->markArchivesInvalidated($idSites, $periodDates, $segment);

        $yearMonths = array_keys($periodDates);
        $this->markInvalidatedArchivesForReprocessAndPurge($idSites, $yearMonths);

        foreach ($idSites as $idSite) {
            foreach ($dates as $date) {
                $this->forgetRememberedArchivedReportsToInvalidate($idSite, $date);
            }
        }

        return $invalidationInfo;
    }

    /**
     * @param string[][][] $periodDates
     * @return string[][][]
     */
    private function getUniqueDates($periodDates)
    {
        $result = array();
        foreach ($periodDates as $yearMonth => $periodsByYearMonth) {
            foreach ($periodsByYearMonth as $periodType => $periods) {
                $result[$yearMonth][$periodType] = array_unique($periods);
            }
        }
        return $result;
    }

    /**
     * @param Date[] $dates
     * @param string $periodType
     * @param bool $cascadeDown
     * @return Period[]
     */
    private function getPeriodsToInvalidate($dates, $periodType, $cascadeDown)
    {
        $periodsToInvalidate = array();

        foreach ($dates as $date) {
            if ($periodType == 'range') {
                $date = $date . ',' . $date;
            }

//            echo "From getPeriodsToInvalidate in ArchiveInvalidator:\n";
//            echo "The date: $date and period type is: $periodType \n ****\n";
            
            $period = Period\Factory::build($periodType, $date);
            $periodsToInvalidate[] = $period;

            // [Thangnt 2016-10-27] 
//            $e = new \Exception;
//            var_dump($e->getTraceAsString());
            
            if ($cascadeDown) {
                $periodsToInvalidate = array_merge($periodsToInvalidate, $period->getAllOverlappingChildPeriods());
            }

            // [Thangnt 2016-10-27] In case of month and week, build year
            if ($periodType != 'year'
                && $periodType != 'range'
            ) {
                $periodsToInvalidate[] = Period\Factory::build('year', $date);
            }
        }

        return $periodsToInvalidate;
    }

    /**
     * [Thangnt 2016-11-10] Return full Year-Month-Day for hour period (temp table)
     * 
     * @param Period[] $periods
     * @return string[][][]
     */
    private function getPeriodDatesByYearMonthAndPeriodType($periods)
    {
        $result = array();
        foreach ($periods as $period) {
            $date = $period->getDateStart();
            $periodType = $period->getId();

            if ($period->getLabel() === 'hour') {
                $yearMonth = ArchiveTableCreator::getTableTempDateFromHour($date);
            } else {
                $yearMonth = ArchiveTableCreator::getTableMonthFromDate($date);
            }
            $result[$yearMonth][$periodType][] = $date->toString();
        }
        return $result;
    }

    /**
     * [Thangnt 2016-11-10]
     * This needs to support Hour periods with temp tables.
     * Case 1: Input as all period and dates is HOUR
     * Case 2: Input as all period and dates is DAY
     * 14:11 I think this is ok, an array of month and date returned with period is NULL 
     *        just think how to apply this to temp table
     * 
     * Called when deleting all periods.
     *
     * @param Date[] $dates
     * @return string[][][]
     */
    private function getDatesByYearMonthAndPeriodType($dates)
    {
        $result = array();
        foreach ($dates as $date) {
            $yearMonth = ArchiveTableCreator::getTableMonthFromDate($date);
            $result[$yearMonth][null][] = $date->toString();

            // since we're removing all periods, we must make sure to remove year periods as well.
            // this means we have to make sure the january table is processed.
            // Thangnt: this mean Year archive will be store in January's archive table
            $janYearMonth = $date->toString('Y') . '_01';
            $result[$janYearMonth][null][] = $date->toString();
        }
        return $result;
    }

    /**
     * @param int[] $idSites
     * @param string[][][] $dates
     * @throws \Exception
     */
    private function markArchivesInvalidated($idSites, $dates, Segment $segment = null)
    {
        $archiveNumericTables = ArchiveTableCreator::getTablesArchivesInstalled($type = ArchiveTableCreator::NUMERIC_TABLE);
        
        //[Thangnt 2016-11-07]
//        $tables = implode(",", $archiveNumericTables);
//        Log::debug("ArchiveInvalidator::markArchivesInvalidated archive Tables detected:  $tables\n");
        
        foreach ($archiveNumericTables as $table) {
            $tableDate = ArchiveTableCreator::getDateFromTableName($table);
            if (empty($dates[$tableDate])) {
                continue;
            }

            $this->model->updateArchiveAsInvalidated($table, $idSites, $dates[$tableDate], $segment);
        }
    }

    /**
     * @param Date[] $dates
     * @param InvalidationResult $invalidationInfo
     * @return \Piwik\Date[]
     */
    private function removeDatesThatHaveBeenPurged($dates, InvalidationResult $invalidationInfo)
    {
        $this->findOlderDateWithLogs($invalidationInfo);

        $result = array();
        foreach ($dates as $date) {
            // we should only delete reports for dates that are more recent than N days
            if ($invalidationInfo->minimumDateWithLogs
                && $date->isEarlier($invalidationInfo->minimumDateWithLogs)
            ) {
                $invalidationInfo->warningDates[] = $date->toString();
                continue;
            }

            $result[] = $date;
            $invalidationInfo->processedDates[] = $date->toString();
        }
        return $result;
    }

    private function findOlderDateWithLogs(InvalidationResult $info)
    {
        // If using the feature "Delete logs older than N days"...
        $purgeDataSettings = PrivacyManager::getPurgeDataSettings();
        $logsDeletedWhenOlderThanDays = (int)$purgeDataSettings['delete_logs_older_than'];
        $logsDeleteEnabled = $purgeDataSettings['delete_logs_enable'];

        if ($logsDeleteEnabled
            && $logsDeletedWhenOlderThanDays
        ) {
            $info->minimumDateWithLogs = Date::factory('today')->subDay($logsDeletedWhenOlderThanDays);
        }
    }

    /**
     * @param array $idSites
     * @param array $yearMonths
     */
    private function markInvalidatedArchivesForReprocessAndPurge(array $idSites, $yearMonths)
    {
        $store = new SitesToReprocessDistributedList();
        $store->add($idSites);

        $archivesToPurge = new ArchivesToPurgeDistributedList();
        $archivesToPurge->add($yearMonths);
    }
}
