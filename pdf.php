<?php
require('../vendor/autoload.php');
error_reporting(E_ERROR);
set_time_limit(0);


use Dompdf\Dompdf;
$dompdf = new Dompdf();


ob_start();
include('view_clausula.php');
$html = ob_get_clean();

//echo $html;
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait'); // 'portrait' para retrato, 'landscape' para paisagem

$dompdf->render();
$dompdf->stream('arquivo.pdf', ['Attachment' => false]); // false exibe no navegador, true faz download
