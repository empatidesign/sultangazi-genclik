<?php
namespace App\Libraries;
require_once dirname(__FILE__) . '/TCPDF/tcpdf.php';

class Pdf extends \TCPDF {

  public function __construct() {
      parent::__construct();
  }

  public function createPDF(string $fileName, string $html, string $type = 'F', string $orientation = 'P') {
		ob_start();

		$pdf = new \TCPDF($orientation, PDF_UNIT, PDF_PAGE_FORMAT, TRUE, 'UTF-8', FALSE);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('PDF');
		$pdf->SetTitle('PDF');
		$pdf->SetSubject('PDF');
		$pdf->SetKeywords('PDF');

		// Set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// Set header and footer fonts
		$pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
		$pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

		$pdf->SetPrintHeader(FALSE);
		$pdf->SetPrintFooter(FALSE);

		// Set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// Set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT, 15);
		$pdf->SetHeaderMargin(0);
		$pdf->SetFooterMargin(0);

		// Set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// Set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// Set font
		$pdf->SetFont('dejavusans', '', 9);

		$pdf->AddPage();
		$pdf->writeHTML($html, TRUE, FALSE, TRUE, FALSE, '');
		$pdf->lastPage();
		ob_end_clean();
		return base64_encode($pdf->Output($fileName, $type));
	}
}
