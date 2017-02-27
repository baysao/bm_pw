<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\DataAccess;

use Piwik\Common;
use Piwik\Date;
use Piwik\DbHelper;

/**
 * [Thangnt 2016-10-27] Continue to refactor this class to support 
 * prefix and name processing for temporary archive tables.
 */


class ArchiveTableCreator
{
    const NUMERIC_TABLE = "numeric";
    const BLOB_TABLE    = "blob";

    public static $tablesAlreadyInstalled = null;

    public static function getNumericTable(Date $date)
    {
        return self::getTable($date, self::NUMERIC_TABLE);
    }

    public static function getBlobTable(Date $date)
    {
        return self::getTable($date, self::BLOB_TABLE);
    }

    protected static function getTable(Date $date, $type)
    {
        /**
         * [Thangnt 2016-09-16]
         * Implement checking if Date is Hour instance and
         * get the temp table accordingly.
         *
         * !!!Make sure that the delimiters are all underscore "_"
         */
        if($date->isValidForHour()) {
            // [Thangnt 2016-10-27] Change temp table name
            $tableNamePrefix = "archive_".$type."_temp";
            $tableName = $tableNamePrefix . "_" .self::getTableTempDateFromHour($date);
            //this func. just addes the prefix of table set on config.ini
            $tableName = Common::prefixTable($tableName);
            
            //echo "From getTable in ArchiveTableCreator: tableName is $tableName\n\n";
            
        } else {
            $tableNamePrefix = "archive_" . $type;
            $tableName = $tableNamePrefix . "_" . self::getTableMonthFromDate($date);
            $tableName = Common::prefixTable($tableName);
        }

        // @Thangnt: create Table method should be the same for Hour and other period
        self::createArchiveTablesIfAbsent($tableName, $tableNamePrefix);

        return $tableName;
    }

    protected static function createArchiveTablesIfAbsent($tableName, $tableNamePrefix)
    {
        if (is_null(self::$tablesAlreadyInstalled)) {
            self::refreshTableList();
        }

        if (!in_array($tableName, self::$tablesAlreadyInstalled)) {
            
            // @Thangnt 2016-09-27: This should accept $tableNamePrefix as temp_archive...
            self::getModel()->createArchiveTable($tableName, $tableNamePrefix);
            self::$tablesAlreadyInstalled[] = $tableName;
        }
    }

    private static function getModel()
    {
        return new Model();
    }

    public static function clear()
    {
        self::$tablesAlreadyInstalled = null;
    }

    public static function refreshTableList($forceReload = false)
    {
        self::$tablesAlreadyInstalled = DbHelper::getTablesInstalled($forceReload);
    }

    /**
     * [Thangnt 2016-10-27] I think this function should return all archive and 
     * temp_archive tables, how the returned array of table names is used depends
     * on the caller. 
     * *Confirm if this is true
     * 
     * Returns all table names archive_*
     *
     * @param string $type The type of table to return. Either `self::NUMERIC_TABLE` or `self::BLOB_TABLE`.
     * @return array
     */
    public static function getTablesArchivesInstalled($type = null)
    {
        if (is_null(self::$tablesAlreadyInstalled)) {
            self::refreshTableList();
        }

        if (empty($type)) {
            $tableMatchRegex = '/archive_(numeric|blob)_/';
        } else {
            $tableMatchRegex = '/archive_' . preg_quote($type) . '_/';
        }

        $archiveTables = array();
        foreach (self::$tablesAlreadyInstalled as $table) {
            if (preg_match($tableMatchRegex, $table)) {
                $archiveTables[] = $table;
            }
        }
        return $archiveTables;
    }

    /**
     * [Thangnt 2016-10-27] This returns the date (Y-m-d) 
     * for temp tables as well
     * 
     * @param type $tableName
     * @return type
     */
    public static function getDateFromTableName($tableName)
    {
        $tableName = Common::unprefixTable($tableName);
          
        //original
        //$date = str_replace(array('archive_numeric_', 'archive_blob_'), '', $tableName);
  
        //Thangnt
        $date =  str_replace(array('archive_numeric_temp_', 'archive_blob_temp_', 'archive_numeric_', 'archive_blob_'), '', $tableName);
        
        return $date;
    }

    public static function getTableMonthFromDate(Date $date)
    {
        return $date->toString('Y_m');
    }

    /**
     * [Thangnt 2016-10-17]
     * 
     * @param Date $hour
     * @return type
     */
    public static function getTableTempDateFromHour(Date $hour)
    {
        //calling function should check for the validity of $hour
        // no checking here
        return $hour->toString('Y_m_d');
    }

    /**
     * [Thangnt 2016-10-17]
     * Them temp tableName condition may be just 'overdone' leave there anyway.
     * 
     * @param type $tableName
     * @return boolean
     */
    public static function getTypeFromTableName($tableName)
    {
        if (strpos($tableName, 'archive_numeric_') !== false ||
               strpos($tableName, 'archive_numeric_temp') !== false ) {
            return self::NUMERIC_TABLE;
        }

        if (strpos($tableName, 'archive_blob_') !== false ||
                strpos($tableName, 'archive_blob_temp') !== false) {
            return self::BLOB_TABLE;
        }

        return false;
    }
}
