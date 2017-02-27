<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\QoS;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Site;
use Piwik\Metrics\Formatter;

/**
 * ExampleUI API is also an example API useful if you are developing a Piwik plugin.
 *
 * The functions listed in this API are returning the data used in the Controller to draw graphs and
 * display tables. See also the ExampleAPI plugin for an introduction to Piwik APIs.
 *
 * @method static \Piwik\Plugins\ExampleUI\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
	private $trafficByIsp;
	private $overview;
	private $totalSpeedDownload;
	private $ispSpeedDownload;
	private $cacheHit;
	private $httpCode;
	private $isp;
	private $country;

	public function __construct() {
		$this->setOverview();
		$this->setTraffic();
		$this->setHttpCode();
		$this->setCacheHit();
		$this->setTotalSpeedDownload();
		$this->setIspSpeedDownload();
		$this->setIsp();
		$this->setCountry();
	}

	public function getOverview() {
		return $this->overview;
	}

	private function setOverview()
	{
		$overview = new Settings('QoS');
		$this->overview = $overview->overview->getValue();
	}

	public function getTraffic() {
		return $this->trafficByIsp;
	}

	private function setTraffic()
	{
		$traffic = new Settings('QoS');
		$this->trafficByIsp = $traffic->traffic->getValue();
	}

	public function getHttpCode() {
		return $this->httpCode;
	}

	private function setHttpCode()
	{
		$httpCode = new Settings('QoS');
		$this->httpCode = $httpCode->httpCode->getValue();
	}

	public function getCacheHit() {
		return $this->cacheHit;
	}

	private function setCacheHit()
	{
		$cacheHitSetting = new Settings('cacheHit');
		$this->cacheHit = $cacheHitSetting->cacheHit->getValue();
	}

	public function getTotalSpeedDownload() {
		return $this->totalSpeedDownload;
	}

	private function setTotalSpeedDownload()
	{
		$totalSpeedDowload = new Settings('totalSpeedDownload');
		$this->totalSpeedDownload  = $totalSpeedDowload->totalSpeedDownload->getValue();
	}

	public function getIspSpeedDownload() {
		return $this->ispSpeedDownload;
	}

	private function setIspSpeedDownload()
	{
		$ispSpeedDowload            = new Settings('ispSpeedDownload');
		$this->ispSpeedDownload     = $ispSpeedDowload->ispSpeedDownload->getValue();
	}

	public function getIsp() {
		return $this->isp;
	}

	private function setIsp()
	{
		$ispSetting = new Settings('isp');
		$this->isp  = $ispSetting->isp->getValue();
	}

	public function getCountry() {
		return $this->country;
	}

	private function setCountry()
	{
		$countrySpeedSetting = new Settings('country');
		$this->country  = $countrySpeedSetting->country->getValue();
	}

	public function buildDataBwGraph()
	{
		$columns = array('avg_speed');

		$idSite = Common::getRequestVar('idSite', 1);
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
        $nameCdn    = explode("//",$nameCdn)[1];

		$now = date("Y-m-d H:i:s");

		$params = array(
			'name'      => $nameCdn,
			'date'      => $now,
			'period'    => '24 hours', // range 24 hours
			'unit'      => 'hour',
			'type'      => 'avg_speed',
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);

		$bandwidthData = array();

		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime )
					{
						$bandwidthData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = $valueByTime['value'];
					}
				}
			}
		}
		ksort($bandwidthData);
		$graphData = array_slice($bandwidthData, -24, 24, true);
		$tmp = array();
		foreach ( $graphData as $keyTime => $valueByTime )
		{
			// $key = explode(" ", $keyTime);
			// $tmp[ $key[1]."h" ] = $valueByTime['avg_speed'];
			$tmp[ $keyTime."h" ] = $valueByTime['avg_speed'];
		}
		$graphData = $tmp;

		return DataTable::makeFromIndexedArray($graphData);
	}

	public function buildDataHttpCodeGraph()
	{
		$columns = 'request_count_200,request_count_204,request_count_206';

		$idSite = Common::getRequestVar('idSite', 1);
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
        $nameCdn    = explode("//",$nameCdn)[1];

		$lastMinutes = 2;
		$now = time();
		$before_3mins = $now - ($lastMinutes * 60);
		$date_param = date("Y-m-d H:i:s", $before_3mins).",".date("Y-m-d H:i:s", $before_3mins);
		// $date_param = date("Y-m-d H:i:s").",".date("Y-m-d H:i:s");
		$params = array(
			'name'      => $nameCdn,
			'date'      => "$date_param",
			'period'    => 'range',
			'unit'      => 'day', // range 1 minute
			'type'      => $columns,
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);

		$graphData = array();
		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime )
					{
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = (int)$valueByTime['value'];
					}
				}
			}
		}

		return DataTable::makeFromIndexedArray(current($graphData));
	}

	public function buildDataIspGraph()
	{
		$columns = 'isp_request_count_200_mobiphone,isp_request_count_200_vinaphone,isp_request_count_200_fpt,isp_request_count_200_viettel,isp_request_count_200_vnpt';

		$idSite = Common::getRequestVar('idSite', 1);
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
        $nameCdn    = explode("//",$nameCdn)[1];

		$date_param = date("Y-m-d H:i:s").",".date("Y-m-d H:i:s");
		$params = array(
			'name'      => $nameCdn,
			'date'      => "$date_param",
			'period'    => 'range',
			'unit'      => 'day', // range 1 minute
			'type'      => $columns,
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);

		$graphData = array();
		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime )
					{
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = (int)$valueByTime['value'];
					}
				}
			}
		}

		return DataTable::makeFromIndexedArray(current($graphData));
	}

	public function buildDataCountryGraph()
	{
		$columns = 'country_request_count_200_VN,country_request_count_200_US,country_request_count_200_CN';

		$idSite = Common::getRequestVar('idSite', 1);
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
        $nameCdn    = explode("//",$nameCdn)[1];

		$date_param = date("Y-m-d H:i:s").",".date("Y-m-d H:i:s");
		$params = array(
			'name'      => $nameCdn,
			'date'      => "$date_param",
			'period'    => 'range',
			'unit'      => 'day', // range 1 minute
			'type'      => $columns,
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);

		$graphData = array();
		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime )
					{
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = (int)$valueByTime['value'];
					}
				}
			}
		}

		return DataTable::makeFromIndexedArray(current($graphData));
	}

	public function overViewSpeedGraph($idSite, $metric)
	{
		if(!$idSite) {
			$idSite = Common::getRequestVar('idSite', 1);
		}

		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
        $nameCdn    = explode("//",$nameCdn)[1];

		$date_param = date("Y-m-d H:i:s").",".date("Y-m-d H:i:s");
		$params = array(
			'name'      => $nameCdn,
			'date'      => "$date_param",
			'period'    => 'range',
			'unit'      => 'minute', // range 1 minute
			'type'      => $metric ? $metric : 'avg_speed',
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);

		$graphData = array();
		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime )
					{
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = (int)$valueByTime['value'];
					}
				}
			}
		}

		$userSpeed  = current(current($graphData));
		$maxtime    = $userSpeed * 1.5;

		return array(
			'maxtime'       => (int)$maxtime,
			'user_speed'    => (int)$userSpeed
		);
	}

	public function overViewCacheHitGraph($idSite, $metric)
	{
		if(!$idSite) {
			$idSite = Common::getRequestVar('idSite', 1);
		}

		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
        $nameCdn    = explode("//",$nameCdn)[1];

		$date_param = date("Y-m-d H:i:s").",".date("Y-m-d H:i:s");
		$params = array(
			'name'      => $nameCdn,
			'date'      => "$date_param",
			'period'    => 'range',
			'unit'      => 'minute', // range 1 minute
			'type'      => $metric ? $metric : 'isp_request_count_200_viettel',
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);

		$graphData = array();
		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime )
					{
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = (int)$valueByTime['value'];
					}
				}
			}
		}

		$cacheHit   = current(current($graphData));
		$maxtime    = $cacheHit * 1.5;

		return array(
			'maxtime'       => (int)$maxtime,
			'cache_hit'     => (int)$cacheHit
		);
	}

	public function getEvolutionOverview($idSite, $date, $period, $columns = false)
	{
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
        $nameCdn    = explode("//",$nameCdn)[1];

		$module = Common::getRequestVar('module', false);
		$action = Common::getRequestVar('action', false);

		$typePeriod = $this->countStepPeriod($period);
		$dates      = explode(",", $date);

		if (!$columns) {
			$columns = Common::getRequestVar('columns', false);
			if( !$columns && $module == 'QoS' && $action == 'httpCode' ) {
				$columns = $this->httpCode;
			}
		}

		if ( is_array($columns) ) {
			$columns = implode(",",$columns);
		}

		$params = array(
			'name'      => $nameCdn,
			'date'      => ($typePeriod == 'range') ? $date : $dates[1],
			'period'    => ($typePeriod == 'range') ? $typePeriod : $this->diffDays($dates[0], $dates[1]) . ' days',
			'unit'      => $period,
			'type'      => $columns ? $columns : 'request_count_200,request_count_204,request_count_206,request_count_301,request_count_302,request_count_304'
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);

		/**
		 * Make data like
		 *
		 * array (
		 *      "2016-07-17" => array ( "request_count_200" => X, "request_count_500" => Y ),
		 *      "2016-07-18" => array ( "request_count_200" => X, "request_count_500" => Y ),
		 *      "2016-07-19" => array ( "request_count_200" => X, "request_count_500" => Y )
		 * )
		 */

		$dataCustomer = json_decode($dataCustomer, true);
		$graphData = array();

		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime )
					{
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = $valueByTime['value'];
					}
				}
			}
		}
		ksort($graphData);

		return DataTable::makeFromIndexedArray($graphData);
	}

	public function getGraphEvolution($idSite, $date, $period, $columns = false)
	{
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
        $nameCdn = explode("//",$nameCdn)[1];

		$module = Common::getRequestVar('module', false);
		$action = Common::getRequestVar('action', false);

		$isp    = Common::getRequestVar('isp',    false);
		$metric    = Common::getRequestVar('metric',    false);
		$statusCode = Common::getRequestVar('statusCode',    false);

		$typePeriod = $this->countStepPeriod($period);
		$dates      = explode(",", $date);

		if (!$columns) {
			$columns = Common::getRequestVar('columns', false);
			if( !$columns && $module == 'QoS' && $action == 'overview' ) {
				if ( $metric ){
					$columns = $this->overview[ $metric ];
				} else {
					$columns = array();
					$columns[] = implode(",",$this->overview);
				}
			} elseif( !$columns && $module == 'QoS' && $action == 'mnBandwidth' ) {
				if ( $isp ){
					$columns = $this->trafficByIsp[$isp];
				} else {
					$columns = array();
					foreach ($this->trafficByIsp as $metrics) {
						$columns[] = implode(",",$metrics);
					}
				}

			} elseif( !$columns && $module == 'QoS' && $action == 'httpCode' ) {
				if ( $statusCode ){
					$columns = $this->httpCode[$statusCode];
				} else {
					$columns = array();
					foreach ($this->httpCode as $metrics) {
						$columns[] = implode(",",$metrics);
					}
				}
			} elseif (!$columns && $module == 'QoS' && $action == 'cacheHit') {
				if ( $isp ){
					$columns = $this->cacheHit[$isp];
				} else {
					$columns = array();
					foreach ($this->cacheHit as $metrics) {
						$columns[] = implode(",",$metrics);
					}
				}
			} elseif (!$columns && $module == 'QoS' && $action == 'mnSizeTraffic') {
				$columns = $this->userSpeed;
			} elseif (!$columns && $module == 'QoS' && $action == 'isp') {
				if ( $isp ){
					$columns = $this->isp[$isp];
				} else {
					$columns = array();
					foreach ($this->isp as $metrics) {
						$columns[] = implode(",",$metrics);
					}
				}
			} elseif (!$columns && $module == 'QoS' && $action == 'country') {
				$columns = $this->country;
			}
		}

		if ( is_array($columns) ) {
			$columns = implode(",",$columns);
		}

		$params = array(
			'name'      => $nameCdn,
			'date'      => ($typePeriod == 'range') ? $date : $dates[1],
			'period'    => ($typePeriod == 'range') ? $typePeriod : $this->diffDays($dates[0], $dates[1]) . ' days',
			'unit'      => $period,
			'type'      => $columns
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);

		/**
		 * Make data like
		 *
		 * array (
		 *      "2016-07-17" => array ( "request_count_200" => X, "request_count_500" => Y ),
		 *      "2016-07-18" => array ( "request_count_200" => X, "request_count_500" => Y ),
		 *      "2016-07-19" => array ( "request_count_200" => X, "request_count_500" => Y )
		 * )
		 */

		$dataCustomer = json_decode($dataCustomer, true);
		$graphData = array();
		$format = new Formatter();
		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime ) {
						if( $module == 'QoS' && $action == 'overview' && $metric == 'body_bytes_sent') {
							$graphData[$valueByTime['name']][$valueOfTypeRequest['type']] = $format->getPrettySizeFromBytes($valueByTime['value'], 'G');
						} else {
							$graphData[$valueByTime['name']][$valueOfTypeRequest['type']] = $format->getPrettySizeFromBytes($valueByTime['value'], 'M');

						}
					}
				}
			}
		}

		ksort($graphData);

		$r = DataTable::makeFromIndexedArray($graphData);
		return $r;
	}

	public function getGraphEvolutionCacheHit($idSite, $date, $period, $columns = false)
	{
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
        $nameCdn = explode("//",$nameCdn)[1];

		$module = Common::getRequestVar('module', false);
		$action = Common::getRequestVar('action', false);
		$metric = Common::getRequestVar('metric', false);

		$typePeriod = $this->countStepPeriod($period);
		$dates      = explode(",", $date);

		if (!$columns) {
			$columns = Common::getRequestVar('columns', false);
			if (!$columns && $module == 'QoS' && $action == 'mnEdgeHit') {
				if ( $metric ){
					$columns = $this->cacheHit[ $metric ];
				} else {
					$columns = array();
					$columns[] = implode(",",$this->cacheHit);
				}
			}
		}

		if ( is_array($columns) ) {
			if (in_array('isp_request_count_2xx_total', $columns)) {
				$columns = array_diff($columns, array('isp_request_count_2xx_total'));
			}
			$columns = implode(",",$columns);
		}

		if ( strrpos( $columns, 'isp_request_count_2xx_total') ) {
			$columns = explode(",",$columns);
			$columns = array_diff($columns, array('isp_request_count_2xx_total'));
			$columns = implode(",",$columns);
		}

		$columns2 = $this->cacheHit[ $metric ];
		if ( is_array($columns2) ) {
			if (in_array('isp_request_count_2xx_total', $columns2)) {
				$columns2 = array_diff($columns2, array('isp_request_count_2xx_total'));
			}
			$columns2 = implode(",",$columns2);
		}

		$params = array(
			'name'      => $nameCdn,
			'date'      => ($typePeriod == 'range') ? $date : $dates[1],
			'period'    => ($typePeriod == 'range') ? $typePeriod : $this->diffDays($dates[0], $dates[1]) . ' days',
			'unit'      => $period,
			'type'      => $columns2
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);

		$graphData  = array();
		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime ) {
						if ( $metric == 'edge_hit') {
							if (strpos($columns, $valueOfTypeRequest['type']) !== false) {
								$graphData[$valueByTime['name']][$valueOfTypeRequest['type']] = (int)$valueByTime['value'];
							}
							if (isset($graphData[$valueByTime['name']]['isp_request_count_2xx_total'])) {
								$graphData[$valueByTime['name']]['isp_request_count_2xx_total'] += (int)$valueByTime['value'];
							} else {
								$graphData[$valueByTime['name']]['isp_request_count_2xx_total'] = (int)$valueByTime['value'];
							}
						} else {
							$graphData[$valueByTime['name']]['ratio_hit'][$valueOfTypeRequest['type']] = (int)$valueByTime['value'];
						}
					}
				}
			}
		}
		if ( $metric == 'ratio_hit') {
			foreach ($graphData as $d => $v) {
				$t = ($v['ratio_hit']['cache_status_HIT']/$v['ratio_hit']['request_count_2xx']);
				$graphData[ $d ]['ratio_hit'] = $t * 100;
			}
		}

		ksort($graphData);

		return DataTable::makeFromIndexedArray($graphData);
	}

	public function getGraphEvolutionISP($idSite, $date, $period, $columns = false)
	{
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
        $nameCdn    = explode("//",$nameCdn)[1];

		$module = Common::getRequestVar('module', false);
		$action = Common::getRequestVar('action', false);

		$typePeriod = $this->countStepPeriod($period);
		$dates      = explode(",", $date);

		if (!$columns) {
			$columns = Common::getRequestVar('columns', false);
			if (!$columns && $module == 'QoS' && $action == 'isp') {
				$columns = $this->trafficByIsp;
			}
		}

		if ( is_array($columns) ) {
			if (in_array('isp_traffic_ps_total', $columns)) {
				$columns = array_diff($columns, array('isp_traffic_ps_total'));
			}
			$columns = implode(",",$columns);
		}

		if ( strrpos( $columns, 'isp_traffic_ps_total') ) {
			$columns = explode(",",$columns);
			$columns = array_diff($columns, array('isp_traffic_ps_total'));
			$columns = implode(",",$columns);
		}

		$columns2 = $this->trafficByIsp;
		if ( is_array($columns2) ) {
			if (in_array('isp_traffic_ps_total', $columns2)) {
				$columns2 = array_diff($columns2, array('isp_traffic_ps_total'));
			}
			$columns2 = implode(",",$columns2);
		}

		$params = array(
			'name'      => $nameCdn,
			'date'      => ($typePeriod == 'range') ? $date : $dates[1],
			'period'    => ($typePeriod == 'range') ? $typePeriod : $this->diffDays($dates[0], $dates[1]) . ' days',
			'unit'      => $period,
			'type'      => $columns2
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);
		$graphData  = array();
		$format = new Formatter();
		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime ) {

						if ( strpos($columns, $valueOfTypeRequest['type']) !== false ) {
							$graphData[$valueByTime['name']][$valueOfTypeRequest['type']] = $format->getPrettySizeFromBytes((int)$valueByTime['value'], "M");
						}
						if ( isset($graphData[ $valueByTime['name'] ][ 'isp_traffic_ps_total' ]) ) {
							$graphData[$valueByTime['name']]['isp_traffic_ps_total'] += $format->getPrettySizeFromBytes((int)$valueByTime['value'], "M");
						} else {
							$graphData[$valueByTime['name']]['isp_traffic_ps_total'] = $format->getPrettySizeFromBytes((int)$valueByTime['value'], "M");
						}
					}
				}
			}
		}

		ksort($graphData);

		return DataTable::makeFromIndexedArray($graphData);
	}

	public function getGraphEvolutionBandwidth($idSite, $date, $period, $columns = false)
	{
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
        $nameCdn    = explode("//",$nameCdn)[1];

		$module = Common::getRequestVar('module', false);
		$action = Common::getRequestVar('action', false);

		$isp    = Common::getRequestVar('isp',    false);

		$typePeriod = $this->countStepPeriod($period);
		$dates      = explode(",", $date);

		if (!$columns) {
			$columns = Common::getRequestVar('columns', false);
			if (!$columns && $module == 'QoS' && $action == 'mnBandwidth') {
				if ($isp) {
					$columns = $this->getTraffic()[$isp];
				} else {
					$columns = array();
					foreach ($this->getTraffic() as $metrics) {
						$columns[] = implode(",", $metrics);
					}
				}
			}
		}
		if (in_array('isp_isp_traffic_ps_total', $columns)) {
			unset($columns['isp_isp_traffic_ps_total']);
		}
		if ( is_array($columns) ) {
			$columns = implode(",",$columns);
		}

		$params = array(
			'name'      => $nameCdn,
			'date'      => ($typePeriod == 'range') ? $date : $dates[1],
			'period'    => ($typePeriod == 'range') ? $typePeriod : $this->diffDays($dates[0], $dates[1]) . ' days',
			'unit'      => $period,
			'type'      => $columns
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);
		$graphData  = array();
		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime )
					{
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ]    = $valueByTime['value'];
						$graphData[ $valueByTime['name'] ][ 'isp_isp_traffic_ps_total' ]    += $valueByTime['value'];
					}
				}
			}
		}
		ksort($graphData);

		return DataTable::makeFromIndexedArray($graphData);
	}

	public function getGraphEvolutionAvgSpeed($idSite, $date, $period, $columns = false)
	{
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
        $nameCdn    = explode("//",$nameCdn)[1];

		$module = Common::getRequestVar('module', false);
		$action = Common::getRequestVar('action', false);

		$isp    = Common::getRequestVar('isp',    false);

		$typePeriod = $this->countStepPeriod($period);
		$dates      = explode(",", $date);

		if (!$columns) {
			$columns = Common::getRequestVar('columns', false);
			if (!$columns && $module == 'QoS' && $action == 'mnSizeTraffic') {
				if ($isp) {
					$columns = $this->ispSpeedDownload[$isp];
				} else {
					$columns = array();
					foreach ($this->ispSpeedDownload as $metrics) {
						$columns[] = implode(",", $metrics);
					}
				}
			}
		}

		if (in_array('isp_avg_speed_total', $columns)) {
			unset($columns['isp_avg_speed_total']);
		}
		if ( is_array($columns) ) {
			$columns = implode(",",$columns);
		}

		$params = array(
			'name'      => $nameCdn,
			'date'      => ($typePeriod == 'range') ? $date : $dates[1],
			'period'    => ($typePeriod == 'range') ? $typePeriod : $this->diffDays($dates[0], $dates[1]) . ' days',
			'unit'      => $period,
			'type'      => $columns
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);
		$graphData  = array();
		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime )
					{
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = $valueByTime['value'];
						$graphData[ $valueByTime['name'] ][ 'isp_avg_speed_total' ] += $valueByTime['value'];
					}
				}
			}
		}
		ksort($graphData);

		return DataTable::makeFromIndexedArray($graphData);
	}

	public function getBrowsers($idSite, $period, $date, $segment = false)
	{

		$data = \Piwik\API\Request::processRequest('DevicesDetection.getBrowsers', array(
			'idSite'    => $idSite,
			'period'    => $period,
			'date'      => $date,
			'segment'   => $segment,
		));
		$data->applyQueuedFilters();

		$result = $data->getEmptyClone($keepFilters = false);

		foreach ($data->getRows() as $visitRow) {
			$browserName = $visitRow->getColumn('label');

			$result->addRowFromSimpleArray(array(
				'label'             => $browserName,
				'nb_uniq_visitors'  => $visitRow->getColumn('nb_uniq_visitors')
			));
		}

		return $result;
	}

	public function getCity($idSite, $period, $date, $segment = false)
	{

		$data = \Piwik\API\Request::processRequest('UserCountry.getCity', array(
			'idSite'    => $idSite,
			'period'    => $period,
			'date'      => $date,
			'segment'   => $segment,
		));
		$data->applyQueuedFilters();

		$result = $data->getEmptyClone($keepFilters = false);

		foreach ($data->getRows() as $visitRow) {
			$browserName = $visitRow->getColumn('label');

			$result->addRowFromSimpleArray(array(
				'label'             => $browserName,
				'nb_uniq_visitors'  => $visitRow->getColumn('nb_uniq_visitors')
			));
		}

		return $result;
	}

	public function getUrls($idSite, $period, $date, $segment = false)
	{

		$data = \Piwik\API\Request::processRequest('Actions.getPageUrls', array(
			'idSite'    => $idSite,
			'period'    => $period,
			'date'      => $date,
			'segment'   => $segment,
		));
		$data->applyQueuedFilters();

		$result = $data->getEmptyClone($keepFilters = false);

//        foreach ($data->getRows() as $visitRow) {
//            $browserName = $visitRow->getColumn('label');
//
//            $result->addRowFromSimpleArray(array(
//                'label'             => $browserName,
//                'nb_uniq_visitors'  => $visitRow->getColumn('nb_uniq_visitors')
//            ));
//        }

		return $result;
	}

