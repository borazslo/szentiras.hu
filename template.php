<!DOCTYPE html>
<!-- HTML5 Mobile Boilerplate -->
<!--[if IEMobile 7]><html class="no-js iem7"><![endif]-->
<!--[if (gt IEMobile 7)|!(IEMobile)]><!--><html class="no-js" lang="en"><!--<![endif]-->

<!-- HTML5 Boilerplate -->
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if (IE 7)&!(IEMobile)]><html class="no-js lt-ie9 lt-ie8" lang="en"><![endif]-->
<!--[if (IE 8)&!(IEMobile)]><html class="no-js lt-ie9" lang="en"><![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"><!--<![endif]-->

<head>

	<meta charset="utf8">
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta property="fb:admins" content="1819502454" />
	<title><?php if(isset($pagetitle)) echo $pagetitle; ?></title>
	<meta name="description" content="A teljes Szentírás, azaz a Biblia magyarul az interneten: katolikus és protestáns fordításban">
	<meta name="keywords" content="biblia, katolikus, protestáns, szentírás, keresztény, keresztyén, református, hivatalos">

	<meta name="author" content="szentiras.hu">

	<meta http-equiv="cleartype" content="on">

	<meta property="og:image" content="http://szentiras.hu/img/biblia.jpg">
	<?php echo $meta ?>

	
	<!--<link rel="shortcut icon" href="/favicon.ico">-->

	<!-- Responsive and mobile friendly stuff -->
	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Stylesheets -->
	<link rel="stylesheet" href="<?php echo $fileurl; ?>css/html5reset.css" media="all">
	<link rel="stylesheet" href="<?php echo $fileurl; ?>css/responsivegridsystem.css" media="all">
	<link rel="stylesheet" href="<?php echo $fileurl; ?>css/col.css" media="all">
	<link rel="stylesheet" href="<?php echo $fileurl; ?>css/2cols.css" media="all">
	<!--<link rel="stylesheet" href="<?php echo $fileurl; ?>css/3cols.css" media="all">
	<link rel="stylesheet" href="<?php echo $fileurl; ?>css/4cols.css" media="all">-->
	<link rel="stylesheet" href="<?php echo $fileurl; ?>css/search.css" media="all">
	

	<!-- Responsive Stylesheets 
	<link rel="stylesheet" media="only screen and (max-width: 1024px) and (min-width: 769px)" href="/css/1024.css">
	<link rel="stylesheet" media="only screen and (max-width: 768px) and (min-width: 481px)" href="/css/768.css">
	<link rel="stylesheet" media="only screen and (max-width: 480px)" href="/css/480.css"> -->

	<!-- BIBLIA STYLLESHEETS -->
	<link rel="stylesheet" href="<?php echo $fileurl; ?>css/style.css" media="all">
	
	<!-- All JavaScript at the bottom, except for Modernizr which enables HTML5 elements and feature detects -->
	<script src="<?php echo $fileurl; ?>js/modernizr-2.5.3-min.js"></script>

	
	<style type="text/css">

	/*  THIS IS JUST TO GET THE GRID TO SHOW UP.  YOU DONT NEED THIS IN YOUR CODE */

	#maincontent .col {
/*		background: #ccc;
		background: rgba(204, 204, 204, 0.85);
*/
	}

	</style>

</head>

<body>
<script src="http://beta.szentiras.hu/js/search.js"></script>
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-36302080-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

<div id="skiptomain"><a href="#maincontent">írány a tartalom</a></div>

