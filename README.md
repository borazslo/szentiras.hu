Szentírás .hu
========

A [szentiras.hu](http://szentiras.hu) teljes kódja.

## Felépítés

### Framework
A Laravel keretrendszert használjuk.

#### Könyvtár struktúra
- **app** - Maga a webalkalmazás.
- **bootstrap** - Framework beizzítás
- **deploy** - deployment konfigurációk és scriptek (apache, git hook stb.)
- **old** - a régi, össze-vissza programozott szentiras.hu kódja
- **public** - a közvetlenül kiszolgált fájlok, az index.php, css, js és hasonlók
- **tmp** - tesztadatbázis-példa

## Alapvető funkciók

### Főoldal:
- fordítások listája, néhány információval kiegészítve DONE
- igenaptár adott napi megjelenítése a katolikus.hu/igenaptar-ból kihalászva minden alkalommal DONE
- mysql-ből hírek (szerkesztő felület nélkül) DONE

### Könyvnézegetés
- Fordítás könyveinek listája DONE
- Egy-egy könyv fejezeteinek listája bevezetővel DONE
- Egy-egy fejezet megjelenítése DONE
    - Lépés a következő/előző fejezetre. (Ez most val. nem működik.)

###Idézés
- Url-be vagy keresőbe beírt szentírás hivatkozás észrevétele, elemzése és megjelenítése. pl. Mk3,4-7.9-12 vagy Mk3-6 vagy Mk 5,3-7,4.7 stb. DONE
- Adott igehely/rész megjelenítése lábjegyzettel és magyarázattal - PARTLY DONE Egyelőre a lábjegyzet és magyarázat nem támogatott
- Link a többi fordítás azonos könyvére (sokszor már a rövidítés!) DONE
- Hibás fordítás/könyvrövidítés estén átvisz a helyes kombinációra DONE

###Szövegmegjelenítés - Idézésnél és fejezet nézetnél is
- Vannak címsorok, alcímsorok és a szöveg DONE
- A vers száma felső indexben nem mindig a tényleges sorszám (akár betű is lehet) DONE
- Twitter és facebook megosztás (hozzávaló html tag-ek hogy szebb legyen a megosztott anyag) DONE
- Link a többi fordítás azonos könyvére (sokszor már a rövidítés!) DONE
- Lábjegyztekkel és magyrázatokkal. Ezek nem versekhez, hanem verstartományokhoz tartoznak.(Éppen nem működik, mert nincs az adatbázisban.)

###Keresés
- Url-be vagy keresőbe beírt kifejezés keresése DONE
- szűri lehetőség: bármely könyvre ill. Újszöv/Ószöv; fordítás
- találatok csoportosítása: versenként vagy fejezetenként DONE
- A találatokat súlyozza és a legsúlyosabbal kezdi (tehát nem előfordulási könyvbeli sorrendben jelennek meg) DONE
- Amit figyel
    - Sphinx extended search nyelvtan szerint DONE
    - szótóben keres (megkeresi a hunspell a keresőszó szótöveit, a szentírás minden versét szótöves változatban is tároljuk) PARTLY DONE (a keresőszót nem tövezzük) 
- Extrák
    - megnézni, hogy az alternatív fordításokban ugyan erre mennyi a találat DONE - csak a Sphinx indexben nézi, ezért villámgyors
    - tippeket ad hasonló kifejezésekre ill. azonos szótövű szavakra
    - tippeket ad kézzel készülő szinoníma szótár alapján: létra/lajtorja, Nebukodonozor/Nebukadanazzár, stb.
    - ugyan ezen szótár szerint elgépelt nevekre is tippeket ad

###API/Fejlesztőknek
- JSON válaszokat ad megfelelő kérésekre DONE (JSON)
- főként a keresésekre válaszol és elvileg mindent tudnia kéne, amit a keresőnek, csak más formátumban válaszol DONE (legalábbis az kész, ami volt)
- könyvek automatikus /epub kimenetele ill. abból generált /mobi (havont egyszer gyárt, onnantól mindig azt hviatkozza) (ez nem megy az élesen sem)

###Egyéb
- pár statikusabb oldal kéne ill van (címlap, infó, impresszum, stb.) (nem kell szerkesztő felület)
- agresszív cache: a php script a végén html-ben lementi az oldalt, legközelebb azt hozza be - ez már nincs, de van egy csomó cache-elés, főleg db
- mysql cache: a kereséseket logolja és cacheli, hogy az alternítvákat jól mutassa és lássam mi megy az életben DONE
- rövid urlek (és régi-régi linkek hosszú url-jeit is értelmezi) DONE
- GoogleAnalytics egyelőre igénytelenül (pl. kereséseket nem követ) DONE
- cron napi/heti/havi: adatbázis karbantartás, felesleges fájlok törlése TO BE DELETED