<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\ArchiveProcessor;

use Piwik\Archive;
use Piwik\Cache;
use Piwik\Config;
use Piwik\DataAccess\ArchiveSelector;
use Piwik\Date;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Log;

/**
 * This class uses PluginsArchiver class to trigger data aggregation and create archives.
 */
class Loader
{
    /**
     * Is the current archive temporary. ie.
     * - today
     * - current week / month / year
     */
    protected $temporaryArchive;

    /**
     * Idarchive in the DB for the requested archive
     *
     * @var int
     */
    protected $idArchive;

    /**
     * @var Parameters
     */
    protected $params;

    public function __construct(Parameters $params)
    {
	$period = $params->getPeriod();
	Log::Debug("ArchiveProcessor/Loader:construct period:%s start:%s", $period->getLabel(), $period->getDateStart()->getDatetime());
        $this->params = $params;
    }

    /**
     * @return bool
     */
    protected function isThereSomeVisits($visits)
    {
        return $visits > 0;
    }

    /**
     * @return bool
     */
    protected function mustProcessVisitCount($visits)
    {
        return $visits === false;
    }

    public function prepareArchive($pluginName)
    {
        $this->params->setRequestedPlugin($pluginName);

        list($idArchive, $visits, $visitsConverted) = $this->loadExistingArchiveIdFromDb();
        if (!empty($idArchive)) {
            return $idArchive;
        }

        list($visits, $visitsConverted) = $this->prepareCoreMetricsArchive($visits, $visitsConverted);
        list($idArchive, $visits) = $this->prepareAllPluginsArchive($visits, $visitsConverted);

        if ($this->isThereSomeVisits($visits)) {
            return $idArchive;
        }
        return false;
    }

    /**
     * Prepares the core metrics if needed.
     *
     * @param $visits
     * @return array
     */
    protected function prepareCoreMetricsArchive($visits, $visitsConverted)
    {
        $createSeparateArchiveForCoreMetrics = $this->mustProcessVisitCount($visits)
                                && !$this->doesRequestedPluginIncludeVisitsSummary();

        if ($createSeparateArchiveForCoreMetrics) {
            $requestedPlugin = $this->params->getRequestedPlugin();

            $this->params->setRequestedPlugin('VisitsSummary');

            $pluginsArchiver = new PluginsArchiver($this->params, $this->isArchiveTemporary());
            $metrics = $pluginsArchiver->callAggregateCoreMetrics();
            $pluginsArchiver->finalizeArchive();

            $this->params->setRequestedPlugin($requestedPlugin);

            $visits = $metrics['nb_visits'];
            $visitsConverted = $metrics['nb_visits_converted'];
        }

        return array($visits, $visitsConverted);
    }

    protected function prepareAllPluginsArchive($visits, $visitsConverted)
    {
        $pluginsArchiver = new PluginsArchiver($this->params, $this->isArchiveTemporary());

        if ($this->mustProcessVisitCount($visits)
            || $this->doesRequestedPluginIncludeVisitsSummary()
        ) {
            $metrics = $pluginsArchiver->callAggregateCoreMetrics();
            $visits = $metrics['nb_visits'];
            $visitsConverted = $metrics['nb_visits_converted'];
        }

        if ($this->isThereSomeVisits($visits)
            || $this->shouldArchiveForSiteEvenWhenNoVisits()
        ) {
            $pluginsArchiver->callAggregateAllPlugins($visits, $visitsConverted);
        }

        $idArchive = $pluginsArchiver->finalizeArchive();

        return array($idArchive, $visits);
    }

    protected function doesRequestedPluginIncludeVisitsSummary()
    {
        $processAllReportsIncludingVisitsSummary =
                Rules::shouldProcessReportsAllPlugins($this->params->getIdSites(), $this->params->getSegment(), $this->params->getPeriod()->getLabel());
        $doesRequestedPluginIncludeVisitsSummary = $processAllReportsIncludingVisitsSummary
                                                        || $this->params->getRequestedPlugin() == 'VisitsSummary';
        return $doesRequestedPluginIncludeVisitsSummary;
    }

