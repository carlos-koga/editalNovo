<?php
require('../vendor/autoload.php');

use App\Database\Connect; 



$con =  new Connect();
if (empty($con->getErrorMessage())) {
    $con->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// Carrega o conteúdo do arquivo HTML
$html = file_get_contents('banco_clausula.html');
$ordem = 10;

// Usa DOMDocument para parsear o HTML
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
libxml_clear_errors();

$xpath = new DOMXPath($dom);
$lis = $xpath->query('//li[not(@class)]');



foreach ($lis as $li) {
    $conteudo = '';
    $nivel = $li->getAttribute('data-level') ?? '';

    // Começa com o <li> atual
    $conteudo .= $dom->saveHTML($li);

    // Percorre os irmãos seguintes até o próximo <li> sem classe
    $next = $li->nextSibling;
    while ($next) {
        if ($next->nodeType === XML_ELEMENT_NODE && $next->nodeName === 'li' && !$next->hasAttribute('class')) {
            break; // encontrou o próximo <li> sem classe, então para
        }

        // Adiciona elementos válidos
        if ($next->nodeType === XML_ELEMENT_NODE || ($next->nodeType === XML_TEXT_NODE && trim($next->textContent) !== '')) {
            $conteudo .= $dom->saveHTML($next);
        }

        $next = $next->nextSibling;
    }

    // Corrige caracteres estranhos
    $conteudo = str_replace(['Â«', 'Â»'], ['«', '»'], $conteudo);

    // Insere no banco
    $stmt = $con->pdo->prepare("INSERT INTO banco_clausula (level, cls_tx, cls_nu_ordem) VALUES (:nivel, :conteudo, :ordem)");
    $stmt->execute([
        ':nivel' => $nivel,
        ':conteudo' => $conteudo,
        ':ordem' => $ordem
    ]);
    $ordem += 10;
}


echo "Importação concluída com sucesso!";

} else {
    echo 'Erro de conexão com o banco de dados: ' . $con->getErrorMessage();            
}
