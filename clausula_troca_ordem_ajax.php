<?php
require('../vendor/autoload.php');

use App\Database\Connect; 



$id_atual = $_POST['id_atual'];
$direcao = $_POST['direcao'];

$con =  new Connect();
if (empty($con->getErrorMessage())) {    
    $con->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $con->pdo->prepare("SELECT set_config('application.user', 'Koga', false)");
    $stmt->execute();

    $i = 1;

    // Consulta os dados
    $stmt = $con->pdo->prepare("SELECT trocar_ordem(:id_atual, :direcao)");
    $stmt->execute(['id_atual' => $id_atual, 'direcao' => $direcao]);
    $result = $stmt->fetchColumn();
    echo $result;
}  else {
    echo $con->getErrorMessage();
}
?>
