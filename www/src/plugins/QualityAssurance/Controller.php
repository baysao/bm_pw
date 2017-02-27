<?php
namespace Piwik\Plugins\QualityAssurance;

use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Period;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Piwik;
use Piwik\Translation\Translator;
use Piwik\View;
use Piwik\Container\StaticContainer;
use Piwik\API\Request;

require_once PIWIK_INCLUDE_PATH . '/plugins/QualityAssurance/functions.php';

class Controller extends \Piwik\Plugin\Controller
{
    private $translator;

    public function __construct(Translator $translator)
    {
        parent::__construct();

        $this->translator = $translator;
    }

    public function index()
    {
        return $this->getStreamAnalyzer($isWidgetized = false);
    }

    public function getStreamAnalyzer($isWidgetized = false)
    {
        Piwik::checkUserHasSomeViewAccess();

        $date   = Common::getRequestVar('date', 'today');
        $period = Common::getRequestVar('period', 'day');

        $view = new View("@QualityAssurance/index");

        $view->isWidgetized         = $isWidgetized;
        $view->displayRevenueColumn = Common::isGoalPluginEnabled();
        $view->limit                = Config::getInstance()->General['all_websites_website_per_page'];
        $view->show_sparklines      = Config::getInstance()->General['show_multisites_sparklines'];

        $view->autoRefreshTodayReport = 0;
        // if the current date is today, or yesterday,
        // in case the website is set to UTC-12), or today in UTC+14, we refresh the page every 5min
        if (in_array($date, array('today', date('Y-m-d'),
            'yesterday', Date::factory('yesterday')->toString('Y-m-d'),
            Date::factory('now', 'UTC+14')->toString('Y-m-d')))
        ) {
            $view->autoRefreshTodayReport = Config::getInstance()->General['multisites_refresh_after_seconds'];
        }

        $params = $this->getGraphParamsModified();
        $view->dateSparkline = $period == 'range' ? $date : $params['date'];

        // Widget bandwidth
		$lastMinutes = 2;
		$audience_size = API::getInstance()->overviewGetRowOne( $lastMinutes, $metrics = 'traffic_ps', 5 );
		$view->bw_lastMinutes  	= $lastMinutes;
		$view->audience_size   		= $audience_size['audience_size']['value'];
		$view->startup_time   		= $audience_size['startup_time']['value'];
		$view->bitrate   		    = $audience_size['bitrate']['value'];
		$view->buffer_time   		= $audience_size['buffer_time']['value'];
		$view->refreshAfterXSecs = 10;
		$view->translations 	= array(
			'audience_size' => Piwik::translate('QualityAssurance_Audience')
		);
        $view->lastMinutes  = $lastMinutes;
		$view->refreshAfterXSecs = 5;

        $overview = array(
            'audience',
            'startup_time',
            'bit_rate',
            'rebuffer_time'
        );
        $view->graphOverview = $this->getGraphOverview(array(), $overview);

        $view->byFor    = $this->renderReport('getFor');
        $view->byCon    = $this->renderReport('getCon');
        $view->byCat    = $this->renderReport('getCat');

        // Map -------------- //
        $idSite = Common::getRequestVar('idSite', 1, 'int');
//        Piwik::checkUserHasViewAccess($idSite);
        $period = Common::getRequestVar('period');
        $date = Common::getRequestVar('date');
        if (!empty($segmentOverride)) {
            $segment = $segmentOverride;
        } else {
            $segment = Request::getRawSegmentFromRequest();
            if (empty($segment)) {
                $segment = '';
            }
        }
        $token_auth = Piwik::getCurrentUserTokenAuth();
        $view->defaultMetric = 'nb_visits';
        // request visits summary
        $request = new Request(
            'method=VisitsSummary.get&format=PHP'
            . '&idSite=' . $idSite
            . '&period=' . $period
            . '&date=' . $date
            . '&segment=' . $segment
            . '&token_auth=' . $token_auth
            . '&filter_limit=-1'
        );
        // some translations
        $view->localeJSON = json_encode(array(
            'nb_visits'            => $this->translator->translate('General_NVisits'),
            'one_visit'            => $this->translator->translate('General_OneVisit'),
            'no_visit'             => $this->translator->translate('UserCountryMap_NoVisit'),
            'nb_actions'           => $this->translator->translate('VisitsSummary_NbActionsDescription'),
            'nb_actions_per_visit' => $this->translator->translate('VisitsSummary_NbActionsPerVisit'),
            'bounce_rate'          => $this->translator->translate('VisitsSummary_NbVisitsBounced'),
            'avg_time_on_site'     => $this->translator->translate('VisitsSummary_AverageVisitDuration'),
            'and_n_others'         => $this->translator->translate('UserCountryMap_AndNOthers'),
            'no_data'              => $this->translator->translate('CoreHome_ThereIsNoDataForThisReport'),
            'nb_uniq_visitors'     => $this->translator->translate('VisitsSummary_NbUniqueVisitors'),
            'nb_users'             => $this->translator->translate('VisitsSummary_NbUsers'),
        ));
        $view->reqParamsJSON = $this->getEnrichedRequest($params = array(
            'period'                      => $period,
            'idSite'                      => $idSite,
            'date'                        => $date,
            'segment'                     => $segment,
            'token_auth'                  => $token_auth,
            'enable_filter_excludelowpop' => 1,
            'filter_excludelowpop_value'  => -1
        ));
        $view->metrics = $config['metrics'] = $this->getMetrics($idSite, $period, $date, $token_auth);
        $config['svgBasePath'] = 'plugins/QualityAssurance/svg/';
        $config['mapCssPath'] = 'plugins/QualityAssurance/stylesheets/map.css';
        $view->config = json_encode($config);
        $view->noData = empty($config['visitsSummary']['nb_visits']);

        $countriesByIso = array();
        $regionDataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');
        $countries = array_keys($regionDataProvider->getCountryList());

        foreach ($countries AS $country) {
            $countriesByIso[strtoupper($country)] = Piwik::translate('Intl_Country_'.strtoupper($country));
        }

        $view->countriesByIso = $countriesByIso;

        $view->continents = array(
            'AF' => \Piwik\Plugins\QualityAssurance\continentTranslate('afr'),
            'AS' => \Piwik\Plugins\QualityAssurance\continentTranslate('asi'),
            'EU' => \Piwik\Plugins\QualityAssurance\continentTranslate('eur'),
            'NA' => \Piwik\Plugins\QualityAssurance\continentTranslate('amn'),
            'OC' => \Piwik\Plugins\QualityAssurance\continentTranslate('oce'),
            'SA' => \Piwik\Plugins\QualityAssurance\continentTranslate('ams')
        );
        // ------------------------ //


        $this->setGeneralVariablesView($view);

        $view->siteName = $this->translator->translate('QualityAssurance_AllWebsitesDashboard');

        return $view->render();
    }

