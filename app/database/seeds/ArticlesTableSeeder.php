<?php

class ArticlesTableSeeder extends Seeder
{
    public function run()
    {
        $article = new SzentirasHu\Models\Entities\Article();
        $article->frontpage = true;
        $article->title = 'Front page article title';
        $article->text = 'Front page article text';
        $article->publish_date = '2014-01-01';
        $article->save();
    }
}

