<?php
/**

 */

namespace SzentirasHu\Models\Repositories;


interface SynonymRepository {

    function findSynonyms($word);

    function addSynonyms($words);

} 