//	public function getGraphEvolutionBw($idSite, $period, $date, $segment = false, $columns = false)
//	{
//		$cdnObj     = new Site($idSite);
//		$nameCdn    = $cdnObj->getName();
//
//		$module = Common::getRequestVar('module', false);
//		$action = Common::getRequestVar('action', false);
//
//		$typePeriod = $this->countStepPeriod($period);
//		$dates      = explode(",", $date);
//
//		$params = array(
//			'name'      => $nameCdn,
//			'date'      => ($typePeriod == 'range') ? $date : $dates[1],
//			'period'    => ($typePeriod == 'range') ? $typePeriod : $this->diffDays($dates[0], $dates[1]) . ' days',
//			'unit'      => $period,
//			'type'      => $columns ? $columns : 'traffic_ps'
//		);
//
//		$dataCustomer = $this->apiGetCdnDataMk($params);
//
//		/**
//		 * Make data like
//		 *
//		 * array (
//		 *      "2016-07-17" => array ( "request_count_200" => X, "request_count_500" => Y ),
//		 *      "2016-07-18" => array ( "request_count_200" => X, "request_count_500" => Y ),
//		 *      "2016-07-19" => array ( "request_count_200" => X, "request_count_500" => Y )
//		 * )
//		 */
//
//		$dataCustomer = json_decode($dataCustomer, true);
//		$graphData = array();
//
//		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
//		{
//			foreach ( $dataCustomer['data'] as $valueOfCdn )
//			{
//				// Name of Cdn: $valueOfCdn['name']
//				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
//				{
//					// Type request: valueOfTypeRequest['type']
//					foreach ( $valueOfTypeRequest['value'] as $valueByTime )
//					{
//						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = $valueByTime['value'];
//					}
//				}
//			}
//		}
//		ksort($graphData);
//
//		return DataTable::makeFromIndexedArray($graphData);
//	}

