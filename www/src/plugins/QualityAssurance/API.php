<?php

namespace Piwik\Plugins\QualityAssurance;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Site;
use Piwik\Metrics\Formatter;
use Piwik\API\Request;

class API extends \Piwik\Plugin\API
{

	public function overviewGetRowOne( $lastMinutes , $refreshAfterXSecs )
	{
		$audience_size  = rand(1000, 10000);
		$startup_time   = rand(1000, 10000);
		$bitrate        = rand(1000, 10000);
		$buffer_time    = rand(100, 1000);

		$formatter 		= new Formatter();
		return array(
			'audience_size'     => array(
				'value'     => $formatter->getPrettyNumber( $audience_size ),
				'metrics'   => 'audience_size',
			),
			'startup_time'     => array(
				'value'     => $formatter->getPrettyNumber( $startup_time ),
				'metrics'   => 'startup_time',
			),
			'bitrate'     => array(
				'value'     => $formatter->getPrettyNumber( $bitrate ),
				'metrics'   => 'bitrate',
			),
			'buffer_time'     => array(
				'value'     => $formatter->getPrettyNumber( $buffer_time ),
				'metrics'   => 'buffer_time',
			),
			'refreshAfterXSecs' => 5,
			'lastMinutes'       => $lastMinutes
		);
	}

	public function getGraphEvolution($idSite, $date, $period, $columns = false)
	{
		$columns = array(
			'audience',
			'startup_time',
			'bit_rate',
			'rebuffer_time',
		);

		$end    = date("Y-m-d");
		$from   = date('Y-m-d',(strtotime ( '-15 days', strtotime($end)) ));

		$graphData = array();
		for( $from; $from <= $end; $from=date('Y-m-d', (strtotime ( '+1 day', strtotime($from)))) ) {
			foreach ( $columns as $column ) {
				$graphData[ $from ][ $column ] = rand( 1000, 10000);
			}
		}
		/**
		 * Make data like
		 *
		 * array (
		 *      "2016-07-17" => array ( "request_count_200" => X, "request_count_500" => Y ),
		 *      "2016-07-18" => array ( "request_count_200" => X, "request_count_500" => Y ),
		 *      "2016-07-19" => array ( "request_count_200" => X, "request_count_500" => Y )
		 * )
		 */

		ksort($graphData);

		return DataTable::makeFromIndexedArray($graphData);
	}

	public function getFor($idSite, $period, $date, $segment = false)
	{

        $metrics = array(
            'avi',
            'mp4',
            'flv',
        );
		$result = $this->getDataExamples( $metrics );

		return $result;
	}

	public function getCon($idSite, $period, $date, $segment = false)
	{

        $metrics = array(
            'Cable',
            'Fiber',
            'Moblie',
            'DSL',
            'Others',
        );
		$result = $this->getDataExamples( $metrics, 0, 40 );

		return $result;
	}

	public function getCat($idSite, $period, $date, $segment = false)
	{

        $metrics = array(
            'Sport',
            'Comedy‎',
            'Game show',
            'Talk show',
            'Music',
        );
		$result = $this->getDataExamples( $metrics, 0, 40 );

		return $result;
	}

    public function getCountry($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::COUNTRY_RECORD_NAME, $idSite, $period, $date, $segment);

        // apply filter on the whole datatable in order the inline search to work (searches are done on "beautiful" label)
        $dataTable->filter('AddSegmentValue');
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'code'));
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', __NAMESPACE__ . '\getFlagFromCode'));
        $dataTable->filter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\countryTranslate'));

        $dataTable->queueFilter('ColumnCallbackAddMetadata', array(array(), 'logoWidth', function () { return 16; }));
        $dataTable->queueFilter('ColumnCallbackAddMetadata', array(array(), 'logoHeight', function () { return 11; }));

        return $dataTable;
    }

	public function getDataExamples( $metrics, $min = 1000, $max = 10000 )
	{
		$end    = date("Y-m-d");
		$from   = date('Y-m-d',(strtotime ( '-15 days', strtotime($end)) ));

		$graphData = array();
		for( $from; $from <= $end; $from=date('Y-m-d', (strtotime ( '+1 day', strtotime($from)))) ) {
			foreach ( $metrics as $metric ) {
				$graphData[ $from ][ $metric ] = rand($min, $max);
			}
		}
		/**
		 * Make data like
		 *
		 * array (
		 *      "2016-07-17" => array ( "request_count_200" => X, "request_count_500" => Y ),
		 *      "2016-07-18" => array ( "request_count_200" => X, "request_count_500" => Y ),
		 *      "2016-07-19" => array ( "request_count_200" => X, "request_count_500" => Y )
		 * )
		 */

		ksort($graphData);

		return DataTable::makeFromIndexedArray($graphData);
	}

}