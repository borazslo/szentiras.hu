<?php

/* include and print an HTML file */
function inchtml ($dir, $filename) {
  $fp = fopen($dir . "/" . $filename, "r");
  while ($buffer = fgets($fp,4096)) {
    if (eregi("<!--#include virtual=",$buffer)) {
	$pieces = explode("\"", $buffer);
	inchtml($dir, $pieces[1]);
    } else {
    echo $buffer;
    }
  }
}

?>
