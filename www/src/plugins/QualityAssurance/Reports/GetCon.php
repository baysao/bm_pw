<?php
namespace Piwik\Plugins\QualityAssurance\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
//use Piwik\Plugins\QualityAssurance\Columns\DeviceType;

class getCon extends Base
{
    protected function init()
    {
        parent::init();
//        $this->dimension     = new DeviceType();
        $this->name          = Piwik::translate('QualityAssurance_AudienceByConnectionSpeed');
        $this->documentation = ''; // TODO
        $this->order = 0;
        $this->widgetTitle  = 'QualityAssurance_AudienceByConnectionSpeed';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate("QualityAssurance_Time"));

//        $view->config->translations['value'] = 'Temperature in °C';
        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order = 'asc';
        $view->requestConfig->filter_limit = 24;
//        $view->config->columns_to_display  = array('label', 'value');
//        $view->config->y_axis_unit = '°C'; // useful if the user requests the bar graph
        $view->config->show_exclude_low_population = false;
        $view->config->show_table_all_columns = false;
        $view->config->disable_row_evolution  = true;
        $view->config->max_graph_elements = 24;
        $view->config->metrics_documentation = array('value' => 'Documentation for metrics');
    }

}