<?php
/**
 * Copyright (C) InnoCraft Ltd - All rights reserved.
 *
 * NOTICE:  All information contained herein is, and remains the property of InnoCraft Ltd.
 * The intellectual and technical concepts contained herein are protected by trade secret or copyright law.
 * Redistribution of this information or reproduction of this material is strictly forbidden
 * unless prior written permission is obtained from InnoCraft Ltd.
 *
 * You shall use this code only in accordance with the license agreement obtained from InnoCraft Ltd.
 *
 * @link https://www.innocraft.com/
 * @license For license details see https://www.innocraft.com/license
 */

namespace Piwik\Plugins\MediaAnalytics;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\FrontController;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Bar;
use Piwik\Plugins\MediaAnalytics\Dao\LogTable;
use Piwik\Plugins\MediaAnalytics\Reports\Base;
use Piwik\Plugins\MediaAnalytics\Widgets\Helper;
use Piwik\SettingsPiwik;
use Piwik\ViewDataTable;
use Piwik\Translation\Translator;

class Controller extends \Piwik\Plugin\Controller
{
    /**
     * @var Widgets
     */
    private $widgets;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var Metrics
     */
    private $metrics;

    /**
     * @var LogTable
     */
    private $logTable;

    public function __construct(Widgets $widgets, Translator $translator, Metrics $metrics, LogTable $logTable)
    {
        parent::__construct();

        $this->widgets = $widgets;
        $this->translator = $translator;
        $this->metrics = $metrics;
        $this->logTable = $logTable;
    }

    public function video()
    {
        $this->checkSitePermission();

        return $this->renderTemplate('mediaReport', array(
            'title' => $this->translator->translate('MediaAnalytics_MenuVideo'),
            'titleHours' => 'MediaAnalytics_VideoHours',
            'resourcesReport' => $this->renderReport('getVideoTitles'),
            'resolutions' => $this->renderReport('getVideoResolutions'),
            'hours' => $this->renderReport('getVideoHours'),
        ));
    }

    public function audio()
    {
        $this->checkSitePermission();

        return $this->renderTemplate('mediaReport', array(
            'title' => $this->translator->translate('MediaAnalytics_MenuAudio'),
            'titleHours' => 'MediaAnalytics_AudioHours',
            'resourcesReport' => $this->renderReport('getAudioTitles'),
            'hours' => $this->renderReport('getAudioHours')
        ));
    }

    public function detail()
    {
        $this->checkSitePermission();

        Common::getRequestVar('idSubtable', null, 'int');
        $reportAction = Common::getRequestVar('reportAction', null, 'string');

        /** @var Base $report */
        $report = Report::factory('MediaAnalytics', $reportAction);

        if (empty($report)) {
            throw new \Exception('This report does not exist');
        }

        $dimensionName = $report->getDimension()->getName();
        $isAudioReport = $report->isAudioReport();

        return $this->renderTemplate('detail', array(
            'title' => $dimensionName,
            'watchedTimeTitle' => $isAudioReport ? 'MediaAnalytics_TimeSpentListening' : 'MediaAnalytics_TimeSpentWatching',
            'watchedProgressTitle' => $isAudioReport ? 'MediaAnalytics_MediaProgressTitleAudio' : 'MediaAnalytics_MediaProgressTitleVideo',
            'watchedTime' => $this->renderBarGraphSecondaryDimensionReport($reportAction, Archiver::SECONDARY_DIMENSION_SPENT_TIME),
            'watchedProgress' => $this->renderBarGraphSecondaryDimensionReport($reportAction, Archiver::SECONDARY_DIMENSION_MEDIA_PROGRESS),
            'hoursTitle' => $isAudioReport ? 'MediaAnalytics_AudioHours' : 'MediaAnalytics_VideoHours',
            'hours' => $this->renderTableSecondaryDimensionReport($reportAction, Archiver::SECONDARY_DIMENSION_HOURS, $isAudioReport),
            'resolutions' => $this->renderTableSecondaryDimensionReport($reportAction, Archiver::SECONDARY_DIMENSION_RESOLUTION, $isAudioReport),
        ));
    }

    public function audienceLogReport($fetch = false)
    {
        $this->checkSitePermission();

        return $this->renderTemplate('mediaLog', array(
            'mediaLog' => $this->getAudienceLog($fetch)
        ));
    }

    public function getAudienceLog($fetch = false)
    {
        $this->checkSitePermission();

        $saveGET = $_GET;
        $_GET['segment'] = Helper::getMediaSegment();
        $_GET['widget'] = 1;
        $output = FrontController::getInstance()->dispatch('Live', 'getVisitorLog', array($fetch));
        $_GET   = $saveGET;

        return $output;
    }

