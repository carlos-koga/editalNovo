<?php
require('../vendor/autoload.php');

use App\Database\Connect;

$tipo = $_POST['tipo'];
$editalID = $_POST['editalId'];

$con =  new Connect();
if (empty($con->getErrorMessage())) {
    if ($tipo == 'disponiveis') {
        $sql = "SELECT G.grp_nu, G.grp_no
                FROM grupo G
                WHERE G.grp_nu NOT IN (
                   SELECT (jsonb_array_elements(edt_aplicacao->'grupos'))::int
                   FROM edital WHERE edt_id = :id
                )
                AND G.grp_in_ativo = 'A' ORDER BY G.grp_no ";
    
    } elseif ($tipo == 'selecionados') {
        $sql = "SELECT G.grp_nu, G.grp_no
    FROM grupo G
    JOIN (  
      SELECT jsonb_array_elements(edt_aplicacao->'grupos')::int AS grupo_nu
      FROM edital WHERE edt_id = :id
    ) AS J 
    ON G.grp_nu = J.grupo_nu ORDER BY G.grp_no";
    } else {
        die('Nenhum parâmetro válido para "tipo"');
    } 

    
    $stmt = $con->pdo->prepare($sql);
    $row = $stmt->execute([':id' => $editalID]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<option value="' . $row['grp_nu'] . '">' . $row['grp_no'] . '</option>';
    }
} else {
    echo $con->getErrorMessage();
    die();
}
