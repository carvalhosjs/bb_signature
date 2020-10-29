<?php namespace Source\Core;

use setasign\Fpdi\Tcpdf\Fpdi;
use setasign\Fpdi\PdfReader;
use BBSignature\Exception\SignerException;
/**
 * Class Signer A1 - BB_Signature by Carlos Mateus Carvalho
 * @package Source\Core
 */
class Signer{

    /**
     * Atributo para guardar a instancia fo FPDF
     * @var Fpdi
     */
    protected $pdf;

    /**
     *  Atributo para guardar o caminho no disco onde serão armazenados os arquivos pdf.
     * @var mixed|string
     */
    protected $path;

    /**
     * Atributo para guardar nome do arquivo pdf.
     * @var mixed|string
     */
    protected $pdfname;

    /**
     * Atributo para gurdar as informações da da dependencia  de Certificate().
     * @var array
     */
    protected $certificate;

    /**
     * Atributo para guardar os erros.
     * @var array
     */
    private $errors;

    /**
     * Signer constructor.
     * @param Certificate $certificate - Dependencia da classe Certificate().
     * @param array $opt - Opções como header, footer, path do arquivo digital assinado, filename nome do arquivo assinado.
     */
    public function __construct(Certificate $certificate, $opt=[])
    {
        $this->pdf = new Fpdi();
        $this->pdf->setPrintHeader((empty($opt['header']) ? false : $opt['header']));
        $this->pdf->setPrintFooter((empty($opt['footer']) ? false : $opt['footer']));
        $this->path = empty($opt['path']) ?  PATH_DIGITAL : $opt['path'];
        $this->pdfname = (empty($opt['filename']) ? "signed.pdf" : $opt['filename']);
        $this->certificate = $certificate->getInfoCert();
        $this->errors = $certificate->getErrors();
    }

    /**
     * Método responsável por mesclar um pdf normal em pdf assinado.
     * @param string $filename - Path do arquivo PDFnão assinado.
     * @return $this
     * @throws PdfReader\PdfReaderException
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     */
    public function mergePDF(string $filename){
        $pageCount = $this->pdf->setSourceFile($filename);

        if($pageCount>1){
            for($pageNo=1; $pageNo<=$pageCount;$pageNo++){

                $pageId = $this->pdf->importPage($pageNo, PdfReader\PageBoundaries::MEDIA_BOX);
                $this->pdf->SetXY(150, 7.3);
                $this->pdf->SetFontSize(7.5);
                $this->pdf->Cell(30, 0.5, "Assinado Digitalmente", 1, 'R');
                $this->pdf->addPage();
                $this->pdf->useImportedPage($pageId, 0, 0, 210);
            }
        }else{
            $pageId = $this->pdf->importPage(1, PdfReader\PageBoundaries::MEDIA_BOX);
            $this->pdf->addPage();
            $this->pdf->useImportedPage($pageId, 0, 0, 210);
        }
        return $this;
    }

    /**
     * Método responsável por assinar o documento PDF
     * @return $this
     */
     public function sign()
    {
        if($this->errors->success){
            $this->pdf->setSignature($this->certificate->filename->certificate_path,
                $this->certificate->filename->certificate_path,
                $this->certificate->filename->password,
                '',
                2,
                $this->certificate->more->certificate_add_info);
        }else{
            $this->errors = $this->errors;
        }
        return $this;
    }


    /**
     * Método responsável por mostrar o carimbo ou não no documento PDF.
     * @return $this
     */
    public function withStamp()
    {
        $this->pdf->SetXY(150, 7.3);
        $this->pdf->SetFontSize(7.5);
        $this->pdf->Cell(30, 0.5, "Assinado Digitalmente", 1, 'R');
        $this->pdf->SetFontSize(9);
        return $this;

    }

    /**
     * Método responsável por mostrar na tela o documento assinado digitalmente.
     */
    public function show()
    {
        $this->pdf->Output('ArquivoAssinado.pdf', 'I');
    }

    /**
     * Método responsável por forçar download do arquivo PDF assinado digitalmente.
     */
    public function download()
    {

        $file_url = $this->path . $this->pdfname;
        $this->savePDF($file_url);
        header('Content-Type: application/pdf');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=".$this->pdfname);
        readfile($file_url);
    }

    /**
     * Método responsável por salvar no sico o arquivo PDF assinado digitalmente.
     * @return string
     */
    public function save()
    {
        $path = $this->path;
        if(!file_exists($path)){
            mkdir($path, 0777, true);
        }
        $file_url = $path. $this->pdfname;
        $this->savePDF($file_url);
        return $file_url;
    }

    /**
     * Método responsável por salvar o documento PDF utilizado em Save(), Download();
     * @param $filename - Nome do arquivo a ser gravado no disco.
     */
    private function savePDF($filename)
    {
        $this->pdf->Output(__DIR__ . '/../../' . $filename, 'F');
        chmod($filename , 0777);
    }
}