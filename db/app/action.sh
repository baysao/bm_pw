#!/bin/sh
TERM=xterm
mysql="mysql -A -N -f $DB_DATABASE"

mtmp=`mktemp`
echo "desc piwik_log_link_visit_action;" | mysql -f -N $DB_DATABASE  | awk '{print $1}' > $mtmp
nfields=`wc -l $mtmp | awk '{print $1}'`
nfields=$((nfields - 1))
fields=`tail -$nfields $mtmp| while read field;do echo -n $field;if [ -z $k ];then k=0;fi;k=$((k+1));if [ $k -lt $nfields ];then echo -n ","; fi;done`
rm -f $mtmp
get_idsites() {
	echo "select idsite from piwik_site;" | $mysql	
}
cur(){
	tmp=`mktemp`
	echo "select server_time from piwik_log_link_visit_action_tracker order by idlink_va asc limit 1;" >> $tmp
	echo "select server_time from piwik_log_link_visit_action_tracker order by idlink_va desc limit 1;" >> $tmp
	$mysql < $tmp
	rm -f $tmp
}


stat(){
        tmp=`mktemp`
        echo "select 'tracker=',count(*) from piwik_log_link_visit_action_tracker;"  >> $tmp
        echo "select 'action=',count(*) from piwik_log_link_visit_action;" >> $tmp
        $mysql < $tmp  | while read a b;do echo -n $a$b";";done;echo
        rm -f $tmp
}



deleteOld(){
	tmp=`mktemp`
	n=$1
	if [ -z "$n" ];then n=7;fi
	date1=`date -u +"%Y_%m" -d "$n days ago"`
	date=`date -u +"%Y_%m_%d" -d "$n days ago"`
	old="piwik_log_link_visit_action_${date}"
	echo "show tables like 'piwik_log_link_visit_action_${date1}_%';" | $mysql | awk -v old=$old '$1 < old {print "drop table",$1,";"}' > $tmp
	$mysql < $tmp
	rm -f $tmp
	
}
reset(){
	tmp=`mktemp`
	echo "show tables like 'piwik_log_link_visit_action_2%';"  | mysql -N -f $DB_DATABASE  | awk '{print "drop table", $1";"}' >> $tmp
	echo "show tables like 'piwik_log_link_visit_action_tracker_%';"  | mysql -N -f $DB_DATABASE  | awk '{print "drop table", $1";"}' >> $tmp
	echo "show tables like 'piwik_archive_blob_%';"  | mysql -N -f $DB_DATABASE  | awk '{print "drop table", $1";"}' >> $tmp
	echo "show tables like 'piwik_archive_numeric_%';"  | mysql -N -f $DB_DATABASE  | awk '{print "drop table", $1";"}' >> $tmp
	echo "show tables like 'piwik_archive_temp_%';"  | mysql -N -f $DB_DATABASE  | awk '{print "drop table", $1";"}' >> $tmp
	echo "truncate piwik_log_link_visit_action;"  | mysql -N -f $DB_DATABASE >> $tmp
	echo "truncate piwik_log_visit;"  | mysql -N -f $DB_DATABASE >> $tmp
	echo "truncate piwik_log_action;"  | mysql -N -f $DB_DATABASE >> $tmp
	echo "truncate piwik_sequence;"  | mysql -N -f $DB_DATABASE >> $tmp
	#rd="redis-cli -p $REDIS_PORT"
	#echo 'keys *' | $rd  | while read k;do echo "del $k" | $rd;done
	mysql -N -f $DB_DATABASE < $tmp
	rm -f $tmp
}
switch_off(){
        date=$1
        hour=$2
        datep=`echo $date | tr -s '-' '_'`
        action="piwik_log_link_visit_action"
        table=$action
        tmp=`mktemp`
        echo "rename table $action to ${table};" >> $tmp
        echo "create table $action like $table;" >> $tmp
        cat $tmp
        $mysql < $tmp
        rm -f $tmp
}

