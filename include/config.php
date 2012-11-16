<?

$config=1;

function dbconnect() {

         $host = "localhost";
         $uname = "root";
         $upass = "Felpecz";
         $dbname = "kportal";

         @mysql_connect($host, $uname, $upass)
                  or die("<p>Nem sikerult az adatbazisszervehez csatlakozni! <br>MySQL hibauzenet:" . mysql_error());
         @mysql_select_db($dbname)
                  or die("<p>Nem sikerult az adatbazist elerni, <br>MySQL hibauzenet:" . mysql_error());
}

?>
