Szentírás .hu
========

A [szentiras.hu](http://szentiras.hu) teljes kódja. 

A régi kereszteny.hu/biblia átvételével egyre sajátosodó össze-vissza programozott valami.
Minden az index.php-n kereszül megy. 
Nem CMS, de egy template.php-n keresztül megy a megjelenítés nem elhanyagolható része.

#Felépítés

###Könyvtár struktúra
- **cache** - az egresszív cache file-ok tárolására (*cron* ürítgeti)
- **css** - responsive miatt vannak használatlanok
- **img** - a sminkben használt képek (egy része talán nem használt)
- **include** - külső, máshonnan szerzett php és egyéb cuccok (részletek lennebb)
- **info** - valamiért kellett, hogy a [/info](http://szentiras.hu/info) működjön :(
- **js** - a scriptecskék és szemeteik
- **tmp** - *importer* és *ebook* használja + db backup/schema/sample (üríthető alkalmanként)

###A függvények főbb helyei:
- **quote.php** - `isquotation()`, `print_quotation()`, `quotation()` - vagyis, hogy mikor idézet hivatkozás valami és azt rakja össze, hogy milyen versek vannak benne
- **biblefunc.php** -  főként az eredeti réges-régi biblia honlap függvényei. részben átdolgozva, de itt maradtak részben csak szemét. + mysql db függvények (a nem elegáns régiek)
- **func.php** - újabb search függvények (simpleverse, rootverse, search, resultorder, addresults, searchsimple, dbsearchtext) és egyebek getnews() (főoldalra hírek), class menu, url(), redirect függvények (hosszú ill rövid url-ek alakítgatás és értelmezése). általában tisztességes leírással

###Az adatbázisról még
- **dropbox_oauth_tokens** -> dropboxban xlsx-ben vannak az szövegforrások, onnan frissítünk néha. ezt tudják a szöveggondozók kezelni
- **igenaptar** -> nem üzemel, törölhető (az bencés igenaptárat tudom ide importálni és van egy kísérleti igenaptar mappám, ahol ez megnyitható, szerkeszthető)
- **news** -> hírek a címlapon ill. talán info fileban is.
- **stats_search** -> keresési statisztikák, a keresési tippek és a cache alapja
- **stats_texttosearch** -> teljes keresési talán szinte log féle
- **szinonmiak** -> szinoníma és alternatív lista kézzel összeállítva. néha 0/1/2/3-al jelölve, hogy melyik fordításban melyik a használatos
- **tdbook** -> könyvek listája
- **tdbook_hibas** -> hibás könyvrövidítések gyűjteménye, hogy kezelni tudjuk
- **tdtrans** -> a fordítások listája. a fordításra általában `$reftrans`, vagy `$transid`-vel hivatkounk
- **tdverse** -> a nagyon-nagyon okos végtelen vers és fejezetcím tábla, nem minden oszlop van használatban, csak az átalakításoknál benne marad
- **vars** -> különféle beállítások változó a `getvars()` és `setvars()` saját függvényeknek. Minél több minden idekerül, hogy könnyen lehessen állítani. Pl. hogy mi mikor volt utoljára update és update közben - mert az hosszú néha - "frissítünk" felirat jelenik meg a honlap tetején

###Külső programok és scriptek (elérgetőséggel)
- *importer* - [include/Dropbox-master](https://github.com/BenTheDesigner/Dropbox)
- *importer* - [include/excel_reader2.php](http://code.google.com/p/php-excel-reader)
- *importer* - [include/PHPExcel-develop](http://www.codeplex.com/PHPExcel)
- *api* - [include/JSON.php](http://mike.teczno.com/JSON/JSON.phps)
- *ebook* - [include/epub/EPub.php](http://www.phpclasses.org/package/6115)
- *ebook* - [include/php-ga-1.1.1](http://code.google.com/p/php-ga)
- *ebook* - [../kindlegen](http://www.amazon.com/gp/feature.html?docId=1000765211)
- *search* - [hunspell](http://hunspell.sourceforge.net/)

#Alapvető funkciók

###Főoldal:
- fordítások listája, néhány információval kiegészítve
- igenaptár adott napi megjelenítése a katolikus.hu/igenaptar-ból kihalászva minden alkalommal
- mysql-ből hírek (szerkesztő felület nélkül)
###Könyvnézegetés
- Fordítás könyveinek listája
- Egy-egy könyv fejezeteinek listája bevezetővel
- Egy-egy fejezet megjelenítése
    - Lépés a következő/előző fejzetre. (Ez most val. nem működik.)
###Idézés
- Url-be vagy keresőbe beírt szentírás hivatkozás észrevétele, elemzése és megjelenítése. pl. Mk3,4-7.9-12 vagy Mk3-6 vagy Mk 5,3-7,4.7 stb.
- Adott igehely/rész megjelenítése lábjegyzettel és magyarázattal
- Link a többi fordítás azonos könyvére (sokszor már a rövidítés!)
- Hibás fordítás/könyvrövidítés estén átvisz a helyes kombinációra
###Szövegmegjelenítés - Idézésnél és fejezet nézetnél is
- Vannak címsorok, alcímsorok és a szöveg
- A vers száma felső indexben nem mindig a tényleges sorszám (akár betű is lehet)
- Twitter és facebook megosztás (hozzávaló html tag-ek hogy szebb legyen a megosztott anyag)
- Link a többi fordítás azonos könyvére (sokszor már a rövidítés!)
- Lábjegyztekkel és magyrázatokkal. Ezek nem versekhez, hanem verstartományokhoz tartoznak.(Éppen nem működik, mert nincs az adatbázisban.)
###Keresés
- Url-be vagy keresőbe beírt kifejezés keresése
- szűri lehetőség: bármely könyvre ill. Újszöv/Ószöv; fordítás
- találatok csoportosítása: versenként vagy fejezetenként
- A találatokat súlyozza és a legsúlyosabbal kezdi (tehát nem előfordulási könyvbeli sorrendben jelennek meg)
- Amit figyel
    - pontos és regexp találat.
    - szavakra bontva és idézőjelben is (idézőjeles keresési lehetőség)
    - szótóben keres (megkeresi a hunspell a keresőszó szótöveit, a szentírás minden versét szótöves változatban is tároljuk)
- Extrák
    - megnézni, hogy az alternatív fordításokban ugyan erre mennyi a találat (hogy ne legyen túl lassú a logban/cacheben lévők között néz csak körül)
    - tippeket ad hasonló kifejezésekre ill. azonos szótövű szavakra, ha valaki keresett már rá és 10-100 talált van
    - tippeket ad kézzel készülő szinoníma szótár alapján: létra/lajtorja, Nebukodonozor/Nebukadanazzár, stb.
    - ugyan ezen szótár szerint elgépelt nevekre is tippeket ad
###API/Fejlesztőknek
- csupán json/xml válaszokat ad megfelelő kérésekre (tehát nincs cross browsing, felhasznál kezelé, oauth, stb.) (ez magasabb szinten is kell majd)
- főként a keresésekre válaszol és elvileg mindent tudnia kéne, amit a keresőnek, csak más formátumban válaszol
- könyvek automatikus /epub kimenetele ill. abból generált /mobi (havont egyszer gyárt, onnantól mindig azt hviatkozza)
###Egyéb
- pár statikusabb oldal kéne ill van (címlap, infó, impresszum, stb.) (nem kell szerkesztő felület)
- agresszív cache: a php script a végén html-ben lementi az oldalt, legközelebb azt hozza be
- mysql cache: a kereséseket logolja és cacheli, hogy az alternítvákat jól mutassa és lássam mi megy az életben
- rövid urlek (és régi-régi linkek hosszú url-jeit is értelmezi)
- GoogleAnalytics egyelőre igénytelenül (pl. kereséseket nem követ)
- cron napi/heti/havi: adatbázis karbantartás, felesleges fájlok törlése