<?php

require_once(__DIR__ . "/../../libraries/tfpdf/tfpdf.php");


class PDFController extends TFPDF
{


    /**
     * ReportController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $timezone = "Europe/Bratislava";
        date_default_timezone_set($timezone);

    }

    public function fontHandler($type): void
    {
        $this->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
        $this->AddFont('DejaVuBold', '', 'DejaVuSansCondensed-Bold.ttf', true);
        $this->SetFont('DejaVu', '', 14);
        if ($type === 'pageTitle') {
            $this->SetFont('DejaVuBold', '', 20);
        }
        if ($type === 'title') {
            $this->SetFont('DejaVuBold', '', 16);
        }
        if ($type === 'subTitle') {
            $this->SetFont('DejaVuBold', '', 14);
        }
        if ($type === 'text') {
            $this->SetFont('DejaVu', '', 12);
        }
    }

    public function appendCheckBox($print, $chosen, $correct)
    {
        $this->Ln();
        if ($correct) {
            $this->SetTextColor(10, 215, 14);
        } else {
            $this->SetTextColor(215, 10, 14);
        }
        if ($chosen) {
            $this->SetDrawColor(0, 0, 0);
            $this->Cell(5, 5, '           ' . $print, 1, 5, 'l', true);
        } else {
            $this->Cell(5, 5, '           ' . $print, 1, 5, 'l');
        }
        $this->SetTextColor(0, 0, 0);

        $this->Ln();
    }


    public function getRow()
    {
        return $this->GetY();
    }

    public function setRow($row)
    {
        $this->SetY($row);
    }

    public function setColumn($column): void
    {
        $this->SetX($column);
    }

    public function appendPair($print, $chosen): void
    {
        if ($chosen === true) {
            $this->SetDrawColor(10, 215, 14);
        } else if ($chosen === false) {
            $this->SetDrawColor(215, 10, 14);
        }
        $this->Cell(30, 30, $print, 1, 1, 'C');
        $this->Ln(10);
        $this->SetDrawColor(0, 0, 0);
    }

    public function appendText($print, $type): void
    {

        $this->fontHandler($type);
        $this->Cell(10, 10, $print, 0, 1, 'l');
    }

    public function appendImage($url64): void
    {

        if ($this->getY() + 100 >= $this->GetPageHeight()) {
            $this->newPage();
        }
        $this->Cell(10, 10, '   ', 0, 1, 'l');
        $imagePath = 'data://text/plain;base64,' . explode(',', $url64, 2)[1];
        $this->Image($imagePath, 50, $this->GetY(), 100, 100, 'png');
        $this->Ln(100);


    }

    public function appendLatex($quote): void
    {
        $this->Ln(10);
        $this->Cell(10, 10, '   ', 0, 1, 'l');
        $url = 'https://latex.codecogs.com/png.download?%5Cdpi%7B300%7D%20%5Cfn_phv%20%5Chuge';
        $this->Image($url . urlencode($quote), $this->GetX(), $this->GetY(), -300, -300, 'png');
        $this->Cell(10, 10, '   ', 0, 1, 'l');
        $this->Ln(50);
    }

    public function breakPage()
    {
        if ($this->getRow() > 250) {
            $this->newPage();
        }
    }


    public function newPage(): void
    {
        $this->AddPage();
    }

    public function getWidth()
    {
        return $this->GetPageWidth();
    }

    public function export(): string
    {
        return $this->Output('S');
    }

    public function createHeader($title): void
    {
        $imagePath = __DIR__ . "/../../resources/favicon.png";
        $this->Image($imagePath, 10, 6, 30);
        $this->fontHandler('pageTitle');
        $this->Cell(80);
        $this->Cell(30, 10, $title, 0, 0, 'C');
        $this->Ln(40);
    }
}
