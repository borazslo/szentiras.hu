Szentírás .hu
========

A http://szentiras.hu teljes kódja. A régi kereszteny.hu/biblia átvételével egyre sajátosodó össze-vissza programozott valami.

Minden az index.php-n kereszül megy. 

Nem CMS, de egy template.php-n keresztül megy a megjelenítés nem elhanyagolható része.

###Könyvtár struktúra
. cache - az egresszív cache file-ok tárolására (néha üríteni kell)
. css - responsive miatt vannak használatlanok
. ebook - a generált ebookok (epub/mobi) tárolására (üríthető, néha üríteni kell egy részét)
. img - a sminkben használt képek (egy része talán nem használt)
. include - külső, máshonnan szerzett php és egyéb cuccok
. info - valamiért kellett, hogy a szentiras.hu/info működjön :(
. js - a scriptecskék és szemeteik
. tmp - importer használja + db backup (részben üríthető alkalmankén)
