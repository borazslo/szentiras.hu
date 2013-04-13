<?php
/* ÖTLETEK
 A "Keresés" menü működésével kapcsolatban egy felhasználói álom: vajon volna-e lehetőség olyan prediktív rendszerre, 
 hogy a begépelés során felkínálja a rendszer a Bibliában előforduló szavakat, melyek a megkezdett gépelés folytatásaként lehetségesek.
Erre azért volna nagy szükség, mert többen többféle fordítást is használunk, és a keresőszó terén néha bizonytalanok vagyunk, 
nem emlékezünk, hogy az adott fordításban éppen melyik kifejezést használja a szöveg. Ezért néha eredménytelen, 
vagy sok vesződséget okoz egy keresett hely megtalálása.
*/


$title = 'Fejlesztések';
$pagetitle = 'Fejlesztések | Szentírás';
$content .= '
  <br><span class="cim">2013. 04. 10. </span><span class="alcim">Keresés a könyvekben</span><span class="alap"> pl.: „ember in:Ószöv” vagy „medve in:Zsolt”</span><span class="cim">2013. 04. 05. </span><span class="alcim">Károli fordítás</span><span class="alap"> is felkerült, mégha nem is egészen kidolgozva. </span>
  <span class="cim">2013. 03. 27. </span><span class="alcim">Új kinézet</span><span class="alap">, hogy mobilkütyükön is használható legyen az oldal. </span>
  <span class="alcim">Javított Káldi-Neovulgáta szöveg</span><span class="alap">, mert össze voltak keverve fejezetek, sok volt az elgépelés, rosszak voltak az idézőjelek. </span>
  <span class="alcim">Szentírási helyekre keresés</span><span class="alap">, így egész bonyolult kifejezésket is használhatunk, mint pl. Mk 3,1-5.10;4,5-7. </span>
  <span class="alcim">Keresés a címekben</span><span class="alap"> is, nem csak a szövegben. </span>
  <span class="alcim">Rövid címek</span><span class="alap"> is elérhetőek, hogy kedvenc helyünket könnyen megjegyezhessük és megoszthassuk. </span>
  <span class="alcim">API</span> <span class="alap">json és xml kimenettel fejlesztőknek. </span>
 <!--<p class="feher menulink" style="background-color:#7F87C2;padding:10px;font-size:20px;width:100%;" align="center">húsvétkor megújulunk</p>-->
  
<div id="share" class="feher menulink" style="background-color:#7F87C2;padding:10px;font-size:20px;width:100%" align="center">
Szólj mindenkinek! 
				<div id="facebook" align="center">
					<a expr:share_url="data:post.url" href="http://www.facebook.com/sharer.php?" name="fb_share" rel="nofollow" type="button">Share</a>
					<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"/></script>
				</div>
				<div id="twitter" align="center"><a href="https://twitter.com/share" class="twitter-share-button" data-related="jasoncosta" data-lang="hu"  data-count="none" data-hashtags="Biblia" data-url="http://szentiras.hu" data-text="Megújul a Biblia - Keresztény Portál">Tweet</a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>
</div>';
?>