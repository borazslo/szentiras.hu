<?
global $admin;
   miniframeemptyhead("Admin",170,50);

if(strstr ($admin, 'h'))
  echo "<li class=catlinklarge><a href=hirek.php class=catlinksmall>Hírek</a></li>";

if(strstr ($admin, 'n'))
  echo "<li class=catlinklarge><a href=naptar.php class=catlinksmall>Eseménynaptár</a></li>";

echo "<li class=catlinklarge><a href=forum.php class=catlinksmall>Fórum</a></li>";

if(strstr ($admin, 'k'))
  echo "<li class=catlinklarge><a href=../keres/admin.php class=catlinksmall>Linkek szerkesztése</a></li>";

if(strstr ($admin, 'l'))
  echo "<li class=catlinklarge><a href=../lapszemle/adm/admin.php class=catlinksmall>Lapszemle szerkesztése</a></li>";

echo "<li class=catlinklarge><a href='regist.php?".SID."&modos=' class=catlinksmall>Adatok módosítása</a></li>";
echo "<li class=catlinklarge><a href='index.php?".SID."&kilep=' class=catlinksmall>Kilépes</a></li>";

   miniframefoot();

?>
