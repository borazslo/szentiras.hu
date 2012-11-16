<?php

  require("design.php");
  require("biblemenu.php");
  require("bibleconf.php");
  require("biblefunc.php");

  portalhead("Keresés a Bibliában");
  bibleleftmenu();

  textframehead();

    echo "<p class='cim'>Keresés a Bibliában</p>";

    echo "<form action='searchbible.php' method='get'>\n";

    /* displaytextfield ($name,$size,$maxlength,$value,$comment,) */
    /* displaytextarea ($name,$cols,$rows,$value,$comment) */
    /* displayoptionlist($name,$size,$rs,$valuefield,$listfield,$default,$comment) */

    displaytextfield("texttosearch",30,40,"","Keresendõ:","alap");
    echo "<br>\n";
    displayoptionlist("reftrans",5,listbible($db),"did","name","1","Fordítás:","alap");
    echo "<br>\n";
    echo "<input type=reset value='Törlés' class='alap'> &nbsp;&nbsp;\n";
    echo "<input type=submit value='Küldés' class='alap'>\n";
    echo "</form>\n";

    textframefoot();

  portalfoot();

?>