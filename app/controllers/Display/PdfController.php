<?php
/**

 */

namespace SzentirasHu\Controllers\Display;


use Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use \View;
use SzentirasHu\Lib\Reference\CanonicalReference;
use SzentirasHu\Lib\Text\TextService;
use SzentirasHu\Models\Entities\Translation;

class PdfController extends \BaseController {

    /**
     * @var TextService
     */
    private $textService;

    function __construct(TextService $textService)
    {
        $this->textService = $textService;
    }

    public function getDialog($translationAbbrev, $refString) {
        return View::make('textDisplay.pdf.pdfDialog')->with([ 'refString' => $refString]);
    }

    public function postRef($refString)
    {
        $ref = CanonicalReference::fromString($refString);
        $verses = $this->textService->getTranslatedVerses($ref, 3);
        $content = View::make('textDisplay.pdf.latex')->with('verses', $verses)->render();
        $workingDir = sys_get_temp_dir();
        $tmpFileName = tempnam($workingDir, 'szentiras-pdf-');
        $tmpFile = fopen($tmpFileName, 'w+');
        fwrite($tmpFile, $content);
        $builder = new ProcessBuilder(['xelatex', '-interaction=batchmode', '-no-shell-escape', "-output-directory={$workingDir}", $tmpFileName]);
        $builder->setWorkingDirectory($workingDir);
        $builder->getProcess()->run(function ($type, $buffer) {
        });
        fclose($tmpFile);
        $response = Response::download(
            preg_replace('/\.tmp$/', '', $tmpFileName) . '.pdf',
            "szentiras.hu-{$refString}.pdf",
            ['Content-Type' => 'application/pdf']);
        return $response;
    }

} 