<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\Reports;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Menu\MenuReporting;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Actions\Columns\Metrics\AveragePageGenerationTime;
use Piwik\Plugins\Actions\Columns\Metrics\ExitRate;
use Piwik\Plugins\CoreHome\Columns\Metrics\ActionsPerVisit;
use Piwik\Plugins\CoreHome\Columns\Metrics\AverageTimeOnSite;
use Piwik\Plugins\CoreHome\Columns\Metrics\BounceRate;
use Piwik\Plugins\CoreHome\Columns\UserId;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\CustomDimensions\Columns\Metrics\AverageTimeOnDimension;
use Piwik\Plugins\CustomDimensions\Dimension\CustomActionDimension;
use Piwik\Plugins\CustomDimensions\Dimension\CustomVisitDimension;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\CustomDimensions\Dao\Configuration;
use Piwik\Plugins\CustomDimensions\Tracker\CustomDimensionsRequestProcessor;
use Piwik\View;
use Piwik\WidgetsList;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetCustomDimension extends Report
{
    private $dimensionCache = array();

    private $scopeOfDimension = null;

    protected function init()
    {
        parent::init();

        $this->category = 'CustomDimensions_CustomDimensions';
        $this->menuTitle = $this->category;
        $this->name  = Piwik::translate($this->category);
        $this->order = 100;
        $this->actionToLoadSubTables = $this->action;

        $idDimension = Common::getRequestVar('idDimension', 0, 'int');
        $idSite      = Common::getRequestVar('idSite', 0, 'int');

        if ($idDimension > 0 && $idSite > 0) {
            $dimensions = $this->getActiveDimensionsForSite($idSite);
            foreach ($dimensions as $dimension) {
                if (((int) $dimension['idcustomdimension']) === $idDimension) {
                    $this->initThisReportFromDimension($dimension);
                }
            }
        }
    }

    /**
     * Here you can configure how your report should be displayed. For instance whether your report supports a search
     * etc. You can also change the default request config. For instance change how many rows are displayed by default.
     *
     * @param ViewDataTable $view
     */
    public function configureView(ViewDataTable $view)
    {
        $idDimension = Common::getRequestVar('idDimension', 0, 'int');
        if ($idDimension < 1) {
            return;
        }

        $isWidget = Common::getRequestVar('widget', 0, 'int');
        $module = Common::getRequestVar('module', '', 'string');
        if ($isWidget && $module !== 'Widgetize' && $view->isViewDataTableId(HtmlTable::ID)) {
            // we disable row evolution as it would not forward the idDimension when requesting the row evolution
            // this is a limitation in row evolution
            $view->config->disable_row_evolution = true;
        }

        $module = $view->requestConfig->getApiModuleToRequest();
        $method = $view->requestConfig->getApiMethodToRequest();
        $idReport = sprintf('%s_%s_idDimension--%d', $module, $method, $idDimension);

        if ($view->requestConfig->idSubtable) {
            $view->config->addTranslation('label', Piwik::translate('Actions_ColumnActionURL'));
        } elseif (!empty($this->dimension)) {
            $view->config->addTranslation('label', $this->dimension->getName());
        }

        $view->requestConfig->request_parameters_to_modify['idDimension'] = $idDimension;
        $view->requestConfig->request_parameters_to_modify['reportUniqueId'] = $idReport;
        $view->config->custom_parameters['scopeOfDimension'] = $this->scopeOfDimension;

        if ($this->scopeOfDimension === CustomDimensions::SCOPE_VISIT) {

            // Goal metrics for each custom dimension  of 'visit' scope is processed in Archiver via aggregateFromConversions
            $view->config->show_goals = true;

            $view->config->columns_to_display = array(
                'label', 'nb_visits', 'nb_uniq_visitors', 'nb_users', 'nb_actions', 'nb_actions_per_visit', 'avg_time_on_site', 'bounce_rate'
            );

            $view->config->filters[] = function (DataTable $table) use ($view) {
                $userId = new UserId();
                if (!$userId->hasDataTableUsers($table)) {
                    $view->config->removeColumnToDisplay('nb_users');
                }

                if ($table->getRowsCount() > 0 && !$table->getFirstRow()->hasColumn('nb_uniq_visitors')) {
                    $view->config->removeColumnToDisplay('nb_uniq_visitors');
                }
            };
        } elseif ($this->scopeOfDimension === CustomDimensions::SCOPE_ACTION) {
            $view->config->columns_to_display = array(
                'label', 'nb_hits', 'nb_visits', 'bounce_rate', 'avg_time_on_dimension', 'exit_rate', 'avg_time_generation'
            );

            $formatter = new Metrics\Formatter();

            // add avg_generation_time tooltip
            $tooltipCallback = function ($hits, $min, $max) use ($formatter) {
                if (!$hits) {
                    return false;
                }

                return Piwik::translate("Actions_AvgGenerationTimeTooltip", array(
                    $hits,
                    "<br />",
                    $formatter->getPrettyTimeFromSeconds($min, true),
                    $formatter->getPrettyTimeFromSeconds($max, true)
                ));
            };
            $view->config->filters[] = array('ColumnCallbackAddMetadata',
                array(
                    array('nb_hits_with_time_generation', 'min_time_generation', 'max_time_generation'),
                    'avg_time_generation_tooltip',
                    $tooltipCallback
                )
            );
        }

        $view->config->show_table_all_columns = false;
    }

    public function getMetrics()
    {
        $metrics = parent::getMetrics();

        if ($this->scopeOfDimension === CustomDimensions::SCOPE_ACTION) {
            $metrics['nb_visits'] = Piwik::translate('CustomDimensions_ColumnUniqueActions');
        }

        if (array_key_exists('nb_hits', $metrics)) {
            $metrics['nb_hits'] = Piwik::translate('General_ColumnNbActions');
        }

        return $metrics;
    }

    public function configureWidget(WidgetsList $widget)
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');

        if ($idSite < 1) {
            return;
        }

        $dimensions = $this->getActiveDimensionsForSite($idSite);

        foreach ($dimensions as $dimension) {
            if (!$dimension['active']) {
                continue;
            }

            if ($dimension['scope'] === CustomDimensions::SCOPE_ACTION) {
                $this->category = 'General_Actions';
            } elseif ($dimension['scope'] === CustomDimensions::SCOPE_VISIT) {
                $this->category = 'General_Visitors';
            } else {
                continue;
            }

            $this->widgetTitle = $dimension['name'];
            $this->widgetParams = array('idDimension' => $dimension['idcustomdimension']);

            parent::configureWidget($widget);
        }
    }

    public function configureReportingMenu(MenuReporting $menu)
    {
        // we handle this one manually in Menu.php
    }

    public function configureReportMetadata(&$availableReports, $infos)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $idSite = $this->getIdSiteFromInfos($infos);

        if (isset($idSite)) {
            $this->addReportMetadataForEachDimension($availableReports, $idSite);
        }
    }

    private function getActiveDimensionsForSite($idSite)
    {
        if (empty($this->dimensionCache[$idSite])) {
            $this->dimensionCache[$idSite] = array();

            $configuration = new Configuration();
            $dimensions    = $configuration->getCustomDimensionsForSite($idSite);

            foreach ($dimensions as $index => $dimension) {
                if ($dimension['active']) {
                    $this->dimensionCache[$idSite][] = $dimension;
                }
            }
        }

        return $this->dimensionCache[$idSite];
    }

    protected function addReportMetadataForEachDimension(&$availableReports, $idSite)
    {
        $dimensions = $this->getActiveDimensionsForSite($idSite);

        foreach ($dimensions as $dimension) {
            if ($this->initThisReportFromDimension($dimension)) {
                $availableReports[] = $this->buildReportMetadata();
            }
        }
    }

    private function initThisReportFromDimension($dimension)
    {
        $this->name = $dimension['name'];
        $this->menuTitle = $this->name;
        $this->widgetTitle = $this->name;
        $this->scopeOfDimension = $dimension['scope'];
        $dimensionField = CustomDimensionsRequestProcessor::buildCustomDimensionTrackingApiName($dimension);

        if ($this->scopeOfDimension === CustomDimensions::SCOPE_ACTION) {
            $this->category = 'General_Actions';
            $this->dimension = new CustomActionDimension($dimensionField, $this->name);
            $this->metrics = array('nb_hits', 'nb_visits');
            $this->processedMetrics = array(
                new AverageTimeOnDimension(),
                new BounceRate(),
                new ExitRate(),
                new AveragePageGenerationTime()
            );
        } elseif ($this->scopeOfDimension === CustomDimensions::SCOPE_VISIT) {
            $this->category = 'General_Visitors';
            $this->dimension = new CustomVisitDimension($dimensionField, $this->name);
            $this->metrics = array('nb_visits', 'nb_actions');
            $this->processedMetrics = array(
                new AverageTimeOnSite(),
                new BounceRate(),
                new ActionsPerVisit()
            );
        } else {
            return false;
        }

        $this->parameters = array('idDimension' => $dimension['idcustomdimension']);
        $this->order = 100 + $dimension['idcustomdimension'];

        return true;
    }

    protected function getIdSiteFromInfos($infos)
    {
        $idSites = $infos['idSites'];

        if (count($idSites) != 1) {
            return null;
        }

        $idSite = reset($idSites);

        return $idSite;
    }

}
