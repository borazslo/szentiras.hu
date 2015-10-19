<?php
/**

 */

namespace SzentirasHu\Data\Repository;


interface SynonymRepository {

    function findSynonyms($word);

    function addSynonyms($words);

} 