; <?php exit; ?> DO NOT REMOVE THIS LINE
; file automatically generated or modified by Piwik; you can manually override the default values in global.ini.php by redefining them in this file.
[database]
host = "__DB_HOST__"
username = "__DB_USER__"
password = "__DB_PASS__"
dbname = "__DB_DATABASE__"
tables_prefix = "piwik_"
port=__DB_PORT__

[log]
log_writers[] = "file"
log_level = "DEBUG"
logger_file_path = "__APP_LOG__"

[Debug]
disable_merged_assets = 1

[General]
assume_secure_protocol=1
proxy_client_headers[] = HTTP_X_FORWARDED_FOR
proxy_host_headers[] = HTTP_X_FORWARDED_HOST
enable_processing_unique_visitors_day = 1
enable_processing_unique_visitors_week = 1
enable_processing_unique_visitors_month = 1
enable_processing_unique_visitors_year = 0
enable_processing_unique_visitors_range = 0
enable_trusted_host_check = 0
enable_load_data_infile = 0
salt = "7ec3223d93b4967360749f2d0da36906"
time_limit = 600
my_period = 600
my_nperiod_back = 1
; The list of periods that are available in the Piwik calendar
; Example use case: custom date range requests are processed in real time,
; so they may take a few minutes on very high traffic website: you may remove "range" below to disable this period
enabled_periods_UI = "hour,day,week,month,year,range"
enabled_periods_API = "hour,day,week,month,year,range"

[Tracker]
bulk_requests_use_transaction = 0

[Plugins]
Plugins[] = "CorePluginsAdmin"
Plugins[] = "CoreAdminHome"
Plugins[] = "CoreHome"
Plugins[] = "WebsiteMeasurable"
Plugins[] = "Diagnostics"
Plugins[] = "CoreVisualizations"
Plugins[] = "Proxy"
Plugins[] = "API"
Plugins[] = "Widgetize"
Plugins[] = "Transitions"
Plugins[] = "LanguagesManager"
Plugins[] = "Actions"
Plugins[] = "Dashboard"
Plugins[] = "MultiSites"
Plugins[] = "Referrers"
Plugins[] = "UserLanguage"
Plugins[] = "DevicesDetection"
Plugins[] = "UserCountry"
Plugins[] = "VisitsSummary"
Plugins[] = "VisitFrequency"
Plugins[] = "VisitTime"
Plugins[] = "VisitorInterest"
Plugins[] = "Monolog"
Plugins[] = "Login"
Plugins[] = "UsersManager"
Plugins[] = "SitesManager"
Plugins[] = "Installation"
Plugins[] = "CoreUpdater"
Plugins[] = "CoreConsole"
Plugins[] = "UserCountryMap"
Plugins[] = "Live"
Plugins[] = "PrivacyManager"
Plugins[] = "ImageGraph"
Plugins[] = "Annotations"
Plugins[] = "Overlay"
Plugins[] = "SegmentEditor"
Plugins[] = "Insights"
Plugins[] = "Morpheus"
Plugins[] = "BulkTracking"
Plugins[] = "DevicePlugins"
Plugins[] = "Intl"
Plugins[] = "Marketplace"
Plugins[] = "CustomPiwikJs"
Plugins[] = "DBStats"
Plugins[] = "Provider"
Plugins[] = "Bandwidth"
Plugins[] = "MediaAnalytics"
Plugins[] = "QoS"
Plugins[] = "QueuedTracking"

[PluginsInstalled]
PluginsInstalled[] = "Diagnostics"
PluginsInstalled[] = "Login"
PluginsInstalled[] = "CoreAdminHome"
PluginsInstalled[] = "UsersManager"
PluginsInstalled[] = "SitesManager"
PluginsInstalled[] = "Installation"
PluginsInstalled[] = "Monolog"
PluginsInstalled[] = "Intl"
PluginsInstalled[] = "CorePluginsAdmin"
PluginsInstalled[] = "CoreHome"
PluginsInstalled[] = "WebsiteMeasurable"
PluginsInstalled[] = "CoreVisualizations"
PluginsInstalled[] = "Proxy"
PluginsInstalled[] = "API"
PluginsInstalled[] = "ExamplePlugin"
PluginsInstalled[] = "Widgetize"
PluginsInstalled[] = "Transitions"
PluginsInstalled[] = "LanguagesManager"
PluginsInstalled[] = "Actions"
PluginsInstalled[] = "Dashboard"
PluginsInstalled[] = "MultiSites"
PluginsInstalled[] = "Referrers"
PluginsInstalled[] = "UserLanguage"
PluginsInstalled[] = "DevicesDetection"
PluginsInstalled[] = "Goals"
PluginsInstalled[] = "Ecommerce"
PluginsInstalled[] = "SEO"
PluginsInstalled[] = "Events"
PluginsInstalled[] = "UserCountry"
PluginsInstalled[] = "VisitsSummary"
PluginsInstalled[] = "VisitFrequency"
PluginsInstalled[] = "VisitTime"
PluginsInstalled[] = "VisitorInterest"
PluginsInstalled[] = "ExampleAPI"
PluginsInstalled[] = "ExampleRssWidget"
PluginsInstalled[] = "Feedback"
PluginsInstalled[] = "CoreUpdater"
PluginsInstalled[] = "CoreConsole"
PluginsInstalled[] = "ScheduledReports"
PluginsInstalled[] = "UserCountryMap"
PluginsInstalled[] = "Live"
PluginsInstalled[] = "CustomVariables"
PluginsInstalled[] = "PrivacyManager"
PluginsInstalled[] = "ImageGraph"
PluginsInstalled[] = "Annotations"
PluginsInstalled[] = "MobileMessaging"
PluginsInstalled[] = "Overlay"
PluginsInstalled[] = "SegmentEditor"
PluginsInstalled[] = "Insights"
PluginsInstalled[] = "Morpheus"
PluginsInstalled[] = "Contents"
PluginsInstalled[] = "BulkTracking"
PluginsInstalled[] = "Resolution"
PluginsInstalled[] = "DevicePlugins"
PluginsInstalled[] = "Heartbeat"
PluginsInstalled[] = "ProfessionalServices"
PluginsInstalled[] = "UserId"
PluginsInstalled[] = "QueuedTracking"
PluginsInstalled[] = "Bandwidth"
PluginsInstalled[] = "Provider"
PluginsInstalled[] = "DBStats"
PluginsInstalled[] = "Marketplace"
PluginsInstalled[] = "MediaAnalytics"
PluginsInstalled[] = "QoS"
PluginsInstalled[] = "CustomPiwikJs"

[QueuedTracking]
queueEnabled=true
redisPort=__REDIS_PORT__
processDuringTrackingRequest=true
numQueueWorkers=32
numRequestsToProcess=1
[RedisCache]
port=__REDIS_PORT__

[MediaAnalytics]
media_analytics_exclude_query_parameters = "enablejsapi,player_id"