//	public function overviewGetBandwidth( $lastMinutes, $metrics , $refreshAfterXSecs )
//	{
//		$idSite     = Common::getRequestVar('idSite', 1);
//
//		$cdnObj     = new Site($idSite);
//		$nameCdn    = $cdnObj->getName();
//
//		$now = time();
//		$before_3mins 	= $now - ($lastMinutes * 60);
//		$date_param 	= date("Y-m-d H:i:s", $before_3mins).",".date("Y-m-d H:i:s", $before_3mins);
//		$params = array(
//			'name'      => $nameCdn,
//			'date'      => "$date_param",
//			'period'    => 'range',
//			'unit'      => 'minute', // range 1 minute
//			'type'      => $metrics ? $metrics : 'traffic_ps',
//		);
//
//		$dataCustomer = $this->apiGetCdnDataMk($params);
//		$dataCustomer = json_decode($dataCustomer, true);
//
//		$graphData = array();
//		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
//		{
//			foreach ( $dataCustomer['data'] as $valueOfCdn )
//			{
//				// Name of Cdn: $valueOfCdn['name']
//				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
//				{
//					// Type request: valueOfTypeRequest['type']
//					foreach ( $valueOfTypeRequest['value'] as $valueByTime )
//					{
//						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = (int)$valueByTime['value'];
//					}
//				}
//			}
//		}
//
//		(int)$bandwidth = current(current($graphData));
//		$formatter 		= new Formatter();
//
//		return array(
//			'bandwidth'        	=> $formatter->getPrettySizeFromBytes( (int)$bandwidth, '', 2 ),
//			'refreshAfterXSecs' => 5,
//			'metrics'           => 'traffic_ps',
//			'lastMinutes'       => $lastMinutes
//		);
//	}

	public function overviewGetUserSpeed( $lastMinutes, $metrics , $refreshAfterXSecs )
	{
		$idSite     = Common::getRequestVar('idSite', 1);

		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
        $nameCdn    = explode("//",$nameCdn)[1];

		$now = time();
		$before_3mins = $now - ($lastMinutes * 60);
		$date_param = date("Y-m-d H:i:s", $before_3mins).",".date("Y-m-d H:i:s", $before_3mins);
		$params = array(
			'name'      => $nameCdn,
			'date'      => "$date_param",
			'period'    => 'range',
			'unit'      => 'minute', // range 1 minute
			'type'      => 'avg_speed',
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);

		$graphData = array();
		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime )
					{
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = (int)$valueByTime['value'];
					}
				}
			}
		}

		(int)$userSpeed  = current(current($graphData));
		$formatter = new Formatter();

		return array(
			'user_speed'        => $formatter->getPrettySizeFromBytes((int)$userSpeed, '', 2),
			'refreshAfterXSecs' => 5,
			'metrics'           => 'avg_speed',
			'lastMinutes'       => $lastMinutes
		);
	}

	/**
	 * @return mixed
	 */
	public function getTraffps($idSite, $lastMinutes, $metric) {
		$now = date("Y-m-d H:i:s");
		$time = strtotime($now) - ($lastMinutes * 60);
		$lastTime = date("Y-m-d H:i:s", $time);

		if(!$idSite) {
			$idSite = Common::getRequestVar('idSite', 1);
		}
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
        $nameCdn    = explode("//",$nameCdn)[1];

		$date_param = $lastTime.",".$lastTime;
		$params = array(
			'name'      => $nameCdn,
			'date'      => "$date_param",
			'period'    => 'range',
			'unit'      => 'minute', // range 2 minute
			'type'      => $metric ? $metric : 'traffic_ps',
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);

		$format = new Formatter();
		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime )
					{
						$graphData[ $valueOfTypeRequest['type'] ] = $format->getPrettySizeFromBytes((int)$valueByTime['value']);
					}
				}
			}
		}
		$split = explode(" ", $graphData['traffic_ps']);
		$graphData['traffic_ps']    = $split[0];
		$graphData['unit']          = $split[1];

		return $graphData;
	}

	public function getAvgDl($idSite, $lastMinutes, $metric) {
        $now = date("Y-m-d H:i:s");
        $time = strtotime($now) - ($lastMinutes * 60);
        $lastTime = date("Y-m-d H:i:s", $time);

        if(!$idSite) {
            $idSite = Common::getRequestVar('idSite', 1);
        }
        $cdnObj     = new Site($idSite);
        $nameCdn    = $cdnObj->getMainUrl();
        $nameCdn    = explode("//",$nameCdn)[1];

        $date_param = $lastTime.",".$lastTime;
        $params = array(
            'name'      => $nameCdn,
            'date'      => "$date_param",
            'period'    => 'range',
            'unit'      => 'minute', // range 2 minute
            'type'      => $metric ? $metric : 'avg_speed',
        );

        $dataCustomer = $this->apiGetCdnDataMk($params);
        $dataCustomer = json_decode($dataCustomer, true);

        $format = new Formatter();
        if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
        {
            foreach ( $dataCustomer['data'] as $valueOfCdn )
            {
                // Name of Cdn: $valueOfCdn['name']
                foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
                {
                    // Type request: valueOfTypeRequest['type']
                    foreach ( $valueOfTypeRequest['value'] as $valueByTime )
                    {
                        $graphData[ $valueOfTypeRequest['type'] ] = $format->getPrettySizeFromBytes((int)$valueByTime['value']);
                    }
                }
            }
        }
        $split = explode(" ", $graphData['avg_speed']);
        $graphData['avg_speed']    = $split[0];
        $graphData['unit']         = $split[1];

        return $graphData;
	}

	private function apiGetCdnDataMk( $data )
	{
		// $url = 'http://172.16.64.169:8001';
		//$url = 'http://113.164.27.58:8001';
		$url = 'http://125.212.200.247:8001';
		// $url = 'http://127.0.0.1:8001';
		$data['path'] = '/api/v1/stat';

		$query = $data['path']."?name=".$data['name']."&date=".$data['date']."&period=".$data['period']."&unit=".$data['unit']."&type=".$data['type'];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->encodeURI($url.$query));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, 50000);
		$result = curl_exec($ch);

		$curl_errno = curl_errno($ch);
		if($curl_errno > 0) {
			curl_close($ch);
			return 'timeout';
		}
		curl_close($ch);

		return $result;
	}

	private function encodeURI($url)
	{
		// http://php.net/manual/en/function.rawurlencode.php
		// https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/encodeURI
		$unescaped = array(
			'%2D'=>'-','%5F'=>'_','%2E'=>'.','%21'=>'!', '%7E'=>'~',
			'%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')'
		);
		$reserved = array(
			'%3B'=>';','%2C'=>',','%2F'=>'/','%3F'=>'?','%3A'=>':',
			'%40'=>'@','%26'=>'&','%3D'=>'=','%2B'=>'+','%24'=>'$'
		);
		$score = array(
			'%23'=>'#'
		);
		return strtr(rawurlencode($url), array_merge($reserved,$unescaped,$score));
	}

	private function diffDays($dateFrom, $dateTo)
	{
		$dateTimeFrom = strtotime($dateFrom);
		$dateTimeTo = strtotime($dateTo);

		return ($dateTimeTo - $dateTimeFrom)/86400;
	}

	private function countStepPeriod($period)
	{
		switch ($period)
		{
			case 'week':
			case 'month':
			case 'year';
				$typePeriod = 'range';
				break;
			default:
				$typePeriod = 'days';
				break;
		}

		return $typePeriod;
	}
}