<div id="wrapper">
	<div id="headcontainer">
		<?php 
        if(getvar('dolgozunk')=='true') 
		echo '<div algin="center" style="background-color:red;color:white;text-align:center">Éppen fejlesztünk! Ha furcsa dolgot lát, kérjük frissítse az oldalt, vagy térjen vissza pár perc múlva!</div>'; 
		
        foreach($translations as $t) {            
            if(getvar('frissitunk_'.$t['abbrev'])=='true') 
                echo '<div algin="center" style="background-color:red;color:white;text-align:center">Éppen frissítjük a '.$t['abbrev'].' szövegeket! Az ebből fakadó hibákért elnézést kérünk!</div>'; 
        }
		//echo '<div algin="center" style="background-color:red;color:white;text-align:center">FIGYELEM! Ma éjjel (április 6.) 23:00 és hajnali 01:00 között teljes leállás várható adatbázis frissítés miatt.</div>'; 
		
		?>
		<header>
		<!--<?php if(isset($pagetitle)) echo "<h1>".$pagetitle."</h1>"; ?>-->
		<h1><a href="<?php if(isset($baseurl)) echo $baseurl; ?>" style="color:white"><?php if(isset($sitetitle)) echo $sitetitle; ?></a></h1>
		<?php if(isset($subsitetitle)) echo "<h2>".$subsitetitle."</h2>"; ?>
		<!--<p class="introtext">You're gonna have to view the source to grab the code here.</p>-->
		</header>
	</div>
	<div id="maincontentcontainer">
		<div id="maincontent">

			<div class="section group">	
										
				<div class="col span_2_of_2" style="float: right;position: relative;">
				<div id="share"><?php echo $share; ?></div>
				<?php if(isset($title)) echo "<h4>".$title."</h4>"; ?>
				<?php if(isset($hir)) 
					echo '<div algin="center" style="background-color:#373A8D;color:white;padding-left:3px;margin-bottom:3px">'.$hir.'</div>'; 
				?>
				<?php if(isset($hirek) and is_array($hirek)) { 
					if(!is_array($hirek[0])) $hirek[0] = $hirek;
					foreach($hirek as $hir) {
						echo '<div algin="center" class="menu" style="background-color:#9DA7D8;color:white;padding-left:3px;margin-bottom:3px"><div class="menulink clicktoopen">'.$hir[0].'</div><div class="openit" style="display:none;">'.$hir[1].'</div></div>'; 
					}
					}
				?>
				<?php if(isset($content)) echo $content; 
				
				echo "<br>".$abbrevlist;
				?>
				</div>
				
				<div class="col span_1_of_2" style="position: relative;">
				
				<?php if(isset($menu->items)) $menu->html(); ?>
				</div>
				
			</div>
			
			<div class="section group" style="margin-top:30px">	
										
				<div class="col span_2_of_2" style="float: right;position: relative;">
				<?php if(isset($comments) AND $comments != '') {
				    echo "<h4>Kommentár</h4>";
					echo $comments; 
					
				} ?>
				</div>
				
				<div class="col span_1_of_2" style="position: relative;">
				<?php 
						//echo showbookabbrevlist($db,$reftrans,"");
				 ?>
				</div>
				
			</div>
		</div>
		
		
	</div>
	<div id="footercontainer">
	
		<footer class="group">
			<div class="col span_1_of_2">
				<?php if(isset($copyright)) echo $copyright; ?>
			
				
			</div>
			<div class="col span_2_of_2">
				<!--<p>Az oldalhoz elérhető egy API is, ami az adott Szentírási idézetet ill. keresés eredményt JSON vagy XML formátumban adja vissza.
				Lásd: <a href="<?php echo $baseurl; ?>API"><?php echo $baseurl; ?>API</a></p>-->
				<!--<p> Tartalmi kérdések: <a href='mailto:info@kereszteny.hu' class='link'>Info</a> - 
				Technikai problémák: <a href='mailto:webmaster@kereszteny.hu' class='link'>Webmaster</a> - 
				<a href='/impresszum.php' class='link'>Impresszum</a></p>-->
				<p>Kérdések, ötletek, problémák: <a href='mailto:eleklaszlosj@gmail.com'>Elek László SJ</a> (<a href="http://jezsuita.hu">JTMR</a>)</p>
			</div>
			

   			<br class="breaker" />

			<div id="smallprint">
			
			<!--<span class="heart">&hearts;</span> Lovingly made in Newcastle upon Tyne.</span>-->
			</div>
		</footer>
	</div>
</div>



	<!-- JavaScript at the bottom for fast page loading -->

	<!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if necessary -->
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script>window.jQuery || document.write('<script src="js/jquery-1.7.2.min.js"><\/script>')</script>

	<!--[if (lt IE 9) & (!IEMobile)]>
	<script src="js/selectivizr-min.js"></script>
	<![endif]-->

	<?php
	if(isset($scripts)) {
		foreach($scripts as $script) {
		//echo $fileurl.'js/'.$script;
		echo '<script src="'.$fileurl.'js/'.$script.'"></script>'."\n";
		if(file_exists($fileurl.'js/'.$script)) {
			//echo '<script src="'.$fileurl.'js/'.$script.'"></script>'."\n";
		
		}
		}
	}
	?>

	<!-- More Scripts-->
	<script src="<?php echo $fileurl; ?>js/responsivegridsystem.js"></script>


</body>
</html>