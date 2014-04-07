Szentírás .hu
========

A [szentiras.hu](http://szentiras.hu) teljes kódja.

[![Build Status](https://travis-ci.org/borazslo/szentiras.hu.png)](https://travis-ci.org/borazslo/szentiras.hu)

#Felépítés

## Framework
A Laravel keretrendszert használjuk.

###Könyvtár struktúra
- **app** - Maga a webalkalmazás.
- **bootstrap** - Framework beizzítás
- **deploy** - deployment konfigurációk és scriptek (apache, git hook stb.)
- **old** - a régi, össze-vissza programozott szentiras.hu kódja
- **public** - a közvetlenül kiszolgált fájlok, az index.php, css, js és hasonlók
- **tmp** - tesztadatbázis-példa

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