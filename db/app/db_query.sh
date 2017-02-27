#!/usr/bin/env bash
mysql="mysql pw2 -N -s -A"

#Copy this file to /data/bimax/pw#/db/etc/

#this takes 3 arguments for archive type: (blob or numeric)
# and archive day (e.g: 2016_11_24)
# and metric (e.g: Actions_actions, done, done%. This argument accepts wildcard %)

ar_type=$1
day=$2
metric=$3

if [ $# -lt 3 ]; then
    echo "!! Invalid input argument"
    exit 1
fi

output="/app/out/archive_${ar_type}_${day}_$metric"
ofile="${output}.txt"

echo "select idsite from piwik_site;" | $mysql | while read site; do
    echo "select concat_ws(',', name, date1, date2, ts_archived) from piwik_archive_temp_${ar_type}_${day} where idsite=$site and name like '$metric';" | $mysql  >> $ofile
    #/usr/bin/python archive_blob.py $output $day
    #echo "run test suite for site $site"
done
