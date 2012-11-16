<?

function jobbmenu($oldal) {
    global $config;
    $menu[1]="hirek";
    $menu[2]="lapszemle";
    $menu[3]="naptar";
//    $menu[4]="forum";
    $menu[4]="linkek";
    $menu[5]="admin";

    $menuk=count($menu);
    for($x=1;$x<=$menuk;$x++) {
        if($menu[$x]==$oldal) {
            require("menu_$menu[$x].php");
            $y=$x;
        }
    }

    for($x=1;$x<$menuk;$x++) {
        if($x!=$y) require("intro_$menu[$x].php");
    }
}


?>
