<?php
require('../vendor/autoload.php');

use App\Database\Connect; // SQL Server

$con =  new Connect();
if (empty($con->getErrorMessage())) {

    $sql = "SELECT anx_id, anx_titulo  
            FROM anexo WHERE anx_ativo = true ORDER BY anx_titulo";

    $stmt = $con->pdo->prepare($sql);
    $stmt->execute();

    echo '<table id="tableOptions">';
    echo '<thead>';
    echo '<tr>';
    echo '    <th style="width:30px"></th>';
    echo '    <th>Título</th>';
    echo '</tr>';
    echo '</thead>';
    /*echo '<tfoot>';
    echo '<tr>';
    echo '    <th></th>';
    echo '    <th class="columnFilter" size="20">Título</th>';
    echo '</tr>';
    echo '</tfoot>'; */
    echo '<tbody>';

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr>';
        echo '<td>';
        echo '<input type="radio" value="' . $row['anx_id'] . '" name="opcao" />';
        echo '</td>';
        echo '<td>' . $row['anx_titulo'] . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
} else {
    echo $con->getErrorMessage();
}

unset($con);
