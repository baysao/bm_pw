#!/bin/sh
app_config=/usr/share/nginx/www/config/config.ini.php
console=/usr/share/nginx/www/console
awk -v REDIS_PORT=$REDIS_PORT \
	-v DB_PORT=$DB_PORT \
	-v DB_HOST=$DB_HOST \
	-v DB_USER=$DB_USER \
	-v DB_PASS=$DB_PASS \
	-v DB_DATABASE=$DB_DATABASE \
	-v APP_LOG=$APP_LOG '{
	gsub(/__DB_PORT__/,DB_PORT,$0);
	gsub(/__DB_HOST__/,DB_HOST,$0);
	gsub(/__DB_PASS__/,DB_PASS,$0);
	gsub(/__DB_USER__/,DB_USER,$0);
	gsub(/__DB_DATABASE__/,DB_DATABASE,$0);
	gsub(/__REDIS_PORT__/,REDIS_PORT,$0);
	gsub(/__APP_LOG__/,APP_LOG,$0);
	print;
}' /app/config.ini.php > $app_config

awk -v HHVM_PORT=$HHVM_PORT  \
	-v IS_HHVM=$IS_HHVM \
	-v IS_FPM=$IS_FPM \
	-v PORT=$PORT '{
	gsub(/__HHVM_PORT__/,HHVM_PORT,$0);
	gsub(/__IS_HHVM__/,IS_HHVM,$0);
	gsub(/__IS_FPM__/,IS_FPM,$0);
	gsub(/__PORT__/,PORT,$0);
	print;
}' /app/bimax.conf > /etc/nginx/conf.d/bimax.conf
service nginx reload
enable="Provider UsersManager Actions Referrers DevicesDetection Widgetize VisitTime UserCountry Bandwidth UserCountry QoS" 
disable="UserLanguage UserId CustomPiwikJs DevicePlugins Overlay Transitions Events MediaAnalytics \ 
	VisitorInterest VisitFrequency DBStats Marketplace ScheduledReports SEO ExampleAPI ExampleRssWidget \
	Feedback MobileMessaging Heartbeat ProfessionalServices \
        ExamplePlugin Goals Ecommerce CustomVariables Resolution Contents"

for p in $enable;do $console plugin:activate $p ;done
for p in $disable;do $console plugin:deactivate $p ;done

sleep 10
$console --yes core:update
