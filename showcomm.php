<?php
/*
  require("design.php");
  require("biblemenu.php");
  require("bibleconf.php");
  require("biblefunc.php");

  portalhead("Biblia - Magyarázatok");
  bibleleftmenu();
*/
  $ptitle = 'maci';
  if (!(empty($reftrans) or empty($did))) {
    $content .= showcomm($db,$reftrans,$did);
  }
  if($ptitle != 'maci') $title = $ptitle;
  //portalfoot();

?>