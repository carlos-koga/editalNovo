<?php
require('../vendor/autoload.php');

use App\Database\Connect; 

$con =  new Connect();
if (empty($con->getErrorMessage())) {    
    $con->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo '<div data-value="0">Edital modelo</div>';
    $stmt = $con->pdo->prepare("SELECT edt_id, edt_nome || '_'  || edt_versao || '.' || edt_designacao AS nome , edt_bloqueado FROM edital WHERE edt_ativo = true ORDER BY edt_nome");
    $stmt->execute();    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row) {
            echo '<div data-value="' . htmlspecialchars($row['edt_id'], ENT_QUOTES, 'UTF-8') . '" ';
            if ( $row['edt_bloqueado'] ) {
                echo ' class="bloqueado" title="Bloqueado" data-bloqueado="' . htmlspecialchars($row['edt_bloqueado'], ENT_QUOTES, 'UTF-8') . '">';
            } else {
                echo '>';
            }                
            echo htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8');            
            echo '</div>';
            
        }    
    }
    
} else {
    echo $con->getErrorMessage();
}
unset($con->pdo);
?>