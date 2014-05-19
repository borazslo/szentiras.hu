<?php
/**

 */

namespace SzentirasHu\Models\Repositories;


use SzentirasHu\Models\Entities\Synonym;

class SynonymRepositoryEloquent implements SynonymRepository
{

    function findSynonyms($word)
    {
        $synonym = Synonym::where('word', $word)->remember(120)->first();
        if ($synonym) {
            return Synonym::where('group', $synonym->group)->remember(120)->get();
        } else {
            return false;
        }
    }

    function addSynonyms($synonyms)
    {
        $existing = Synonym::whereIn('word', $synonyms)->first();
        if ($existing) {
            $group = $existing->group;
            $existingWord = $existing->word;
            foreach ($synonyms as $newWord) {
                if ($newWord != $existingWord) {
                    $newSynonym = new Synonym();
                    $newSynonym->word = $newWord;
                    $newSynonym->group = $group;
                    $newSynonym->save();
                }
            }
        } else {
            $firstSynonym = new Synonym();
            $firstSynonym->save();
            $firstWord = array_pop($synonyms);
            $firstSynonym->word = $firstWord;
            $firstSynonym->group = $firstSynonym->id;
            $firstSynonym->save();
            foreach ($synonyms as $otherWord) {
                $otherSynonym = new Synonym();
                $otherSynonym->word = $otherWord;
                $otherSynonym->group = $firstSynonym->group;
                $otherSynonym->save();
            }
        }
    }

}
