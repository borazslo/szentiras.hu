<?php

  require("../include/design.php");
  require("../include/biblemenu.php");
  require("../include/bibleconf.php");
  require("../include/biblefunc.php");

  portalhead("Biblia");
  bibleleftmenu();

  $script = explode("&",$_SERVER['REQUEST_URI']);

  if (empty($reftrans)) $reftrans = 1;
    showtrans($db, $reftrans, listtrans($db, $reftrans));
  

  portalfoot();

?>