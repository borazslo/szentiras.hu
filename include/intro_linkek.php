<?

   miniframehead("<a href='/keres' class='minifrmh'>Linkek</a>",170,60);

   @mysql_select_db("linkdb")
                  or die("<p>Nem sikerult az adatbazist elerni, <br>MySQL hibauzenet:" . mysql_error());

   $rs=mysql_query("select did, title, url from tdlink where mod=1 order by reccreated desc limit 0,4");
   while(list($did,$title,$url)=mysql_fetch_row($rs)) {
    echo "<a href=/keres/openlink.php?did=$did&url=http://$url class=catlinksmall>- $title</a><br>";
   }

   miniframefoot();

   @mysql_select_db("kportal")
                  or die("<p>Nem sikerult az adatbazist elerni, <br>MySQL hibauzenet:" . mysql_error());

?>