    protected function isArchivingForcedToTrigger()
    {
        $period = $this->params->getPeriod()->getLabel();
        $debugSetting = 'always_archive_data_period'; // default

        if ($period == 'day') {
            $debugSetting = 'always_archive_data_day';
        } elseif ($period == 'range') {
            $debugSetting = 'always_archive_data_range';
        }

        return (bool) Config::getInstance()->Debug[$debugSetting];
    }

    /**
     * Returns the idArchive if the archive is available in the database for the requested plugin.
     * Returns false if the archive needs to be processed.
     *
     * @return array
     */
    protected function loadExistingArchiveIdFromDb()
    {
        $noArchiveFound = array(false, false, false);

        // see isArchiveTemporary()
        $minDatetimeArchiveProcessedUTC = $this->getMinTimeArchiveProcessed();

        if ($this->isArchivingForcedToTrigger()) {
            return $noArchiveFound;
        }

        $idAndVisits = ArchiveSelector::getArchiveIdAndVisits($this->params, $minDatetimeArchiveProcessedUTC);

        if (!$idAndVisits) {
            return $noArchiveFound;
        }

        return $idAndVisits;
    }

    /**
     * [Thangnt 2016-11-11]
     * @TODO: Review this function again, the problem with archiving hours before 8 am
     * is actually problem of VisitTime plugin.
     * 
     * Returns the minimum archive processed datetime to look at. Only public for tests.
     *
     * @return int|bool  Datetime timestamp, or false if must look at any archive available
     */
    protected function getMinTimeArchiveProcessed()
    {
        /**
         * [Thangnt 2016-11-16]
         * @todo It seems that the period passed into params is set wrong timezone in the first place
         */
	//Log::Debug("ArchiveProcessor/Loader:getMinTimeArchiveProcessed");
        $period = $this->params->getPeriod();
        
        //[Thangnt 2016-09-21] For Hour return the end time of that hour.
        /**
         * [Thangnt 2016-11-16] This is kind of lazy solution, but it's much 
         * easier to eliminate the "temporary" definition for 'hour' period 
         * archive.
         * When check temporary for 'hour' archive, there's problem with 
         * getDateStartUTC below in determineIfArchivePermanent.
         */
//        if ($period->getLabel()=== 'hour'){
//            $this->temporaryArchive = false;
//            $endHourTimeStamp = $period->getDateEnd()->getTimestampUTC();
//            if($endHourTimeStamp <= now) {
//                return $endHourTimeStamp;
//            }
//        }

        //Thangnt 2016-11-16 getDateEnd always give 23:59:59 even when period is hour
        //Log::debug("Loader::%s start:%s, end:%s.", __FUNCTION__, $period->getDateStart()->toString("Y-m-d H:i:s"), $period->getDateEnd()->toString("Y-m-d H:i:s"));
        
        if ($period->getLabel()=== 'hour') {
            $endDateTimestamp = self::determineIfArchivePermanent($this->params->getDateEnd(), $isHour = true);
        } else {
            $endDateTimestamp = self::determineIfArchivePermanent($this->params->getDateEnd());
        }
//        $endDateTimestamp = self::determineIfArchivePermanent($this->params->getDateEnd());
        $isArchiveTemporary = ($endDateTimestamp === false);
        $this->temporaryArchive = $isArchiveTemporary;

        if ($endDateTimestamp) {
            // Permanent archive
            return $endDateTimestamp;
        }

        $dateStart = $this->params->getDateStart();
       //$dateEnd = $this->params->getDateEnd();
//        $period    = $this->params->getPeriod();
        $segment   = $this->params->getSegment();
        $site      = $this->params->getSite();
        
        /**
         * [Thangnt 2016-11-16]
         * Moved this block to Piwik\Archive::cacheArchiveIdsAfterLaunching.
         * This function called to check the existing archives and determine
         * whether that should be permanent or temporary, which are managed to
         * calculated properly in term of time and validation. 
         * Put this process here cause all 'hour' (include future) in a day to be 
         * calculated and archives always marked 'temporary done' (value=3). 
         * Basically that is wrong behavior of archiving.
         */
        
//        if ($period->getLabel() === 'hour') {
//            $now = Date::now();
//            $endOfPeriod = $period->getDateEnd()->setTimezone($site->getTimezone());
//            
//            $now_ts = $now->getTimestamp();
//            $dateEnd_ts = $endOfPeriod->getTimestamp();
//            $timeback = $now_ts - $dateEnd_ts;
////                     Log::debug("ArchiveProcessor/Loader:getMinTimeArchiveProcessed: now:%s and getDateEnd: %s timeBack:%s",
////                                $now->getDatetime(), $endOfPeriod->getDatetime(), $timeback);
//            $config = Config::getInstance()->General;
//            $time_limit = 3600;
//  
//            /**
//             * [Thangnt 2016-11-15]
//             * Set default values. 
//             * This prevents errors by missed config in config.php.ini as well
//             */
//            $my_period = 60;
//            $my_nperiod_back = 2;
//            
//            if (isset($config['my_period'])) {
//                $my_period = $config['my_period'];
//            } 
//            if (isset($config['my_nperiod_back'])) {
//                $my_nperiod_back = $config['my_nperiod_back'];
//            } 
//            if (isset($time_limit)) {
//                $time_limit = $my_period * $my_nperiod_back;
//            }	
//            //Log::Debug("time_limit:%s", $time_limit);
//            if ($now_ts < $dateEnd_ts || $timeback > $time_limit) {
////                        Log::debug("ArchiveProcessor/Loader:getMinTimeArchiveProcessed %s (%s) skipped, archive is for future hours.",
////                            $period->getLabel(), $period->getPrettyString());
//                return $endDateTimestamp;
//            }
//        }

        // Temporary archive
        return Rules::getMinTimeProcessedForTemporaryArchive($dateStart, $period, $segment, $site);
    }

