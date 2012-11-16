<?php

function portalhead($title){
echo"<html><head>\n";
echo"<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-2'>\n";
if (!empty($title)) {
  $titlestring = "Ökumenikus Ifjúsági Iroda - $title\n";
} else {
  $titlestring = "Ökumenikus Ifjúsági Iroda\n";
}
echo"<title>$titlestring</title>\n";
echo"<link rel='stylesheet' href='/include/style.css'>\n";
echo"</head>\n";
echo"<body bgcolor='#FFFFFF' topmargin='0' leftmargin='0'>\n";

echo"<table border='0' cellpadding='0' cellspacing='0' width='750'>\n";
echo"  <tr>\n";
echo"    <td valign='top'>\n";
   tophead();
echo"    </td>\n";
echo"  </tr>\n";
echo"  <tr>\n";
echo"    <td valign='top'>\n";
   topmenu();
echo"    </td>\n";
echo"  </tr>\n";
echo"  <tr>\n";
echo"    <td>\n";
echo"      <table border='0' cellpadding='0' cellspacing='0' width='750'>\n";
echo"      <tr>\n";
echo"        <td width='195' valign='top' background='/img/vmenupausebg2.jpg'>\n";
}


function tophead(){
echo"   <table border='0' cellpadding='0' cellspacing='0' width='750'>\n";
echo"   <tr>\n";
echo"      <td width='29'>&nbsp;</td>\n";
echo"      <td bgcolor='#373A8D' width='21'>&nbsp;</td>\n";
echo"      <td width='700' height='85'>\n";
echo"        <table width='700' border='0' cellpadding='5' cellspacing='0'>\n";
echo"        <tr>\n";
echo"          <td>\n";
echo"          <img src='/img/okilogo.jpg' width='690' height='108' align='middle' alt='Logo'>\n";
echo"          </td></tr></table>\n";
echo"      </td>\n";
echo"   </tr>\n";
echo"   </table>\n";
}

function topmenu(){
echo"    <table border='0' cellpadding='0' cellspacing='0' width='750'>\n";
echo"    <tr>\n";
echo"      <td background='/img/hmenubg.jpg' width='29' height='35'>&nbsp;</td>\n";
echo"      <td background='/img/crossbg.gif' width='21'>&nbsp;</td>\n";
echo"      <td valign='top' background='/img/hmenubg.jpg' width='400'>";
echo"      <span class='feher'>&nbsp;&nbsp;&nbsp;Ökumenikus Ifjúsági Iroda (ÖKI)</span>";
echo"      </td>\n";
echo"      <td background='/img/hmenubg.jpg' width='300'>&nbsp;</td>\n";
echo"    </tr>\n";
echo"    </table>\n";
}

function leftmenuhead() {
echo"        <table border='0' cellpadding='0' cellspacing='0' width='195'>\n";
echo"        <tr>\n";
echo"          <td width='15'></td>\n";
echo"          <td width='180' background='/img/vmenupausebg.jpg'>\n";
echo"            <table border='0' cellpadding='0' cellspacing='0' width='180'>\n";
echo"            <tr>\n";
echo"               <td height='2'></td>\n";
echo"            </tr>\n";
}

function leftmenugrouphead() {
echo"            <tr>\n";
echo"              <td background='/img/vmenugrouphead.jpg' height='10'>&nbsp;</td>\n";
echo"            </tr>\n";
echo"            <tr>\n";
echo"              <td background='/img/vmenubg.gif' valign='top' align='left'>\n";
echo"                <table border='0' cellpadding='0' cellspacing='0' width='180'>\n";
}

function leftmenuentry($text,$link){

echo"                <tr>\n";
echo"                  <td>\n";
echo"                    <table border='0' cellpadding='0' cellspacing='0' width='180'>\n";
echo"                    <tr>\n";
echo"                      <td width='40'>&nbsp;</td>\n";
echo"                      <td background='/img/vmenucolorbg.gif' width='140' align='left'>\n";
echo"                         <a href='". $link ."' class='menulink'>$text</a></td>\n";
echo"                    </tr>\n";
echo"                    </table>\n";
echo"                  </td>\n";
echo"                </tr>\n";
echo"                <tr>\n";
echo"                  <td>\n";
echo"                    <table border='0' cellpadding='0' cellspacing='0' width='180'>\n";
echo"                    <tr>\n";
echo"                      <td background='/img/vmenupausebg.jpg' height='3'><img src='/img/clear.gif'></td>\n";
echo"                    </tr>\n";
echo"                    </table>\n";
echo"                  </td>\n";
echo"                </tr>\n";
}

