<?php

namespace SzentirasHu\Http\ViewComposers;

use Carbon\Carbon;

/**
 * View composer for the menu. This will lookup and provide data needed for the menu.
 *
 * @author berti
 */
class MenuComposer
{

    public function compose($view)
    {

        $currentSecond = Carbon::now()->second;
        if ($currentSecond > 30) {
            $adView = "ad.simontl";
        } else {
            $adView = "ad.konyvekkonyve";
        }

        $view
            ->with('adview', $adView);
    }

}
