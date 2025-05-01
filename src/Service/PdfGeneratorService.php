<?php

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGeneratorService
{
    private Dompdf $dompdf;

    public function __construct(KernelInterface $kernel)
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $this->dompdf = new Dompdf($options);
    }

    public function generateFromHtml(string $html): string
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();

        return $this->dompdf->output();
    }
}
