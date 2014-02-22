<?php
echo "<pre>\n";

switch ($_REQUEST['type']) {
/* HOURLY cron */
case "hourly":
	/* html cache ürítés */ //find -newermt "20130207003851" -ls
	exec("find cache -regextype posix-extended -regex '^(cache/cached-).*' -newermt \"".date('Y-m-d H:i:s',strtotime("-".getvar('cache_html_lifetime')))."\" -delete");
	echo "html cache ürítve\n";
	break;
exit;

/* DAILY cron */
case "daily":
	/* mysql stats_search és stats_texttosearch cache ürítése */
	$query = "DELETE FROM ".DBPREF."stats_search WHERE 
		resultupdated > '".date('Y-m-d H:i:s',strtotime("-".getvar('cache_mysql_lifetime')))."' 
		AND searchcount < 2 ";
	$stmt = $db->prepare($query);$stmt->execute();
	$stmt = $db->prepare("optimize table ".DBPREF."stats_search");$stmt->execute();
	
	$query = "DELETE FROM ".DBPREF."stats_texttosearch WHERE 
		date > '".date('Y-m-d H:i:s',strtotime("-".getvar('cache_mysql_lifetime')))."' ";
	$stmt = $db->prepare($query);$stmt->execute();
	$stmt = $db->prepare("optimize table ".DBPREF."stats_texttosearch");$stmt->execute();
	echo "mysql cache ürítve\n";

	/* mysql schema és example export */
	exec("mysql -N information_schema -e \"select table_name from tables where table_name like '".DBPREF."%'\" -u ".$mysqluser." --password=".$mysqlpw,$tables);
	exec('mysqldump --opt --skip-dump-date --where="1 limit 400" '.$mysqldb.' '.implode(' ',$tables).' -u '.$mysqluser.' --password='.$mysqlpw.' > tmp/mysql_sample.sql');
	exec('mysqldump -d '.$mysqldb.' '.implode(' ',$tables).' -u '.$mysqluser.' --skip-dump-date --password='.$mysqlpw.' > tmp/mysql_schema.sql',$ret2);
	echo "mysql schema és example data export készen\n";	
	break;

/* WEEKLY cron */
case "weekly":
	/* mysql data export */
	exec("mysql -N information_schema -e \"select table_name from tables where table_name like '".DBPREF."%'\" -u ".$mysqluser." --password=".$mysqlpw,$tables);
	exec('mysqldump --opt '.$mysqldb.' '.implode(' ',$tables).' -u '.$mysqluser.' --password='.$mysqlpw.' > tmp/mysql_backup_'.date('YmdHis').'.sql');
	echo "mysql schema és example data export készen\n";
	break;

/* */
default:
	echo "nem csináltam semmit...\n";
break;
}

exit;

?>