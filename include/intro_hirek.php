<?

   miniframehead("<a href=hirek.php class=minifrmh>Hírek</a>",170,50);
   if(!isset($config)) {
       include("config.php");
       dbconnect();
   }
echo '<span class=kiscim>Legfrissebb hírek:</span>';
$hirek=mysql_query("select id,cim from hirek where ok='i' and jog='' order by bekulddatum desc limit 0,4");
while(list($hid,$hcim)=mysql_fetch_row($hirek)) {
    echo "<br><a href=hirek.php?op=view&hid=$hid class=catlinksmall>- $hcim</a>";
}
echo "<br><a href=hirek.php class=link title='További hírek'><small>>> További hírek</small></a>";

   miniframefoot();

?>
