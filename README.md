Szentírás .hu
========

A http://szentiras.hu teljes kódja. A régi kereszteny.hu/biblia átvételével egyre sajátosodó össze-vissza programozott valami.

Minden az index.php-n kereszül megy. 

Nem CMS, de egy template.php-n keresztül megy a megjelenítés nem elhanyagolható része.

###Könyvtár struktúra
cache - az egresszív cache file-ok tárolására (néha üríteni kell)
css - responsive miatt vannak használatlanok
ebook - a generált ebookok (epub/mobi) tárolására (üríthető, néha üríteni kell egy részét)
img - a sminkben használt képek (egy része talán nem használt)
include - külső, máshonnan szerzett php és egyéb cuccok
info - valamiért kellett, hogy a szentiras.hu/info működjön :(
js - a scriptecskék és szemeteik
tmp - importer használja + db backup (részben üríthető alkalmankén)

###A függvények főbb helyei:
-quote.php - már az enyém. isquotation(), print_quotation(), quotation() - vagyis, hogy mikor idézet hivatkozás valami és azt rakja össze, hogy milyen versek vannak benne
-searchbible.php - a keresés függvényei a keresés motorján kívül getIdezetTipp(), getSzinonima(), getSzinonimaTIpp(), printsearchform()
- biblefunc.php -  főként az eredeti réges régi biblia honlap függvényei. részben átdolgozva, de itt maradtak részban csak szemét
- func.php - hát újabb search függbények (simpleverse, rootverse, search, resultorder, addresults, searchsimple, dbsearchtext) és egyebek getnews() (főoldalra hírek), class menu, url(), redirect függvények (hosszú ill rövid url-ek alakítgatás és értelmezése)

###Az adatbázisról még
- dropbox_oauth_tokens: dropboxban xlsx-ben vannak az szövegforrások, onnan frissítünk néha. ezt tudják a szöveggondozók kezelni
- igenaptar -> nem üzemel, törölhető (az bencés igenaptárat tudom ide importálni és van egy kísérleti igenaptar mappám, ahol ez megnyitható, szerkeszthető)
- news -> hírek a címlapon ill. talán info filban is.
- stats_search -> keresési statisztikák, a keresési tippek és a cache alapja
- stats_texttosearch -> teljes keresési talán szinte log féle
- szinonmiak -> szinoníma és alternatív lista kézzel összeállítva. néha 0/1/2/3-al jelölve, hogy melyik fordításban melyik a használatos
- tdbook -> könyvek listája
- tdchapter -> melyik könyvben hány fejezet és melyik az utolsó vers. nincs használatban!
- tdtrans -> a fordítások listája. a fordításra általában $reftrans, vagy $transid-vel hivatkounk
- tdverse -> a nagyon-nagyon okos végtelen vers és fejezetcím tábla, nem minden oszlop van használatban, csak az átalakításoknál benne marad
- vars -> különféle beállítások változó a getvars() és setvars() saját függvényeknek. Minél több minden idekerül, hogy könnyen lehessen állítani. Pl. hogy mi mikor volt utoljára update és update közben - mert az hosszú néha - "frissítünk" felirat jelenik meg a honlap tetején
