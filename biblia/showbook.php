<?php

  require("design.php");
  require("biblemenu.php");
  require("bibleconf.php");
  require("biblefunc.php");

  portalhead("Biblia");
  bibleleftmenu();

    $script = explode("&",$REQUEST_URI);

    if (!(empty($reftrans) or empty($abbook))) {
        showbook($db, $reftrans, $abbook, listbook($db, $reftrans, $abbook));
    }

  portalfoot();

?>