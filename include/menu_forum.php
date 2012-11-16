<?

   miniframeemptyhead("Fórum",170,50);

   $query_k="select fkid,nev from fkategoria where jog=''";
   $kategoriak=mysql_query($query_k);
   while(list($fkid,$nev_k)=mysql_fetch_row($kategoriak)) {
       echo "<li class=catlinklarge><a href=forum.php?op=viewkat&fkid=$fkid class=catlinksmall>$nev_k</a>";

       $query_t="select ftid,nev from ftemakor where jog='' and fkid='$fkid' order by datum desc";
       $limit=" limit 0,3";
       $temakorok=mysql_query($query_t.$limit);
       $max=mysql_query($query_t);
       $mennyi=mysql_num_rows($max);
       while(list($ftid,$nev_t)=mysql_fetch_row($temakorok)) {
           echo "<br><a href=forum.php?op=view&ftid=$ftid class=link><small>$nev_t</small></a>";
       }
       if($mennyi>3)
         echo "<br><a href=forum.php?op=viewkat&fkid=$fkid class=link title='További témakörök'><small>>> További témakörök</small></a>";

       echo '</li>';
   }

   miniframefoot();

?>