function leftmenusearchform(){

echo"                <tr>\n";
echo"                  <td>\n";
echo"                    <table border='0' cellpadding='0' cellspacing='0' width='180'>\n";
echo"                    <tr>\n";
echo"                      <td height='6'><img src='/img/clear.gif'></td>\n";echo"                    </tr>\n";
echo"                    </table>\n";
echo"                  </td>\n";
echo"                </tr>\n";
echo"                <tr>\n";
echo"                  <td>\n";
echo"                    <table border='0' cellpadding='0' cellspacing='0' width='180'>\n";
echo"                    <tr>\n";
echo"                      <td width='10'>&nbsp;</td>\n";
echo"                      <td width='170' align='left'>\n";
echo" <form action='/keres/quicksearch.php' method='post'>\n";
echo" <input type=text name='search' size=10 maxlength=20>&nbsp;\n";
echo" <input type='image' src='/img/keress.jpg' border=0>\n";
echo" </form>\n";
echo"                    </tr>\n";
echo"                    </table>\n";
echo"                  </td>\n";
echo"                </tr>\n";
echo"                <tr>\n";
echo"                  <td>\n";
echo"                    <table border='0' cellpadding='0' cellspacing='0' width='180'>\n";
echo"                    <tr>\n";
echo"                      <td height='6'><img src='/img/clear.gif'></td>\n";echo"                    </tr>\n";
echo"                    </table>\n";
echo"                  </td>\n";
echo"                </tr>\n";
}


function leftmenugroupfoot() {
echo"                </table>\n";
echo"              </td>\n";
echo"            </tr>\n";
}

function leftmenupause() {
echo"            <tr>\n";
echo"              <td height='25'>&nbsp;</td>\n";
echo"            </tr>\n";
}


function leftmenufoot(){
echo"            <tr>\n";
echo"              <td height='100%' valign='bottom'>&nbsp;</td>\n";
echo"            </tr>\n";
echo"            </table>\n";
echo"          </td>\n";
echo"        </tr>\n";
echo"        </table>\n";
echo"        </td>\n";
echo"        <td width='555' valign='top'>\n";
echo"          <table border='0' cellpadding='0' cellspacing='0' width='555'>\n";
echo"          <tr>\n";
echo"            <td width='25'></td>\n";
echo"            <td width='530'>\n";
echo"            <table border='0' cellpadding='0' cellspacing='0' width='530'>\n";
echo"            <tr>\n";
echo"              <td height='25'></td>\n";
echo"            </tr>\n";
echo"            <tr>\n";
echo"              <td>\n";
}


function footer() {
echo"    <tr>\n";

echo"        <td valign='top'>\n";
echo"        <table border='0' cellpadding='0' cellspacing='0' width='750'>\n";

echo"        <tr>\n";
echo"          <td width='295' height='3' valign='top'>\n";
echo"            <table border='0' cellpadding='0' cellspacing='0' width='295'>\n";
echo"            <tr>\n";
echo"              <td width='15' valign='top'>&nbsp;</td>\n";
echo"              <td width='280' height='3' valign='top' background='/img/vmenupausebg.jpg'>&nbsp;\n";
echo"              </td>";
echo"            </tr>\n";
echo"            </table>";
echo"          <td width='455' height='3' valign='center' background='/img/pixelhorborder.gif'>&nbsp;";
echo"          </td>";
echo"        </tr>\n";
echo"        <tr>\n";
echo"        <td width='295' valign='top'>\n";
echo"        <table border='0' cellpadding='0' cellspacing='0' width='295'>\n";
echo"        <tr>\n";
echo"          <td width='15' valign='top'></td>\n";
echo"          <td width='280' valign='top' background='/img/vmenupausebg.jpg'>\n";
echo"            <table border='0' cellpadding='0' cellspacing='0' width='280'>\n";
echo"            <tr>\n";
echo"               <td height='2'></td>\n";
echo"            </tr>\n";
echo"            <tr>\n";
echo"              <td background='/img/vmenubg.gif' valign='top' align='left'>\n";
echo"                <table border='0' cellpadding='0' cellspacing='0' width='280'>\n";
echo"                <tr>\n";
echo"                  <td>\n";
echo"                    <table border='0' cellpadding='0' cellspacing='0' width='280'>\n";
echo"                    <tr>\n";
echo"                      <td width='40'>&nbsp;</td>\n";
echo"                      <td background='/img/vmenucolorbg.gif' width='240' align='left'>\n";
echo"                      <span class='feher'>&copy; </span>";
echo"                      <a href='http://www.kereszteny.hu/oki' class='menulink'>ÖKI</a>";
echo"                      <span class='feher'> 2001.<br>Minden jog fenntartva.</span>";
echo"                      </td>\n";
echo"                    </tr>\n";
echo"                    </table>\n";
echo"                  </td>\n";
echo"                </tr>\n";
echo"                <tr>\n";
echo"                  <td>\n";
echo"                    <table border='0' cellpadding='0' cellspacing='0' width='280'>\n";
echo"                    <tr>\n";
echo"                      <td background='/img/vmenupausebg.jpg' height='3'><img src='/img/clear.gif'></td>\n";
echo"                    </tr>\n";
echo"                    </table>\n";
echo"                  </td>\n";
echo"                </tr>\n";
echo"                </table>\n";
echo"              </td>\n";
echo"            </tr>\n";
echo"            </table>\n";
echo"          </td>\n";
echo"        </tr>\n";
echo"        </table>\n";
echo"        </td>\n";
echo"        <td align='center'>\n";
echo"        <span class='alap'>Kérdések és megjegyzések:</span> <a href='mailto:oki@kereszteny.hu' class='link'>oki@kereszteny.hu</a>\n";
echo"        </td>\n";
echo"        </tr></table></td>";
echo"      </tr>\n";

}


