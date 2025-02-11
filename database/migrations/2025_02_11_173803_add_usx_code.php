<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->renameColumn('number', 'order');
            $table->string('usx_code', 3);
        });

        Schema::table('translations', function (Blueprint $table) {
            $table->unique('abbrev');
        });

        Schema::table('book_abbrevs', function (Blueprint $table) {
            $table->renameColumn('book_id', 'usx_code');
        });

        Schema::table('tdverse', function (Blueprint $table) {
            $table->renameColumn('book_number', 'usx_code');
        });

        $this->updateUsxCodeForAbbrev(
            $this->getUsxCodeMapping(),
            ['books', 'book_abbrevs']
        );
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->renameColumn('order', 'number');
            $table->dropColumn('usx_code');
        });
    
        Schema::table('translations', function (Blueprint $table) {
            $table->dropUnique(['abbrev']);
        });
    
        Schema::table('book_abbrevs', function (Blueprint $table) {
            $table->renameColumn('usx_code', 'book_id');
        });
    
        Schema::table('tdverse', function (Blueprint $table) {
            $table->renameColumn('usx_code', 'book_number');
        });
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

    private function getUsxCodeMapping(): array
    {
        $csvFile = database_path('mappings/book_usx_codes.csv');

        if (!file_exists($csvFile)) {
            throw new \Exception("CSV file not found: {$csvFile}");
        }

        $handle = fopen($csvFile, 'r');
        $mapping = [];

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if ($this->isInvalidRow($row)) {
                continue;
            }

            $abbrev = trim($row[0]);
            $usxCode = trim($row[1]);
            $mapping[$abbrev] = $usxCode;
        }
        fclose($handle);

        if (empty($mapping)) {
            throw new \Exception("CSV file is empty or improperly formatted.");
        }

        return $mapping;
    }

    private function isInvalidRow(array $row): bool
    {
        return count($row) !== 2;
    }
};

