<?php

class TCPDFGenerator {

    function __construct() {     
        require_once('libs/tcpdf/tcpdf.php');
    }

    //$title - title of pdf document, also used as filename and subject
    //$htmlcontent - html to be used to generate pdf
    //$path - where to save the file
    //$orientation - P portrait, L landspace
    function html2PDF($title, $htmlcontent, $path, $orientation = 'P') {
        // create new PDF document
        $pdf = new TCPDF($orientation, 'mm', 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(SHORTNAME);
        $pdf->SetAuthor(SHORTNAME);
        $pdf->SetTitle($title);
        $pdf->SetSubject($title);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set margins
        $pdf->SetMargins(15, 25, 15);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 25);

        // add page
        $pdf->AddPage();

        // output the HTML content
        $pdf->writeHTML($htmlcontent, true, false, true, false, '');

        // reset pointer to the last page
        $pdf->lastPage();

        // generate file name
        $filename = str_replace([' ', '.'], '_', $title) . '.pdf';

        //Close and output PDF document
        $pdf->Output($path .'/' . $filename, 'F');

        return $filename;

    }

}

?>
