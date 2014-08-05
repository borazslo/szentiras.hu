<?php
/**

 */

namespace SzentirasHu\Controllers\Display;


use Endroid\QrCode\QrCode;
use Request;
use Response;
use View;

class QrCodeController extends \BaseController{

    public function index($url)
    {
        $qrCode = new QrCode();
        $qrCode->setText($url);
        if (Request::has('size')) {
            $qrCode->setSize(Request::get('size'));
        } else {
            $qrCode->setErrorCorrection(QrCode::LEVEL_HIGH);
            $qrCode->setSize(150);
        }

        $qrCode->setPadding(5);
        return Response::make($qrCode->render(), 200, array('content-type' => 'image/png'));
    }

    public function dialog($url)
    {
        return View::make('textDisplay.qrDialog')->with([ 'url' => $url]);
    }

}