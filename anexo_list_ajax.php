<?php
require('../vendor/autoload.php');

use App\Database\Connect; // SQL Server

$con =  new Connect();
if (empty($con->getErrorMessage())) {

            $sql = "SELECT anx_id, anx_titulo, anx_conteudo, anx_da_criacao, anx_ativo 
            FROM anexo";
            
            $stmt = $con->pdo->prepare($sql);
            $stmt->execute();
            
            echo '<table id="tableDados">';
    echo '<thead>';
    echo '<tr>';
    echo '    <th class="nosort notexport" style="width:90px"></th>';
    echo '    <th>Título</th>';
    echo '    <th>Conteúdo</th>';    
    echo '    <th>Ativo</th>';    
    echo '    <th>Criado em</th>';    
    echo '</tr>';
    echo '</thead>';
    echo '<tfoot>';
    echo '<tr>';    
    echo '    <th></th>';
    echo '    <th class="columnFilter" size="80">Título</th>';
    echo '    <th class="columnFilter" size="65">Conteúdo</th>';    
    echo '    <th>Ativo</th>';    
    echo '    <th class="columnFilter" size="10">Criado em</th>';    
    echo '</tr>';
    echo '</tfoot>';
    echo '<tbody>';
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $texto = strip_tags($row['anx_conteudo']);
      $resumo = mb_strlen($texto) > 70 ? mb_substr($texto, 0, 70) . '...' : $texto;       

        echo '<tr id="' . $row['anx_id'] . '">';  
        echo '<td>';
        echo '<img src="eye.png" alt="" class="view classPointer" title="Visualizar anexo" />'; 
        echo '&emsp;<img src="edit.webp" alt="" class="edit classPointer" title="Editar" />';       
        echo '</td>';          
        echo '<td>' . $row['anx_titulo'] . '</td>';
        echo '<td>' . $resumo . '</td>';        
        echo '<td style="text-align:center">';                
        echo '<label class="switch sm">';
        echo '<input type="checkbox" id="chkAtivo" name="chkAtivo" class="toggle"';
        if ( $row['anx_ativo'] )  
           echo ' checked="checked "';

        echo '     ><span class="slider round"></span>';
        echo '</label>'; 
        echo '</td>';                
        echo '<td>' . date('d/m/Y H:i:s', strtotime($row['anx_da_criacao'])) . '</td>';        
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';

} else {
    echo $con->getErrorMessage();
}

unset($con);




