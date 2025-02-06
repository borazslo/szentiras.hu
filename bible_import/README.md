## How-to

1. get a translation (xlsm)
2. in UpdateTextsCommand.php, near lin 110, set the proper "gepi" and "rov" columns
      - e.g. KNB 'rov' may be 6 - depends on the actual version of the source document
3. run `php artisan szentiras:updateTexts --file=KNB_szovegforras.xlsm --nohunspell`

## Proper translation Xlsm

- Sheet with the same name as the translation (e.g. KNB) has a single header line
- Sheet called "Konyvek" has two header lines

## Note

When populating the DB, this is the right order: load a database with correctly set up schemas and tables corresponding to a proper migration set, run the migrate command (to run any migrations missing), and then you should be able to safely update the texts.

1. `mysql < /app/tmp/database.sql`
2. `php artisan migrate -n`
3. `php artisan szentiras:updateTexts --file=KNB_szovegforras.xlsm --nohunspell`

## Upgrade planned
The new site is based on the `USX` format, this functionality (importing from Excel) will be probably obsolete soon.