    private function renderBarGraphSecondaryDimensionReport($reportAction, $secondarydimension)
    {
        $maxGraphElements = 500;
        if ($secondarydimension === Archiver::SECONDARY_DIMENSION_MEDIA_PROGRESS) {
            $maxGraphElements = 101; // we show up to one entry per percentage from 0...100
        }

        $view = ViewDataTable\Factory::build(Bar::ID, 'MediaAnalytics.' . $reportAction, '', true);
        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order = 'asc';
        $view->config->y_axis_unit = '';
        $view->config->show_footer = false;
        $view->config->show_line_graph = false;
        $view->config->show_all_ticks = true;
        $view->config->show_series_picker = false;
        $view->config->allow_multi_select_series_picker = false;
        $view->config->translations[Metrics::METRIC_NB_PLAYS] = $this->translator->translate('MediaAnalytics_ColumnPlays');
        $view->config->selectable_columns = array(Metrics::METRIC_NB_PLAYS);
        $view->config->max_graph_elements = $maxGraphElements;
        $view->requestConfig->request_parameters_to_modify['secondaryDimension'] = $secondarydimension;

        return $view->render();
    }

    private function renderTableSecondaryDimensionReport($reportAction, $secondarydimension, $isAudioReport)
    {
        if (Archiver::SECONDARY_DIMENSION_RESOLUTION === $secondarydimension
            && $isAudioReport) {
            // we do not render resolutions table for audio...
            return;
        }

        $view = ViewDataTable\Factory::build(HtmlTable::ID, 'MediaAnalytics.' . $reportAction, '', true);
        $view->config->show_footer = false;
        $view->requestConfig->request_parameters_to_modify['secondaryDimension'] = $secondarydimension;

        return $view->render();
    }

    public function live()
    {
        $this->checkSitePermission();

        return $this->renderTemplate('live', array(
            'currentPlays' => $this->widgets->currentPlays(),
            'currentTime' => $this->widgets->currentTime(),
            'mostPlays30' => $this->widgets->mostPlays(30),
            'mostPlays3600' => $this->widgets->mostPlays(3600),
            'map' => $this->widgets->realTimeAudienceMap()
        ));
    }

    public function audienceMap()
    {
        $this->checkSitePermission();

        return $this->widgets->audienceMap();
    }

    public function overview()
    {
        $this->checkSitePermission();

        if (!$this->logTable->hasRecords($this->idSite)) {

            if (PluginManager::getInstance()->isPluginActivated('CustomPiwikJs')) {
                $includeAutomatically = Request::processRequest('CustomPiwikJs.doesIncludePluginTrackersAutomatically');
            } else {
                $includeAutomatically = false;
            }

            return $this->renderTemplate('gettingStarted', array(
                'siteName' => $this->site->getName(),
                'piwikJsWritable' => $includeAutomatically
            ));
        }

        $evolutionGraph = $this->getEvolutionGraph(array(), array(Metrics::METRIC_NB_PLAYS), 'getIndexGraph');

        $sparklines = $this->widgets->sparklinesSummary();

        return $this->renderTemplate('overview', array(
            'evolutionSummary' => $evolutionGraph,
            'sparklines' => $sparklines
        ));
    }

    public function getIndexGraph()
    {
        $this->checkSitePermission();

        return $this->getEvolutionGraph(array(), array(), __FUNCTION__);
    }

    public function getEvolutionGraph(array $columns = array(), array $defaultColumns = array(), $callingAction = __FUNCTION__)
    {
        $this->checkSitePermission();

        if (empty($columns)) {
            $columns = Common::getRequestVar('columns', false);
            if (false !== $columns) {
                $columns = Piwik::getArrayFromApiParameter($columns);
            }
        }

        $report = Report::factory('MediaAnalytics', 'get');
        $documentation = $report->getDocumentation();

        $selectableColumns = $report->getMetricsRequiredForReport(null, null);
        $selectableColumns[] = Metrics::METRIC_PLAY_RATE;
        $selectableColumns[] = Metrics::METRIC_FINISH_RATE;

        $period = Common::getRequestVar('period', '', 'string');
        if (SettingsPiwik::isUniqueVisitorsEnabled($period) && Archiver::isUniqueVisitorsEnabled($period)) {
            $selectableColumns[] = Metrics::METRIC_IMPRESSION_RATE;
        }

        $key = array_search(Metrics::METRIC_NB_UNIQUE_VISITORS, $selectableColumns);
        if ($key !== false) {
            array_splice($selectableColumns, $key, 1);
        }

        // $callingAction may be specified to distinguish between
        // "VisitsSummary_WidgetLastVisits" and "VisitsSummary_WidgetOverviewGraph"
        $view = $this->getLastUnitGraphAcrossPlugins($this->pluginName, $callingAction, $columns,
            $selectableColumns, $documentation);

        if (empty($view->config->columns_to_display) && !empty($defaultColumns)) {
            $view->config->columns_to_display = $defaultColumns;
        }

        return $this->renderView($view);
    }

}
