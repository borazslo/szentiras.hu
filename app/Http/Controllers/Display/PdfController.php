<?php
/**

 */

namespace SzentirasHu\Http\Controllers\Display;


use App;
use Config;
use Illuminate\Http\Request as HttpRequest;
use Request;
use Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use SzentirasHu\Http\Controllers\Controller;
use SzentirasHu\Data\Repository\TranslationRepository;
use \View;
use SzentirasHu\Service\Reference\CanonicalReference;
use SzentirasHu\Service\Text\TextService;

class PdfOptions {
    public $headings = true;
    public $refs = false;
    public $nums = false;
    public $quantity = 1;

    public function __construct(HttpRequest $request) {
        $this->headings = $request->input('headings', 'true') == 'true';
        $this->nums = $request->input('nums', 'false') == 'true';
        $this->refs = $request->input('refs', 'false') == 'true';
        $this->quantity = $request->input('quantity', 1);
    }

}

class PdfController extends Controller {

    /**
     * @var TextService
     */
    private $textService;
    /**
     * @var \SzentirasHu\Data\Repository\TranslationRepository
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
        $pdfFile = $this->generatePdf($translationId, $refString, Request::instance());
        $pngFile = "{$pdfFile}.png";
        $imageMagickCommand = Config::get('settings.imageMagickCommand');
        $command = [];
        if (is_array($imageMagickCommand)) {
            foreach ($imageMagickCommand as $arg) {
                $command[] = $arg;            }
        } else {
            $command[] = $imageMagickCommand;
        }
        foreach (['-density','300', '-resize','24%', '-trim', $pdfFile, $pngFile ] as $arg) {
            $command[] = $arg;        
        }
        $process = new Process($command);
        $process->run();
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
            $this->generatePdf($translationId, $refString, Request::instance()),
            "szentiras.hu-{$refString}.pdf",
            ['Content-Type' => 'application/pdf']);
        return $response;
    }

    private function generatePdf($translationId, $refString, $input)
    {
        $options = new PdfOptions($input);
        $ref = CanonicalReference::fromString($refString);
        $verses = $this->textService->getTranslatedVerses($ref, $translationId);
        $content = View::make('textDisplay.pdf.latex')->with(
            ['verses' => $verses, 'options' => $options] )->render();
        $workingDir = sys_get_temp_dir();
        $tmpFileName = tempnam($workingDir, 'szentiras-pdf-');
        $tmpFile = fopen($tmpFileName, 'w+');
        fwrite($tmpFile, $content);
        $command = [
            'xelatex',
            '-interaction=batchmode',
            '-no-shell-escape',
            "-output-directory={$workingDir}",
            $tmpFileName
        ];
        
        $process = new Process($command);
        $process->setWorkingDirectory($workingDir);
        $process->setEnv(['PATH' => '/usr/bin']);
        $process->run();
        fclose($tmpFile);
        return preg_replace('/\.tmp$/', '', $tmpFileName) . '.pdf';
    }

} 