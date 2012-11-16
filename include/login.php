<?php

            require("design.php");
            require("gen.inc");
            require("jobbmenu.php");

// CGI atiranyitas eltûntetése az url-bõl (lásd 4. cikk)
$reszek=explode('/',$PHP_SELF); 
$PHP_SELF=$reszek[count($reszek)-1];

if(!isset($config)) include("config.php");
dbconnect();

class Login {
	var
		$loginNev,
		$helyesBelepes,
		$voltProbalkozas;

	function Login() {
		global $kilep;
		$this->helyesBelepes = false;
		$this->voltProbalkozas = false;
		$this->loginNev = "";
		if (isset($kilep))
		//a $kilep változóval lehet jelezni, hogy
		//ki kell léptetni a felhasználót
			$this->kileptet();
	}
	
	function beleptet() {
		global $uLogin, $uNev, $uJelszo,$tnev,$admin,$csoport;
		global $HTTP_SESSION_VARS;
                $uJelszo=base64_encode($uJelszo);

		if (isset($uLogin)) {
		//az urlapból jöttek adatok
			$query = "SELECT login,ok,tnev,jogok,csoport " .
				"FROM user " .
				"WHERE login='$uNev' " .
					"AND passw='$uJelszo'";
			if ($eredm = mysql_query($query) and mysql_num_rows($eredm)==1) {
			/* helyesek voltak az adatok -> el kell tárolni
			   a session-ben a felhasználó azonosítóját */

                                list($login,$ok,$tnev,$jogok,$csoport)=mysql_fetch_row($eredm);

                                if($ok!='i') {
                                    $this->loginUrlap1();
                                    exit;
                                }

				global $loginNev;
				$loginNev=$uNev;
				session_register("loginNev");

                                setcookie("tnev","$tnev");
                                setcookie("admin","$jogok");
                                setcookie("csoport","$csoport");
                                $admin=$jogok;

                                //Lastlogin mentése:
                                $lastlogin=date("Y-m-d H:i:s", time());
                                $modosit=mysql_query("update user set lastlogin='$lastlogin' where login='$uNev'");

				$this->helyesBelepes = true;
				$this->voltProbalkozas = true;
	
				$this->loginNev = $uNev;
			} else
				$this->voltProbalkozas = true;
		} elseif (isset($HTTP_SESSION_VARS["loginNev"])) {
		//session-bõl jöttek az adatok
			$this->loginNev=$HTTP_SESSION_VARS["loginNev"];
			$this->helyesBelepes = true;			
		}
		
		if (!$this->helyesBelepes) {
		//nem lepett be a felhasználó, ûrlapot kell neki kirakni
			$this->loginUrlap();
			exit();
		}		
	}
	
	function kileptet() {
		global $PHP_SELF;
		session_unregister("loginNev");		
		header("Location: $PHP_SELF");
                setcookie("tnev");
                setcookie("admin");
	}

        function loginUrlap1() {
            global $PHP_SELF,$uNev,$uJelszo,$admin;

            portalhead("Belépés");
            leftmenu();

            echo '<table width=100% border=0 cellspacing=5><tr>
                 <td width=350 valign=top>';

            textframehead();

            echo '<table width=100% height=100% bgcolor=#eeeeee><tr><td valign=top>';
            echo "<p class=alap>Kis türelmedet kérjük!
            <br>A(z) <b>$uNev</b> bejelentkezési néven még nem tudsz belépni.<br>Emailben értesítést küldünk
            az engedélyezésrõl.</p>";
            echo '</td></tr></table>';

            textframefoot();

            echo '</td><td width=170 valign=top>';

            jobbmenu('');

            echo '</td></tr></table>';

            portalfoot();

        }

	function loginUrlap() {
            global $PHP_SELF,$admin;

            portalhead("Belépés");
            leftmenu();

            echo '<table width=100% border=0 cellspacing=5><tr>
                 <td width=350 valign=top>';

            textframehead();

                echo '<table width=100% height=100% bgcolor=#eeeeee><tr><td valign=top class=alap><p class=alcim>Belépés</p>';
                                ?>
			<?= ($this->voltProbalkozas ?
				"<span class=hiba>Hibás név vagy jelszó!</span>" :
				"Belépés csak regisztrált felhasználóknak!
                               <br>Ha még nem vagy regisztrálva, a <a href=regist.php class=link1>regisztráció</a> oldalon megteheted.")?>
			<form action='<? echo $PHP_SELF; ?>' method="post">
                                <table><tr><td class=alap>
				Bejelentkezési név: </td><td><input type="text" name="uNev"></td></tr>
				<tr><td class=alap>Jelszó: </td><td><input type="password" name="uJelszo"></td></tr>
				<tr><td colspan=2><input type="submit" class=kiscim name="uLogin" value="Belépés"></td></tr>
                                </table>

			</form>
		<?
                  echo '</td></tr></table>';
                  textframefoot();

            echo '</td><td width=170 valign=top>';
            jobbmenu('');

            echo '</td></tr></table>';

            portalfoot();

	}
}

session_start();
?>
