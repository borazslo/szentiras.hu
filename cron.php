<?php
/* mysql schema és example data export */
exec("mysql -N information_schema -e \"select table_name from tables where table_name like '".DBPREF."%'\" -u ".$mysqluser." --password=".$mysqlpw,$tables);
exec('mysqldump --opt --where="1 limit 5000" '.$mysqldb.' '.implode(' ',$tables).' -u '.$mysqluser.' --password='.$mysqlpw.' > tmp/mysql_sample.sql');
exec('mysqldump -d '.$mysqldb.' '.implode(' ',$tables).' -u '.$mysqluser.' --password='.$mysqlpw.' > tmp/mysql_schema.sql',$ret2);

/* html cache ürítés */
exec("find cache -regextype posix-extended -regex '^(cache/cached-).*' -mmin +".(getvar('cache_html_lifetime')/60000)." -delete");

exit;

?>