    protected static function determineIfArchivePermanent(Date $dateEnd, $isHour = false)
    {
        //both of these are UTC
        $now = time();
        
        /**
         * [Thangnt 2016-11-16] The original has a serious problem with this getDateEndUTC()
         * when involve 'hour' period. The end is 23:59:59 for what ever the hour.
         * 
         * @todo similar check the whole core.
         */
        if ($isHour) {
            $endTimestampUTC = strtotime($dateEnd->getDateEndUTC($format="Y-m-d H:i:s"));
        } else {
            $endTimestampUTC = strtotime($dateEnd->getDateEndUTC());
        }
            
        Log::debug("Loader::%s now is: %s, and endTimestampUTC: %s.", __FUNCTION__, date("Y-m-d H:i:s", $now), date("Y-m-d H:i:s", $endTimestampUTC)); 
        
        if ($endTimestampUTC <= $now) {
            return $endTimestampUTC;
    	} 
        return false;
    }

    protected function isArchiveTemporary()
    {
        if (is_null($this->temporaryArchive)) {
            throw new \Exception("getMinTimeArchiveProcessed() should be called prior to isArchiveTemporary()");
        }

        return $this->temporaryArchive;
    }

    private function shouldArchiveForSiteEvenWhenNoVisits()
    {
        $idSitesToArchive = $this->getIdSitesToArchiveWhenNoVisits();
        return in_array($this->params->getSite()->getId(), $idSitesToArchive);
    }

    private function getIdSitesToArchiveWhenNoVisits()
    {
        $cache = Cache::getTransientCache();
        $cacheKey = 'Archiving.getIdSitesToArchiveWhenNoVisits';

        if (!$cache->contains($cacheKey)) {
            $idSites = array();

            // leaving undocumented unless decided otherwise
            Piwik::postEvent('Archiving.getIdSitesToArchiveWhenNoVisits', array(&$idSites));

            $cache->save($cacheKey, $idSites);
        }

        return $cache->fetch($cacheKey);
    }
}
