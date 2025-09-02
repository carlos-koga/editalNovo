<?php
require('../vendor/autoload.php');

use App\Database\Connect; 

header('Content-Type: text/html; charset=utf-8');


$id = $_POST['id'];

$con =  new Connect();
if (empty($con->getErrorMessage())) {    
    $con->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $con->pdo->prepare("SELECT cls_nu, level, cls_tx FROM banco_clausula WHERE cls_nu=:id");
    $stmt->execute([':id' => $id]);    
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);
    //echo $row['cls_tx'];
    $html = $row['cls_tx'];


    $dom = new DOMDocument('1.0', 'UTF-8');
    //$dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD); 
    /* $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD); */
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    
    // Seleciona todas as <section>
    $sections = $xpath->query('//section');
    
    foreach ($sections as $section) {
        $lis = [];
    
        // Coleta os <li> filhos diretos da <section>
        foreach (iterator_to_array($section->childNodes) as $child) {
            if ($child->nodeName === 'li') {
                $lis[] = $child;
            }
        }
    
        if (!empty($lis)) {
            // Cria um novo <ol>
            $ol = $dom->createElement('ol');
    
            // Insere o <ol> antes do primeiro <li>
            $section->insertBefore($ol, $lis[0]);
    
            // Move os <li> para dentro do <ol>
            foreach ($lis as $li) {
                $ol->appendChild($li);
            }
        }
    }
    
    $retorno = $dom->saveHTML();

    
    //$retorno = htmlentities($retorno, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    echo $retorno;

   
    
} else {
    echo "Erro: " . $con->getErrorMessage();
}
unset($con->pdo);
?>