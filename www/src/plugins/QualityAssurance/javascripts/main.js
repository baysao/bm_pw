/* Refresh widget bandwidth in overview */
$(function () {
    var refreshWidget = function (element, refreshAfterXSecs) {
        // if the widget has been removed from the DOM, abort
        if (!element.length || !$.contains(document, element[0])) {
            return;
        }
        function scheduleAnotherRequest() {
            setTimeout(function () {
                refreshWidget(element, refreshAfterXSecs);
            }, refreshAfterXSecs * 1000);
        }
        if (Visibility.hidden()) {
            scheduleAnotherRequest();
            return;
        }
        var lastMinutes = $(element).attr('data-last-minutes') || 3, translations = JSON.parse($(element).attr('data-translations'));
        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({
            module: 'API',
            method: 'QualityAssurance.overviewGetRowOne',
            format: 'json',
            lastMinutes: lastMinutes,
            metrics: 'audience_size',
            refreshAfterXSecs: 5
        }, 'get');
        ajaxRequest.setFormat('json');
        ajaxRequest.setCallback(function (data) {
            var audienceSize = data['audience_size']['value'];
            var refreshafterxsecs = data['refreshAfterXSecs'];
            var lastMinutes = data['lastMinutes'];
            var message = data['audience_size']['metrics'];

            $('.overview-widget-ausize-counter', element)
                .attr('title', message)
                .find('div').text(audienceSize);
            $('.overview-widget-ausize-widget', element).attr('data-refreshafterxsecs', refreshAfterXSecs).attr('data-last-minutes', lastMinutes);

            scheduleAnotherRequest();
        });
        ajaxRequest.send(true);
    };

    var exports = require("piwik/QualityAssurance");
    exports.initOverviewGetRowOne = function () {
        $('.overview-widget-ausize-widget').each(function () {
            var $this = $(this),
                refreshAfterXSecs = $this.attr('data-refreshAfterXSecs');
            if ($this.attr('data-inited')) {
                return;
            }

            $this.attr('data-inited', 1);
            setTimeout(function () {
                refreshWidget($this, refreshAfterXSecs);
            }, refreshAfterXSecs * 1000);
        });
    };

    var refreshWidgetSU = function (element, refreshAfterXSecs) {
        // if the widget has been removed from the DOM, abort
        if (!element.length || !$.contains(document, element[0])) {
            return;
        }
        function scheduleAnotherRequest() {
            setTimeout(function () {
                refreshWidgetSU(element, refreshAfterXSecs);
            }, refreshAfterXSecs * 1000);
        }
        if (Visibility.hidden()) {
            scheduleAnotherRequest();
            return;
        }
        var lastMinutes = $(element).attr('data-last-minutes') || 3, translations = JSON.parse($(element).attr('data-translations'));
        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({
            module: 'API',
            method: 'QualityAssurance.overviewGetRowOne',
            format: 'json',
            lastMinutes: lastMinutes,
            metrics: 'audience_size',
            refreshAfterXSecs: 5
        }, 'get');
        ajaxRequest.setFormat('json');
        ajaxRequest.setCallback(function (data) {
            var val = data['startup_time']['value'];
            var refreshafterxsecs = data['refreshAfterXSecs'];
            var lastMinutes = data['lastMinutes'];
            var message = data['startup_time']['metrics'];

            $('.overview-widget-startup_time-counter', element)
                .attr('title', message)
                .find('div').text(val);
            $('.overview-widget-startup_time-widget', element).attr('data-refreshafterxsecs', refreshAfterXSecs).attr('data-last-minutes', lastMinutes);

            scheduleAnotherRequest();
        });
        ajaxRequest.send(true);
    };

    var exports = require("piwik/QualityAssurance");
    exports.initOverviewGetRowOneSU = function () {
        $('.overview-widget-startup_time-widget').each(function () {
            var $this = $(this),
                refreshAfterXSecs = $this.attr('data-refreshAfterXSecs');
            if ($this.attr('data-inited')) {
                return;
            }

            $this.attr('data-inited', 1);
            setTimeout(function () {
                refreshWidgetSU($this, refreshAfterXSecs);
            }, refreshAfterXSecs * 1000);
        });
    };

    var refreshWidgetBR = function (element, refreshAfterXSecs) {
        // if the widget has been removed from the DOM, abort
        if (!element.length || !$.contains(document, element[0])) {
            return;
        }
        function scheduleAnotherRequest() {
            setTimeout(function () {
                refreshWidgetBR(element, refreshAfterXSecs);
            }, refreshAfterXSecs * 1000);
        }
        if (Visibility.hidden()) {
            scheduleAnotherRequest();
            return;
        }
        var lastMinutes = $(element).attr('data-last-minutes') || 3, translations = JSON.parse($(element).attr('data-translations'));
        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({
            module: 'API',
            method: 'QualityAssurance.overviewGetRowOne',
            format: 'json',
            lastMinutes: lastMinutes,
            metrics: 'audience_size',
            refreshAfterXSecs: 5
        }, 'get');
        ajaxRequest.setFormat('json');
        ajaxRequest.setCallback(function (data) {
            var val = data['bitrate']['value'];
            var refreshafterxsecs = data['refreshAfterXSecs'];
            var lastMinutes = data['lastMinutes'];
            var message = data['bitrate']['metrics'];

            $('.overview-widget-bitrate-counter', element)
                .attr('title', message)
                .find('div').text(val);
            $('.overview-widget-bitrate-widget', element).attr('data-refreshafterxsecs', refreshAfterXSecs).attr('data-last-minutes', lastMinutes);

            scheduleAnotherRequest();
        });
        ajaxRequest.send(true);
    };

    var exports = require("piwik/QualityAssurance");
    exports.initOverviewGetRowOneBR = function () {
        $('.overview-widget-bitrate-widget').each(function () {
            var $this = $(this),
                refreshAfterXSecs = $this.attr('data-refreshAfterXSecs');
            if ($this.attr('data-inited')) {
                return;
            }

            $this.attr('data-inited', 1);
            setTimeout(function () {
                refreshWidgetBR($this, refreshAfterXSecs);
            }, refreshAfterXSecs * 1000);
        });
    };

    var refreshWidgetBT = function (element, refreshAfterXSecs) {
        // if the widget has been removed from the DOM, abort
        if (!element.length || !$.contains(document, element[0])) {
            return;
        }
        function scheduleAnotherRequest() {
            setTimeout(function () {
                refreshWidgetBT(element, refreshAfterXSecs);
            }, refreshAfterXSecs * 1000);
        }
        if (Visibility.hidden()) {
            scheduleAnotherRequest();
            return;
        }
        var lastMinutes = $(element).attr('data-last-minutes') || 3, translations = JSON.parse($(element).attr('data-translations'));
        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({
            module: 'API',
            method: 'QualityAssurance.overviewGetRowOne',
            format: 'json',
            lastMinutes: lastMinutes,
            metrics: 'audience_size',
            refreshAfterXSecs: 5
        }, 'get');
        ajaxRequest.setFormat('json');
        ajaxRequest.setCallback(function (data) {
            var audienceSize = data['buffer_time']['value'];
            var refreshafterxsecs = data['refreshAfterXSecs'];
            var lastMinutes = data['lastMinutes'];
            var message = data['buffer_time']['metrics'];

            $('.overview-widget-buffer_time-counter', element)
                .attr('title', message)
                .find('div').text(audienceSize);
            $('.overview-widget-buffer_time-widget', element).attr('data-refreshafterxsecs', refreshAfterXSecs).attr('data-last-minutes', lastMinutes);

            scheduleAnotherRequest();
        });
        ajaxRequest.send(true);
    };

    var exports = require("piwik/QualityAssurance");
    exports.initOverviewGetRowOneBT = function () {
        $('.overview-widget-buffer_time-widget').each(function () {
            var $this = $(this),
                refreshAfterXSecs = $this.attr('data-refreshAfterXSecs');
            if ($this.attr('data-inited')) {
                return;
            }

            $this.attr('data-inited', 1);
            setTimeout(function () {
                refreshWidgetBT($this, refreshAfterXSecs);
            }, refreshAfterXSecs * 1000);
        });
    };
});
