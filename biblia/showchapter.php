<?php

  require("design.php");
  require("biblemenu.php");
  require("bibleconf.php");
  require("biblefunc.php");

  portalhead("Biblia");
  bibleleftmenu();

  $script = explode("&",$REQUEST_URI);

  if (!(empty($reftrans) or empty($abbook) or empty($numch))) {
    list($res1, $res2, $res3, $res4)=listchapter($db, $reftrans, $abbook, $numch);
    showchapter($db, $res1, $res2, $res3, $res4);
    list($res5,$res6,$res7)=listcomm($db,$res4,$reftrans);
    showcomms($db,$res5,$reftrans,$res6,$res7);
  }

  portalfoot();

?>