<?php

/** This file registers all the view composers.
 * 
 */

View::composer('menu', '\SzentirasHu\Views\Composers\MenuComposer');
View::composer('bookAbbrevList', '\SzentirasHu\Views\Composers\BookAbbrevListComposer');