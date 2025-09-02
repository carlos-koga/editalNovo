<?php
require('../vendor/autoload.php');

use App\Database\Connect; 

header('Content-Type: text/html; charset=utf-8');


$idAtual = $_POST['txtIdAtual'];
$oper  = $_POST['txtOper'];
$texto = $_POST['txtTexto'];


//libxml_use_internal_errors(true);
//$dom = new DOMDocument();
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->loadHTML($texto);
libxml_clear_errors();


// pega o nível do item
$li = $dom->getElementsByTagName('li')->item(0); // Pega o primeiro <li>

if ($li) {
    $nivel = $li->getAttribute('data-level');
} else {
    $nivel = 1;
}

if (($oper == 'inc') ||  ($oper == 'ed')) {
    //Elimina o <ol> colocado automaticamente pelo TinyMCE
    $uls = $dom->getElementsByTagName('ol');

    if ($uls->length > 0) {
        $firstUl = $uls->item(0);
        $parent = $firstUl->parentNode;

        while ($firstUl->firstChild) {
            $parent->insertBefore($firstUl->firstChild, $firstUl);
        }

        $parent->removeChild($firstUl);
    }

    $body = $dom->getElementsByTagName('body')->item(0);
    $output = '';
    foreach ($body->childNodes as $child) {
        $output .= $dom->saveHTML($child);
    }

    $texto = $output;

    $resumo = mb_strlen($texto) > 70 ? mb_substr($texto, 0, 70) . '...' : $texto;
}

$con =  new Connect();
if (empty($con->getErrorMessage())) {
    $con->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    

    if ($oper == 'inc') {
        $stmt = $con->pdo->prepare("SELECT cls_nu_ordem FROM banco_clausula WHERE cls_nu = :id_atual");
        $stmt->execute([':id_atual' => $idAtual]);
        $ordem_atual = $stmt->fetchColumn();
        echo $ordem_atual;

        $stmt = $con->pdo->prepare("SELECT cls_nu_ordem FROM banco_clausula WHERE cls_nu_ordem > :nivel_atual ORDER BY cls_nu_ordem LIMIT 1");
        $stmt->execute([':nivel_atual' => $ordem_atual]);
        $next_order = $stmt->fetchColumn();
        if ($next_order == false) {
            $next_order = $ordem_atual + 10;
        }
        echo $next_order;

        try {
            $con->pdo->beginTransaction();
            $stmt = $con->pdo->prepare("UPDATE banco_clausula SET cls_nu_ordem = cls_nu_ordem + 10 WHERE cls_nu_ordem >= :next_order");
            $stmt->execute([':next_order' => $next_order]);
            $stmt = $con->pdo->prepare("INSERT INTO banco_clausula (cls_tx, cls_nu_ordem, level) VALUES(:clausula, :ordem, :nivel)");
            $stmt->execute([':clausula' => $texto, ':ordem' => $next_order, ':nivel' => $nivel]);
            $clsNu = $con->pdo->lastInsertId();
            $con->pdo->commit();
            $rowId = "row$clsNu";
            //echo "<tr id='$rowId' data-id='$rowId' $parentAttr class='$hiddenClass'>";
            echo "<tr id='$rowId' data-id='$rowId' >";
            echo "<td style='padding-left: {$padding}px;'>";
            echo "<span class='toggle-icon'></span>";
            echo '<span class="view" id=\'' . $clsNu . '\' data-content="' . htmlspecialchars($texto)  .  '">' . htmlspecialchars($resumo) . '</span>';
            echo "</td>";
            echo '<td style="width:220px"><img src="edit.webp" class="action edit" id=\'' . $clsNu . '\' />&emsp;';
            echo '<img src="add_below.png" class="action addrow" id=\'' . $clsNu . '\' />&emsp;';
            echo '<img src="row_up.png" class="action move" data-direcao="up" id=\'' . $clsNu . '\' />&emsp;';
            echo '<img src="row_down.png" class="action move" data-direcao="down" id=\'' . $clsNu . '\' />';
            echo '</td>';
            //echo '<img src="recuo_left.png" class="action left" data-direcao="left" id=\'' . $clsNu . '\' />&emsp;';
            //echo '<img src="recuo_right.png" class="action right" data-direcao="right" id=\'' . $clsNu . '\' /></td>';
            echo "</tr>";
        } catch (Exception $e) {
            $con->pdo->rollBack();
            echo "Erro: " . $e->getMessage();
        }
    } elseif ($oper == 'ed') {
        $stmt = $con->pdo->prepare("UPDATE banco_clausula SET cls_tx=:clausula, level=:nivel  WHERE cls_nu = :id_atual");
        $stmt->execute([':clausula' => $texto, ':nivel' => $nivel, ':id_atual' => $idAtual]);
    } elseif ($oper == 'del') {
        $stmt = $con->pdo->prepare("DELETE FROM banco_clausula WHERE cls_nu = :id_atual");
        $stmt->execute([':id_atual' => $idAtual]);
        $stmt = $con->pdo->prepare("DELETE FROM edital_clausula WHERE cls_nu = :id_atual");
        $stmt->execute([':id_atual' => $idAtual]);
    } elseif ($oper == 'desativar') {
        $stmt = $con->pdo->prepare("UPDATE banco_clausula SET ativo = 0 WHERE cls_nu = :id_atual");
        $stmt->execute([':id_atual' => $idAtual]);
    } elseif ($oper == 'chk') {
        $estado = $_POST['estado'];
        $idEdital = $_POST['txtNuEdital'];

        if ($estado == 'true') {
            $stmt = $con->pdo->prepare("INSERT INTO edital_clausula (edt_id, cls_nu) VALUES (:idEdital , :id_atual)");
            $stmt->execute([':idEdital' => $idEdital, ':id_atual' => $idAtual]);
        } elseif ($estado == 'false') {
            $stmt = $con->pdo->prepare("DELETE FROM edital_clausula WHERE edt_id = :idEdital  AND cls_nu = :id_atual");
            $stmt->execute([':idEdital' => $idEdital, ':id_atual' => $idAtual]);
        }
    }
} else {   
    echo "Erro: " . $con->getMessage();
}
