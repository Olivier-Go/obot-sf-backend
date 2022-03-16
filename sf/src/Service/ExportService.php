<?php

namespace App\Service;

use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Twig\Environment;

class ExportService
{
    private Environment $twig;
    private Pdf $knpSnappyPdf;

    public function __construct(Environment $twig, Pdf $knpSnappyPdf)
    {
        $this->twig = $twig;
        $this->knpSnappyPdf = $knpSnappyPdf;
    }

    public function exportpdf(array $params): PdfResponse
    {
        $filename = sprintf('%s.pdf', $params['name'].'-'.date('YmdHis'));
        $html = $this->twig->render($params['template'], [
            'export' => true,
            'pagination' => $params['pagination']
        ]);

        $this->knpSnappyPdf->setOption('title', $filename);

        return new PdfResponse(
            $this->knpSnappyPdf->getOutputFromHtml($html),
            $filename,
            'application/pdf',
            'inline'
        );
    }
}