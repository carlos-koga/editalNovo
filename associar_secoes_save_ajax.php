<?php
require('../vendor/autoload.php');

use App\Database\Connect;

// Inicializa a variável lista2 como um array vazio
$lista2 = [];
if (isset($_POST['lista2'])) {
    $lista2 = $_POST['lista2'];
}

// Certifica que o editalId está definido e o captura
if (isset($_POST['txtEditalId'])) {
    $editalId = $_POST['txtEditalId'];
} else {
    // Você pode tratar o erro conforme necessário
    die('editalId não definido');
}

$con = new Connect();

if (empty($con->getErrorMessage())) {
    $ordem = 10;
    
    // Exclui dados existentes para o edital especificado
    $sql = "DELETE FROM edital_secao WHERE edt_id = :editalId";
    $stmt = $con->pdo->prepare($sql);
    $stmt->execute([':editalId' => $editalId]);
    
    // Insere as novas seções com a ordem definida
    $sql = "INSERT INTO edital_secao (edt_id, sec_id, sec_ordem) VALUES (:editalId, :secaoId, :ordem)";
    foreach ($lista2 as $secaoId) {
        $stmt = $con->pdo->prepare($sql);
        $stmt->execute([
            ':editalId' => $editalId,
            ':secaoId'  => $secaoId,
            ':ordem'    => $ordem
        ]);
        $ordem += 10;
    }
    
} else {
    echo $con->getErrorMessage();
}

unset($con);
?>