load_append(){
        date=$1
        hour=$2
        datep=`echo $date | tr -s '-' '_'`
        action=piwik_log_link_visit_action
        table=${action}_${datep}_$hour
        tmp=`mktemp`
	echo "insert into $action($fields) select $fields from $table where server_time >= '$date $hour:00:00' and server_time <= '$date $hour:59:59';" >> $tmp
        cat $tmp
        $mysql < $tmp
        rm -f $tmp
}
reduceHourly() {
	prefix=$1
	action=piwik_log_link_visit_action
	time="`echo "select DATE(server_time),HOUR(server_time),MINUTE(server_time) from ${action}$prefix limit 1;" | $mysql`"
	#time="`echo "select DATE(server_time),HOUR(server_time),MINUTE(server_time) from $action order by idlink_va desc limit 1;" | $mysql`"
	echo $time
	if [ -z "$time" ];then exit 0;fi
	date=`echo $time | awk '{print $1}'`
	daten=`echo "$date" | tr -s '-' '_'`
	hour=`echo $time | awk '{print $2}'`
	min1=`echo $time |awk '{print $3}'`
	min=`echo $min1 | awk '{print $1 - $1%10}'`
	if [ $hour -lt 10 ];then hour=0$hour;fi
#	if [ $min -lt 10 ];then min=0$min;fi
	mmago=`echo |awk '{print 40}'`
	now=`date  -u +%Y-%m-%d-%H-%M -d "$mmago minutes ago"`
	if [ ${date}-${hour}-${min} \> $now ];then
		exit 0
	fi
	echo "${date}-${hour}-${min} vs  $now"
	tmp=`mktemp`
	table="${action}_${daten}_${hour}_${min}"
	echo "create table $table like $action;" >> $tmp
	echo "insert into $table select * from ${action}$prefix where server_time >= '$date $hour:$min:00' and server_time <= '$date $hour:$((min+9)):59';" >> $tmp
	echo "delete ${action}$prefix from ${action}$prefix inner join $table on ${action}$prefix.idlink_va = ${table}.idlink_va;" >> $tmp
	cat $tmp
	$mysql < $tmp
	rm -f $tmp
}
switch_on(){
	date=$1
	hour=$2
	datep=`echo $date | tr -s '-' '_'`
	action=piwik_log_link_visit_action
	table=${action}_${datep}_$hour
	tmp=`mktemp`
	
        echo "drop table if exists $action;" >> $tmp
        echo "rename table $table to ${action};" >> $tmp
	cat $tmp
        $mysql < $tmp
	rm -f $tmp
}
load(){
	date=$1
	hour=$2
	tmp=`mktemp`
	dir=/root/mysql/data
	mkdir -p $dir
	datep=`echo $date | tr -s '-' '_'`
	outfile="$dir/${date}_${hour}.csv"
	action=piwik_log_link_visit_action
	table=${action}_${datep}_$hour
	echo "create table if not exists $table like ${action}_tracker;" >> $tmp
	echo "load data local infile '$outfile' into table $table fields terminated by ',';" >> $tmp
	cat $tmp
        $mysql < $tmp
	rm -f $tmp
}
rotate(){
        tmp=`mktemp`
	tracker=piwik_log_link_visit_action_tracker
        mysqladmin processlist | grep -v grep | grep UPDATE > /dev/null
	to=0
	val=$?
	while [ $val -ne 1 ];do
		echo $val
		if [ $to -eq 300 ];then
			break
		fi
		sleep 1
        	mysqladmin processlist | grep -v grep | grep UPDATE > /dev/null
		val=$?
		to=$((to + 1))	
	done
        mysqladmin processlist | awk '/admin/ && /update/ && $2 ~ /^[0-9]/ {print "KILL "$2";"}' | mysql
	i=0
	n=`echo "show tables like '${tracker}_tmp$i';" | $mysql |wc -l`
	while [ $n -ne 0 ];do
		i=$((i + 1))
		n=`echo "show tables like '${tracker}_tmp$i';" | $mysql |wc -l`
	done
	table=${tracker}_tmp$i
	echo "create table ${table}_ like $tracker;" >> $tmp
        echo "rename table $tracker to ${table},${table}_ to $tracker;" >> $tmp
	cat $tmp
        $mysql < $tmp
        rm -f $tmp
}
extractTable(){
	tmp=`mktemp`	
	table=$1
	dir=/root/mysql/data
	action=piwik_log_link_visit_action
	mkdir -p $dir
	echo "select distinct CONCAT(DATE(server_time),' ', HOUR(server_time)) from $table;" | $mysql | while read time;do
#		time="2016-10-11 13"
		p=`echo $time | tr -s ' ' '_'`
		outfile="$dir/${p}.csv" 
		echo "select * from $table where server_time >= '$time:00:00' and server_time <= '$time:59:59';" > $tmp
		cat $tmp
		$mysql < $tmp | sed 's/\t/,/g' >> $outfile
	done
	rm -f $tmp

}
dropTmp(){
	tmp=`mktemp`	
        if [ $# -ne 0 ];then
                for i in $@;do
                        table=piwik_log_link_visit_action_tracker_tmp$i
                        echo "drop table $table;"  >> $tmp
                done
        else
                echo "show tables like 'piwik_log_link_visit_action_tracker_tmp%';" | $mysql | while read table;do
                        echo "drop table $table;"  >> $tmp
                done
        fi
	$mysql < $tmp
	rm -f $tmp
}

extractAll(){
	tmp=`mktemp`
	action=piwik_log_link_visit_action
        if [ $# -ne 0 ];then
                for i in $@;do
                        table=piwik_log_link_visit_action_tracker_tmp$i
                        echo "insert into ${action}($fields) select $fields from $table;"  >> $tmp
                done
        else
                echo "show tables like 'piwik_log_link_visit_action_tracker_tmp%';" | $mysql | while read table;do
                        echo "insert into ${action}($fields) select $fields from $table;"  >> $tmp
                done
        fi
	cat $tmp
	$mysql < $tmp
	rm -f $tmp
}


extract(){
	if [ $# -ne 0 ];then
		for i in $@;do
			table=piwik_log_link_visit_action_tracker_tmp$i
			 extractTable $table
		done
	else
		echo "show tables like 'piwik_log_link_visit_action_tracker_tmp%';" | $mysql | while read table;do
			extractTable $table		
		done
	fi
}
$@
