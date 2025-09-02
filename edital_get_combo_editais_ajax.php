<?php
require('../vendor/autoload.php');

use App\Database\Connect; 

$con =  new Connect();
if (empty($con->getErrorMessage())) {    
    $con->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
    $sql = "SELECT edt_id, edt_nome  || '_' ||  edt_versao || '.' || edt_designacao AS nome , 
                   edt_bloqueado
            FROM edital 
            WHERE edt_ativo = true 
            ORDER BY edt_nome";

    $stmt = $con->pdo->prepare($sql);
    $stmt->execute();    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row) {
            echo '<option value="' . htmlspecialchars($row['edt_id'], ENT_QUOTES, 'UTF-8') . '" data-type=""';

            if ( $row['edt_bloqueado'] ) {
                echo ' class="bloqueado" title="Bloqueado" >';
            } else {
                echo '>';
            }                
            echo htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8');            
            echo '</option>';
            
        }    
    }
    
} else {
    echo $con->getErrorMessage();
}
unset($con->pdo);
?>