    public function getByFormat()
    {

    }

    public function getGraphOverview(array $columns = array(), array $defaultColumns = array())
    {
        if (empty($columns)) {
            $columns = Common::getRequestVar('columns', false);
            if (false !== $columns) {
                $columns = Piwik::getArrayFromApiParameter($columns);
            }
        }

        $selectableColumns = array(
            'audience',
            'startup_time',
            'bit_rate',
            'rebuffer_time'
        );

        $view = $this->getLastUnitGraphAcrossPlugins($this->pluginName, __FUNCTION__, $columns, $selectableColumns, '', 'QualityAssurance.getGraphEvolution');

        $view->config->enable_sort          = false;
        $view->config->max_graph_elements   = 30;
        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order  = 'asc';
        $view->requestConfig->disable_generic_filters=true;
        $view->config->addTranslations(array(
            'audience'      => Piwik::translate('QualityAssurance_Audience'),
            'startup_time'  => Piwik::translate('QualityAssurance_StartupTime'),
            'bit_rate'      => Piwik::translate('QualityAssurance_Bitrate'),
            'rebuffer_time' => Piwik::translate('QualityAssurance_RebufferTime'),
        ));

        // Can not check empty so have to hardcode. F**k me!
        $view->config->columns_to_display = $defaultColumns;

        return $this->renderView($view);
    }

    private function getEnrichedRequest($params, $encode = true)
    {
        $params['format'] = 'json';
        $params['showRawMetrics'] = 1;
        if (empty($params['segment'])) {
            $segment = Request::getRawSegmentFromRequest();
            if (!empty($segment)) {
                $params['segment'] = $segment;
            }
        }

        if (!empty($params['segment'])) {
            $params['segment'] = urldecode($params['segment']);
        }

        if ($encode) {
            $params = json_encode($params);
        }

        return $params;
    }

    private function getMetrics($idSite, $period, $date, $token_auth)
    {
        $request = new Request(
            'method=API.getMetadata&format=PHP'
            . '&apiModule=QualityAssurance&apiAction=getCountry'
            . '&idSite=' . $idSite
            . '&period=' . $period
            . '&date=' . $date
            . '&token_auth=' . $token_auth
            . '&filter_limit=-1'
        );
        $metaData = unserialize($request->process());

        $metrics = array();
        if (!empty($metaData[0]['metrics']) && is_array($metaData[0]['metrics'])) {
            foreach ($metaData[0]['metrics'] as $id => $val) {
                // todo: should use SettingsPiwik::isUniqueVisitorsEnabled ?
                if (Common::getRequestVar('period') == 'day' || $id != 'nb_uniq_visitors') {
                    $metrics[] = array($id, $val);
                }
            }
        }
        if (!empty($metaData[0]['processedMetrics']) && is_array($metaData[0]['processedMetrics'])) {
            foreach ($metaData[0]['processedMetrics'] as $id => $val) {
                $metrics[] = array($id, $val);
            }
        }
        return $metrics;
    }

    private function getApiRequestUrl($module, $action, $idSite, $period, $date, $token_auth, $filter_by_country = false, $segmentOverride = false)
    {
        // use processed reports
        $url = "?module=" . $module
            . "&method=" . $module . "." . $action . "&format=JSON"
            . "&idSite=" . $idSite
            . "&period=" . $period
            . "&date=" . $date
            . "&token_auth=" . $token_auth
            . "&segment=" . ($segmentOverride ? : Request::getRawSegmentFromRequest())
            . "&enable_filter_excludelowpop=1"
            . "&showRawMetrics=1";

        if ($filter_by_country) {
            $url .= "&filter_column=country"
                . "&filter_sort_column=nb_visits"
                . "&filter_limit=-1"
                . "&filter_pattern=";
        } else {
            $url .= "&filter_limit=-1";
        }
        return $url;
    }

    private function _report($module, $action, $idSite, $period, $date, $token_auth, $filter_by_country = false, $segmentOverride = false)
    {
        return $this->getApiRequestUrl('API', 'getProcessedReport&apiModule=' . $module . '&apiAction=' . $action,
            $idSite, $period, $date, $token_auth, $filter_by_country, $segmentOverride);
    }

}