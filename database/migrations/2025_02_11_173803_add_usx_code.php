<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use SzentirasHu\Data\Entity\Book;
use SzentirasHu\Data\Entity\Translation;
use SzentirasHu\Data\UsxCodes;

return new class extends Migration {
    public function up(): void
    {
        $abbreviationToUsxMapping =
            UsxCodes::abbreviationToUsxMapping();
        $bookNumberAndTranslationToUsxMapping =
            $this->bookNumberAndTranslationToUsxMapping(
                $abbreviationToUsxMapping
            );
        $translationIdToTranslationAbbreviationMapping =
            $this->translationIdToTranslationAbbreviationMapping();

        $this->addUsxCodeRelatedColumns(
            $abbreviationToUsxMapping,
            $bookNumberAndTranslationToUsxMapping,
            $translationIdToTranslationAbbreviationMapping
        );

        Schema::table('translations', function (Blueprint $table): void {
            $table->unique('abbrev');
        });

        $this->dropUnnecessaryBookRelatedColumns();
    }

    public function down(): void
    {
        Schema::table('book_abbrevs', function (Blueprint $table): void {
            $table->dropColumn('usx_code');
            $table->dropColumn('translation_abbrev');
        });

        Schema::table('tdverse', function (Blueprint $table): void {
            $table->dropColumn('usx_code');
        });

        Schema::table('translations', function (Blueprint $table): void {
            $table->dropUnique('abbrev');
        });

        Schema::table('books', function (Blueprint $table): void {
            $table->dropColumn('usx_code');
            $table->renameColumn('order', 'number');
        });

        Schema::table('book_abbrevs', function (Blueprint $table): void {
            $table->integer('book_id');
            $table->tinyInteger('translation_id', autoIncrement: false, unsigned: true)->nullable();
        });

        Schema::table('tdverse', function (Blueprint $table): void {
            $table->integer('book_number');
        });
    }


    private function dropUnnecessaryBookRelatedColumns(): void
    {
        Schema::table('book_abbrevs', function (Blueprint $table): void {
            $table->dropColumn('book_id');
            $table->dropColumn('translation_id');
        });
        Schema::table('tdverse', function (Blueprint $table): void {
            $table->dropColumn('book_number');
        });
    }

    private function addUsxCodeRelatedColumns(
        $abbreviationToUsxMapping,
        $bookNumberAndTranslationToUsxMapping,
        $translationIdToTranslationAbbreviationMapping
    ): void {
        Schema::table('books', function (Blueprint $table): void {
            $table->renameColumn('number', 'order');
            $table->string('usx_code', 3);
        });

        Schema::table('book_abbrevs', function (Blueprint $table): void {
            $table->string('translation_abbrev')->nullable();
            $table->string('usx_code', 3);
        });

        $this->updateUsxCodeForAbbrev(
            $abbreviationToUsxMapping,
            ['books', 'book_abbrevs']
        );

        $this->updateTranslationAbbrev(
            $translationIdToTranslationAbbreviationMapping,
            ['book_abbrevs']
        );

        Schema::table('tdverse', function (Blueprint $table): void {
            $table->string('usx_code', 3);
        });

        $this->updateUsxCodeForBookNumberAndTranslation(
            $bookNumberAndTranslationToUsxMapping,
            ['tdverse']
        );
    }

    private function bookNumberAndTranslationToUsxMapping($abbreviationToUsxMapping): array
    {
        $result = [];
        $books = Book::all();
        foreach ($books as $book) {
            $abbrev = $book->translation->abbrev;
            $usx = $abbreviationToUsxMapping[$abbrev];
            $key = $this->encodeBookAndTranslation($book->number, $abbrev);
            $result[$key] = $usx;
        }
        return $result;
    }

    private function translationIdToTranslationAbbreviationMapping(): array
    {
        $result = [];
        $translations = Translation::all();
        foreach ($translations as $translation) {
            $result[$translation->id] = $translation->abbrev;
        }
        return $result;
    }

    private function updateUsxCodeForAbbrev(array $mapping, array $tables): void
    {
        $ids = [];
        $caseStatement = "CASE abbrev ";
        foreach ($mapping as $abbrev => $usxCode) {
            $ids[] = "'{$abbrev}'";
            $caseStatement .= "WHEN '{$abbrev}' THEN '{$usxCode}' ";
        }
        $caseStatement .= "END";

        $idsList = implode(',', $ids);
        foreach ($tables as $tableName) {
            DB::statement("UPDATE {$tableName} SET usx_code = {$caseStatement} WHERE abbrev IN ({$idsList})");
        }
    }

    private function updateUsxCodeForBookNumberAndTranslation(array $mapping, array $tables): void
    {
        $ids = [];
        $caseStatement = "CASE CONCAT(book_number, '|', translation) ";

        foreach ($mapping as $encodedBookAndTranslation => $usxCode) {
            $ids[] = "'{$encodedBookAndTranslation}'";
            $caseStatement .= "WHEN '{$encodedBookAndTranslation}' THEN '{$usxCode}' ";
        }

        $caseStatement .= "END";

        $idsList = implode(',', $ids);

        foreach ($tables as $tableName) {
            DB::statement("UPDATE {$tableName} SET usx_code = {$caseStatement} WHERE CONCAT(book_number, '|', translation) IN ({$idsList})");
        }
    }

    private function updateTranslationAbbrev(array $mapping, array $tables): void
    {
        $ids = [];
        $caseStatement = "CASE translation_id ";
        foreach ($mapping as $transId => $transAbbrev) {
            $ids[] = "'{$transId}'";
            $caseStatement .= "WHEN '{$transId}' THEN '{$transAbbrev}' ";
        }
        $caseStatement .= "END";

        $idsList = implode(',', $ids);
        foreach ($tables as $tableName) {
            DB::statement("UPDATE {$tableName} SET translation_abbrev = {$caseStatement} WHERE translation_id IN ({$idsList})");
        }
    }


    private function isInvalidRow(array $row): bool
    {
        return count($row) !== 2;
    }

    private function encodeBookAndTranslation(int $bookNumber, string $translation): string
    {
        return "{$bookNumber}|{$translation}";
    }
};

