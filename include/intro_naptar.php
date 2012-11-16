<?

if(!isset($config)) {
    include("config.php");
    dbconnect();
}

   miniframehead("<a href=naptar.php class=minifrmh>Naptár</a>",170,50);

echo '<span class=kiscim>Események a következõ héten:</span><br>';

$ma=time();
$het=$ma+604800;

//1 óra=3600
//1 nap=86400
//1 hét=604800
//1 év=31408000

$query_n="select id,cim from hirek where ok='i' and jog='' and (kezddatum>='$ma' and kezddatum<='$het') order by kezddatum limit 0,4";
if(!$lekerdez=mysql_query($query_n))
  echo "HIBA!<br>".mysql_error();
while(list($id,$cim)=mysql_fetch_row($lekerdez)) {
    echo "<a href=hirek.php?op=view&hid=$id class=catlinksmall>- $cim</a><br>";
}
   miniframefoot();

?>
