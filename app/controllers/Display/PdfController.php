<?php
/**

 */

namespace SzentirasHu\Controllers\Display;


use App;
use Config;
use Input;
use Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use SzentirasHu\Models\Repositories\TranslationRepository;
use \View;
use SzentirasHu\Lib\Reference\CanonicalReference;
use SzentirasHu\Lib\Text\TextService;
use SzentirasHu\Models\Entities\Translation;

class PdfOptions {
    public $headings = true;
    public $refs = false;
    public $nums = false;
}

class PdfController extends \BaseController {

    /**
     * @var TextService
     */
    private $textService;
    /**
     * @var \SzentirasHu\Models\Repositories\TranslationRepository
     */
    private $translationRepository;

    function __construct(TextService $textService, TranslationRepository $translationRepository)
    {
        $this->textService = $textService;
        $this->translationRepository = $translationRepository;
    }

    public function getDialog($translationAbbrev, $refString) {
        return View::make('textDisplay.pdf.pdfDialog')->with([ 'refString' => $refString, 'translationId' => $this->translationRepository->getByAbbrev($translationAbbrev)->id]);
    }

    public function getPreview($translationId, $refString)
    {
        $pdfFile = $this->generatePdf($translationId, $refString, Input::all());
        $pngFile = "{$pdfFile}.png";
        $processBuilder = new ProcessBuilder([ Config::get('settings.imageMagickCommand'), '-trim', $pdfFile, $pngFile ]);
        $processBuilder->getProcess()->run();
        if (!file_exists($pngFile)) {
            $pngFile = "$pdfFile-0.png";
            if (!file_exists($pngFile)) {
                App::abort(404);
            }
        }
        return Response::download(
            $pngFile,
            "szentiras.hu-{$refString}-preview.png",
            ['Content-Type' => 'image/png']
        );
    }

    public function getRef($translationId, $refString)
    {
        $response = Response::download(
            $this->generatePdf($translationId, $refString, Input::all()),
            "szentiras.hu-{$refString}.pdf",
            ['Content-Type' => 'application/pdf']);
        return $response;
    }

    private function generatePdf($translationId, $refString, $input)
    {
        $options = new PdfOptions();
        $options->headings = $input['headings'] == 'true';
        $options->nums = $input['nums']  == 'true';
        $options->refs = $input['refs']  == 'true';
        $ref = CanonicalReference::fromString($refString);
        $verses = $this->textService->getTranslatedVerses($ref, $translationId);
        $content = View::make('textDisplay.pdf.latex')->with(
            ['verses' => $verses, 'options' => $options] )->render();
        $workingDir = sys_get_temp_dir();
        $tmpFileName = tempnam($workingDir, 'szentiras-pdf-');
        $tmpFile = fopen($tmpFileName, 'w+');
        fwrite($tmpFile, $content);
        $builder = new ProcessBuilder(['xelatex', '-interaction=batchmode', '-no-shell-escape', "-output-directory={$workingDir}", $tmpFileName]);
        $builder->setWorkingDirectory($workingDir);
        $builder->getProcess()->run(function ($type, $buffer) {
        });
        fclose($tmpFile);
        return preg_replace('/\.tmp$/', '', $tmpFileName) . '.pdf';
    }

} 