function portalfoot(){
echo"              </td>\n";
echo"            </tr>\n";
echo"            </table>\n";
echo"          </td>\n";
echo"        </tr>\n";
echo"        </table>\n";
echo"      </td>\n";
echo"    </tr>\n";
echo"    </table>\n";
echo"  </td>\n";
echo"</tr>\n";
footer();
echo"</table>\n";
echo"</body>\n";
echo"</html>\n";
}


function textframehead(){

echo"<table width='100%' border=0 cellpadding=1 cellspacing=1>\n";
echo"<tr>\n";
echo"  <td width='100%'>\n";
echo"      <table width='100%' border=0 cellpadding=0 cellspacing=0>\n";
echo"      <tr>\n";
echo"        <td width='8'>&nbsp;</td>\n";
echo"        <td width='80%' height='13' valign='center' background='/img/horborder2.gif'>&nbsp;</td>\n";
echo"        <td width='20%'>&nbsp;</td>\n";
echo"      </tr>\n";
echo"      </table>\n";
echo"  </td>\n";
echo"</tr>\n";
echo"<tr>\n";
echo"  <td width='100%'>\n";
echo"    <table width='100%' border=0 cellpadding=0 cellspacing=0>\n";
echo"    <tr>";
echo"      <td width='21' background='/img/pixelborder.gif'>&nbsp;</td>\n";
echo"      <td width='0*'>\n";
}

function textframefoot(){
echo"      </td>\n";
echo"      <td width ='21' background='/img/pixelborder.gif'>&nbsp;</td>\n";
echo"      </tr></table></td>\n";
echo"</tr>\n";
echo"<tr>\n";
echo"  <td width='100%'>\n";
echo"         <table width='100%' border=0  cellpadding=0 cellspacing=0>\n";
echo"         <tr>\n";
echo"         <td width='20%'>&nbsp;</td>\n";
echo"         <td width='80%' height='13' background='/img/horborder2.gif'>&nbsp;\n";
echo"         <td width='8'>&nbsp;</td>\n";
echo"         </tr></table>\n";
echo"  </td>\n";
echo"</tr>\n";
echo"</table>\n";
}

function miniframehead($title, $framewidth,$titlewidth){
echo"<table width='" . $framewidth . "' border=0>\n";
echo"<tr>\n";
echo"  <td width='21' background='/img/pixelborder.gif'>&nbsp;</td>\n";
echo"  <td width='".($framewidth-42)."'>\n";
echo"    <table width='".($framewidth-42)."' border=0 cellspacing=0 cellpadding=0>\n";
echo"    <tr>\n";
echo"      <td width='40' height='22' background='/img/miniframehead1.jpg'>&nbsp;</td>\n";
echo"      <td width='".$titlewidth."' height='22' valign='center' background='/img/miniframehead2.jpg'><span class='minifrmh'>&nbsp;" . $title . "</span></td>\n";
echo"      <td width='24' height='22' background='/img/miniframehead3.jpg'>&nbsp;</td>\n";
echo"      <td width='".($framewidth-$titlewidth-106)."' height='22' background='/img/miniframehead4.jpg'>&nbsp;</td>\n";
echo"    </tr>\n";
echo"    </table>\n";
}


function miniframeemptyhead($title, $framewidth,$titlewidth){
echo"<table width='" . $framewidth . "' border=0>\n";
echo"<tr>\n";
echo"  <td width='21' background='/img/pixelborder.gif'>&nbsp;</td>\n";
echo"  <td width='".($framewidth-42)."'>\n";
echo"    <table width='".($framewidth-42)."' border=0 cellspacing=0 cellpadding=0>\n";
echo"    <tr>\n";
echo"      <td width='40' height='22' background='/img/miniframehead21.jpg'>&nbsp;</td>\n";
echo"      <td width='".$titlewidth."' height='22' valign='center' background='/img/miniframehead22.jpg'><span class='minifrmeh'>&nbsp;" . $title . "</span></td>\n";
echo"      <td width='24' height='22' background='/img/miniframehead23.jpg'>&nbsp;</td>\n";
echo"      <td width='".($framewidth-$titlewidth-106)."' height='22' background='/img/miniframehead4.jpg'>&nbsp;</td>\n";
echo"    </tr>\n";
echo"    </table>\n";
}


function miniframefoot(){
echo"  </td>\n";
echo"  <td width ='21' background='/img/pixelborder.gif'>&nbsp;</td>\n";
echo"</tr>\n";
echo"</table>\n";
}

function okileftmenu(){
  leftmenuhead();
  leftmenugrouphead();
  leftmenuentry("Bemutatkozás","/oki");
  leftmenuentry("Elérhetőségek","/oki/info.php");
  leftmenuentry("Projektek","/oki/projekt.php");
  leftmenugroupfoot();
  leftmenupause();
  leftmenugrouphead();
  leftmenuentry("Keresztény portál","../");
  leftmenugroupfoot();
  leftmenufoot();
}

?>
