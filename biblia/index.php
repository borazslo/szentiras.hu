<?php

  require("design.php");
  require("biblemenu.php");
  require("bibleconf.php");
  require("biblefunc.php");

  portalhead("Biblia");
  bibleleftmenu();

    $script = explode("&",$REQUEST_URI);

    showbible($db,listbible($db));

  portalfoot();

?>