<?php
/**
 *[Thangnt 2016-09-09]
 * New Period - Hour, used mainly by Archiver to aggregate a temporary archive 
 * data of every hours. This mechanism is supposed to profoundly improve the 
 * performance of the Archiver.
 * This period is not applied to End-user facing data and is not used to 
 * query archive data for Reports, so metadata and human-oriented description
 * (e.g. translator) are reduced to saving development effort. 
 * 
 * String format for this period will be "Y-m-d H:i:s"
 * This string when input from command line (console core:archive)
 * will take in the format "Y-m-d x" to indicate the range 
 * "x:00:00" to "x:59:59" (x goes from 0 to 23)
 * 
 * TODO: Not yet support multiple hours range selection so it's a TODO here.
 * 
 * The Hour class mess up the entire definition about subperiod, Start, End
 * of the Period Class. May need to define the new method like get start time 
 * end time and synchronize the LogAggregator and ArchiveProcessor for adaptation.
 * 
 * 
 * [2016-09-20]
 * Refactor this class to make hourStart and hourEnd unify with the concept
 * of hourly archiving.
 * 
 * Remember that for multiple Hour range, the end Hour is actually the start time
 * of the last hour, e.g end Hour of a day is 23:00:00 not 23:59:59
 * 
 */
namespace Piwik\Period;

use Exception;
use Piwik\Date;
use Piwik\Period;

/**
 */
class Hour extends Period
{
    const PERIOD_ID = 6;

    protected $label = 'hour';
    
    
    /**
     * [Thangnt 2016-11-15]
     * @--todo: Call gmdate actually return an UTC time
     * Notice that Date::factory is also an UTC. 
     * @todo Recheck every places that call getDateStart of Period.
     * 
     * 2016-09-20 
     * Override the getDateStart to "quantize" the 
     * timestamp into the hour frame: n:00:00-n:59:59.
     * 
     * @return Date timestamp of the start of the hour
     */
    public function getDateStart() {
        $date = parent::getDateStart();
        //$hourStart = gmdate("Y-m-d H:00:00", $date->getTimestamp());
        //$hourStart = gmdate("Y-m-d H:i:00", $date->getTimestamp());
        
        //Use date to generate a string from timestamp. The date() 
        // give string in server timezone while gmdate() give UTC
        $hourStart = date("Y-m-d H:i:00", $date->getTimestamp());
        
        $hourStart = Date::factory($hourStart);
        return $hourStart;
    }
    
    /**
     * [Thang 2016-09-12] DateStart actually is a Date object
     * so it hold time as well.
     * 
     * @Override Period::getDateEnd()
     * Plus 59m 59s to result
     * 
     * @return Date
     */
    public function getDateEnd() {
        //parent::getDateEnd();
        
        
        /**
         * [Thangnt 2016-11-16] 1:00 am 
         * @todo: $config['my_period'] checking is duplicated all over the places
         * need a centralized default value.
         */
        $myPeriod = \Piwik\Config::getInstance()->General['my_period'];
        $toEnd = 1 - (int)$myPeriod;
   
        $start = $this->getDateStart();
        //\Piwik\Log::debug("Hour::%s dateStart: %s and toEnd = %s", __FUNCTION__, $start->toString("Y-m-d H:i:s"), $toEnd);
        //$end = $start->subSeconds($toEnd);
        //\Piwik\Log::debug("In Hour getDateEnd: dateEnd: %s, %s, %s", $end->isValidForHour(), $end->toString(), $end->toString("Y-m-d")); 
        return $start->subSeconds($toEnd);
        //return $start->subHour(-1)->subSeconds(1);
    }
    
    /**
     * 2016-09-19
     * 
     * Returns the 1 hour range string comprising two timestamps 
     *
     * @return string eg, `'2012-01-01 11:00:00,2012-01-01 11:59:59'`.
     */
    public function getRangeString()
    {
        $dateStart = $this->getDateStart();
        $dateEnd   = $this->getDateEnd();

        return $dateStart->toString("Y-m-d H:i:s") . "," . $dateEnd->toString("Y-m-d H:i:s");
    }   
    
    /**
     * 
     * @return string
     */
    public function getPrettyString()
    {
        $out = $this->getDate()->toString("Y-m-d H:i:s");
        return $out;
        //return $this->date->toString("Y-m-d H:i:s")." to ".$this->date->subHour(-1)->subSeconds(1)->toString("H:i:s");
    }

    /**
     * This return a string for this period in local language 
     * Although implemented as an abstract method, this would 
     * never be called.
     * @return string
     */
    public function getLocalizedShortString()
    {
        return $this->getDate()->getLocalized(Date::DATETIME_FORMAT);
    }

    /**
     * Returns the day of the period as a localized long string
     *(no need for this as well
     * @return string
     */
    public function getLocalizedLongString()
    {
        return $this->getDate()->getLocalized(Date::DATETIME_FORMAT_LONG);
    }

    /**
     * Returns the number of subperiods
     * Always 0, in that case
     *
     * @return int
     */
    public function getNumberOfSubperiods()
    {
        return 0;
    }

    /**
     * Adds a subperiod
     * Not supported for day periods
     *
     * @param $time
     * @throws Exception
     */
    public function addSubperiod($time)
    {
        throw new Exception("Adding a subperiod is not supported for Hour");
    }

    /**
     * Returns the time of the day of the period in the given format
     * Same as {@link getPrettyString}
     * 
     * @param string $format
     * @return string
     */
    public function toString($format = "Y-m-d H:i:s")
    {
        
        return $this->date->toString($format);
    }

    /**
     * Returns the current period as a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    public function getImmediateChildPeriodLabel()
    {
        return null;
    }

    public function getParentPeriodLabel()
    {
        return 'day';
    }
}
