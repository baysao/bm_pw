<?php

namespace Piwik\Plugins\QualityAssurance;

use Piwik\Piwik;

class QualityAssurance extends \Piwik\Plugin
{
    public function postLoad()
    {
        Piwik::addAction('Template.leftColumnUserCountry', array('Piwik\Plugins\UserCountryMap\UserCountryMap', 'insertMapInLocationReport'));
    }

    public static function insertMapInLocationReport(&$out)
    {
        $out = '<h2>' . Piwik::translate('UserCountryMap_VisitorMap') . '</h2>';
        $out .= FrontController::getInstance()->fetchDispatch('UserCountryMap', 'visitorMap');
    }

	public function registerEvents()
	{
		return array(
			'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
			'AssetManager.getJavaScriptFiles' => 'getJsFiles',
		);
	}

	public function getJsFiles(&$jsFiles)
	{
//        $jsFiles[] = "plugins/QualityAssurance/javascripts/bootstrap.min.js";
        $jsFiles[] = "libs/bower_components/visibilityjs/lib/visibility.core.js";
        $jsFiles[] = "plugins/QualityAssurance/javascripts/vendor/raphael.min.js";
        $jsFiles[] = "plugins/QualityAssurance/javascripts/vendor/jquery.qtip.min.js";
        $jsFiles[] = "plugins/QualityAssurance/javascripts/vendor/kartograph.min.js";
        $jsFiles[] = "libs/bower_components/chroma-js/chroma.min.js";
        $jsFiles[] = "plugins/QualityAssurance/javascripts/visitor-map.js";
        $jsFiles[] = "plugins/QualityAssurance/javascripts/realtime-map.js";

		$jsFiles[] = "plugins/QualityAssurance/javascripts/main.js";
	}

	public function getStylesheetFiles(&$stylesheets)
	{
        $stylesheets[] = "plugins/QualityAssurance/stylesheets/visitor-map.less";
        $stylesheets[] = "plugins/QualityAssurance/stylesheets/realtime-map.less";
//        $stylesheets[] = "plugins/QualityAssurance/stylesheets/bootstrap.min.css";

		$stylesheets[] = "plugins/QualityAssurance/stylesheets/style.css";
	}
}