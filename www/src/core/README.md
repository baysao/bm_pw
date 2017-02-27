--- Release Note ---

Piwik archiver - SBD version 1.0

Features:
 1. Allow aggregating raw log hourly and archive data in 'temp' DB tables.
 2. Disable the process that aggregating raw log to calculate Day archive,
	the new Day archive will be calculate from Hour archive.
 3. Allow rotating raw log tables every hour.
 4. Allow rotating (deleting) temporary archive DB tables also.
 
Attention on Usage & Limitation:
 0. Disable unique visitor calculation by append this to [General] section in config.ini.php:
 	enable_processing_unique_visitors_day = 0
	enable_processing_unique_visitors_week = 0
	enable_processing_unique_visitors_month = 0
	enable_processing_unique_visitors_year = 0
	enable_processing_unique_visitors_range = 0
 
 	And enable log fot debugging:
	[log]
	log_writers[] = file
	log_level = DEBUG
	logger_file_path = ""
 
 1. The Hour period is only recognized by the archiver, the other parts of 
 	Piwik core and plugins have not yet supported this feature. 
 2. The CronArchive and Archiver are kept untouched, all the archiving consoles,
 	methods are unchanged (including raw_logs_delete, archive_delete, etc.). 
 3. Unique Visitor calculation is regulated by the config 'enable_processing_unique_visitors_**period**'
 	under [General] section in config.ini.php. Unique Visitor can not be calculated 
 	in current version. Disable this config make nb_uniq_visitors = nb_visits.
 4. [!!] Archiving data for Hour can not be invalidated. That means once an archive
 	is calculated for an Hour period, it can't be re-calculated in case there's some
 	data comes lately. 
 	Workaround: Delete both numeric and blob temporary tables for that day, invalidate
 	that Day's archive and re-calculate for the whole day (with data for the whole day
 	is ready).
 5. [!!] Resulted by #4, archive for an Hour period should be calculated ONLY after raw
 	log of that day is completely collected (e.g. archive for #n hour at (n+1):5). 
 	=> So the archive_today.sh script should be executed with AT LEAST 1 hour interval,
 	 (for example, 5th minute of every hour) 
 6. When all the raw log tables are empty, the dashboard for site is redirected to 
 	"There's nothing tracked". Although when new data comes, the old data is presented 
 	just fine.
 7. Archiver is now relying on external script to rotating the tables. 
 
TODO:
 1. Implement Unique Visitor calculation
 2. Disable "There's nothing tracked" page.
 3. Implement table rotating mechanism into archiver for better control.
 