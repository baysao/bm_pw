#!/bin/bash
init(){
	sleep 10
	mysql -uroot $DB_DATABASE < /app/piwik.sql
	mysql -uroot $DB_DATABASE < /app/piwik_log_media.sql
	mysql -uroot $DB_DATABASE < /app/piwik_option.sql
	mysql -uroot $DB_DATABASE -e "ALTER TABLE piwik_site AUTO_INCREMENT = $SITE_START"
}
sleep 15
for DB_DATABASE in $DB_DATABASES;do
        echo "=> Creating database $DB_DATABASE"
        mysql -uroot -e "CREATE DATABASE IF NOT EXISTS $DB_DATABASE;"
        mysql -uroot -e "DROP USER '$DB_USER'@'localhost';"
        mysql -uroot -e "CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS'"
        mysql -uroot -e "GRANT ALL PRIVILEGES ON *.* TO '$DB_USER'@'localhost' WITH GRANT OPTION"
done
$@
