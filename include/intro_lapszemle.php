<?

   miniframehead("<a href='/lapszemle' class='minifrmh'>Lapszemle</a>",170,60);

   @mysql_select_db("szemle")
                  or die("<p>Nem sikerult az adatbazist elerni, <br>MySQL hibauzenet:" . mysql_error());

   $rs1=mysql_query("select max(datum) as maxdat from lapsz");
   list($maxdat) = mysql_fetch_row($rs1);

   $rs2=mysql_query("select LID, ujsag.ujsag as ujsnev, cim from lapsz, ujsag where lapsz.ujsag=ujsag.UID and lapsz.datum='".$maxdat."'");
   while(list($lid,$ujsnev,$cim)=mysql_fetch_row($rs2)) {
    echo "<a href=/lapszemle/lapszemle.php?type=cikk&LID=$lid class=catlinksmall>- $cim</a><br>";
   }

   miniframefoot();

   @mysql_select_db("kportal")
                  or die("<p>Nem sikerult az adatbazist elerni, <br>MySQL hibauzenet:" . mysql_error());

?>
