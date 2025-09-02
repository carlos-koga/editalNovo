<?php
require('../vendor/autoload.php');

use App\Database\Connect; 

$bloqueado = false;

if (isset( $_POST['edital']))
   $tipoEdital = $_POST['edital'];

   $con =  new Connect();
   if (empty($con->getErrorMessage())) {
       $con->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ( $tipoEdital == 0) {
        $stmt = $con->pdo->prepare("SELECT cls_nu, level, cls_tx , ativo FROM banco_clausula ORDER BY cls_nu_ordem");
        $stmt->execute();
    } else {
        $stmt = $con->pdo->prepare("SELECT B.cls_nu, level, cls_tx , E.cls_nu AS chk, B.ativo FROM banco_clausula AS B LEFT JOIN (SELECT * FROM edital_clausula WHERE edt_id = :editalId ) E ON B.cls_nu = E.cls_nu ORDER BY cls_nu_ordem");
        $stmt->execute([':editalId' => $tipoEdital]);

        $stmt2 = $con->pdo->prepare("SELECT edt_bloqueado FROM edital WHERE edt_id = :editalId");
        $stmt2->execute([':editalId' => $tipoEdital]);
        $row = $stmt2->fetch(PDO::FETCH_ASSOC);
        $bloqueado = $row['edt_bloqueado'];
    }    
    
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<table id="clausulas">';
    echo '<caption style="background-color: steelblue; color:white">Banco de cláusulas para edital</caption>';

    $stack = [];
    $levels = [];

    // Mapeia os níveis por cls_nu
    foreach ($dados as $row) {
        $levels[$row['cls_nu']] = is_numeric($row['level']) ? (int)$row['level'] : 1;
    }

    foreach ($dados as $row) {
        $clsNu = $row['cls_nu'];
        $nivel = $levels[$clsNu];
        $padding = ($nivel - 1) * 20;
        $ativo = $row['ativo'];

        $texto = strip_tags($row['cls_tx']);
        $resumo = mb_strlen($texto) > 70 ? mb_substr($texto, 0, 70) . '...' : $texto;       
            

        // Define o pai com base na pilha
        while (count($stack) >= $nivel) {
            array_pop($stack);
        }
        $parentClsNu = $stack[$nivel - 1] ?? null;
        $stack[$nivel] = $clsNu;

        $rowId = "row$clsNu";
        $parentAttr = $parentClsNu ? "data-parent='row$parentClsNu'" : "";
        $hiddenClass = $nivel > 1 ? "hidden" : "";

        // Verifica se o próximo item é filho deste
        $nextIndex = array_search($row, $dados, true) + 1;
        $hasChildren = isset($dados[$nextIndex]) && $levels[$dados[$nextIndex]['cls_nu']] > $nivel;

        echo "<tr id='$rowId' data-id='$rowId' $parentAttr class='$hiddenClass'>";
        echo "<td style='padding-left: {$padding}px;";
        if (!$ativo )
            echo "color:red;";
        
            echo "'>";
        if ($hasChildren) {
            echo "<span class='toggle-icon' onclick=\"toggleChildren('$rowId')\">▶</span>";
        } else {
            echo "<span class='toggle-icon'></span>";
        }
        echo '<span class="view" id=\'' . $clsNu . '\' data-content="' . htmlspecialchars($texto)  .  '">' . htmlspecialchars($resumo) . '</span>';
        echo "</td>";        
        if ( $tipoEdital == 0) {
            if (!$ativo) {
                echo '<td style="width:220px"><img src="edit.webp" class="img-desabilitada " id=\'' . $clsNu . '\' title="Alterar, desativar ou excluir" />&emsp;';
                echo '<img src="add_below.png" class="img-desabilitada " id=\'' . $clsNu . '\' title="Adicionar novo item abaixo deste"/>&emsp;';
                echo '<img src="row_up.png" class="img-desabilitada " data-direcao="up" id=\'' . $clsNu . '\' " title="Mover para cima"/>&emsp;';
                echo '<img src="row_down.png" class="img-desabilitada " data-direcao="down" id=\'' . $clsNu . '\' title="Mover para baixo" />';
                echo '&emsp;&emsp;&emsp;<img src="replace.png" class="img-desabilitada"  id=\'' . $clsNu . '\' title="Substituir este item"/>';
                echo '</td>';
            } else {
                echo '<td style="width:220px"><img src="edit.webp" class="action edit" id=\'' . $clsNu . '\' title="Alterar, desativar ou excluir" />&emsp;';
                echo '<img src="add_below.png" class="action addrow" id=\'' . $clsNu . '\' title="Adicionar novo item abaixo deste"/>&emsp;';
                echo '<img src="row_up.png" class="action move" data-direcao="up" id=\'' . $clsNu . '\' " title="Mover para cima"/>&emsp;';
                echo '<img src="row_down.png" class="action move" data-direcao="down" id=\'' . $clsNu . '\' title="Mover para baixo" />';
                echo '&emsp;&emsp;&emsp;<img src="replace.png" class="action replace" id=\'' . $clsNu . '\' title="Substituir este item"/>';        
                echo '</td>';
            }
        } else {
           echo '<td><input type="checkbox"  class="chkEdital" id=\'' . $clsNu . '\'' ;
           if (!empty($row['chk'])) {
               echo ' checked="checked" ' ;
           }
           if ( $bloqueado )
                echo ' disabled="disabled" ';

           echo ' /></td>';
        }   
        
        //echo '<img src="recuo_left.png" class="action left" data-direcao="left" id=\'' . $clsNu . '\' />&emsp;';
        //echo '<img src="recuo_right.png" class="action right" data-direcao="right" id=\'' . $clsNu . '\' /></td>';        
        
        echo "</tr>";
    }

    echo '</table>';

} else {
    echo "Erro: " . $con->getErrorMessage();
}
?>