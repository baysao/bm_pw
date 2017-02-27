<?php
namespace Piwik\Plugins\QualityAssurance\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
//use Piwik\Plugins\QualityAssurance\Columns\DeviceType;

class getCat extends Base
{
    protected function init()
    {
        parent::init();
//        $this->dimension     = new DeviceType();
        $this->name          = Piwik::translate('QualityAssurance_AudienceByCategory');
        $this->documentation = ''; // TODO
        $this->order = 0;
        $this->widgetTitle  = 'QualityAssurance_AudienceByCategory';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("QualityAssurance_Time"));

//        $view->config->translations['value'] = 'Temperature in °C';
        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order = 'asc';
        $view->requestConfig->filter_limit = 10;
//        $view->config->columns_to_display  = array('label', 'value');
        $view->config->y_axis_unit = 'Audience'; // useful if the user requests the bar graph
        $view->config->show_exclude_low_population = false;
        $view->config->show_table_all_columns = false;
        $view->config->disable_row_evolution  = true;
        $view->config->max_graph_elements = 10;
        $view->config->metrics_documentation = array('value' => 'Documentation for metrics');
    }

}