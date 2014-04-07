<?php

class DatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        $this->call('BookTableSeeder');
        $this->call('ArticlesTableSeeder');
        $this->call('VersesTableSeeder');
    }

}