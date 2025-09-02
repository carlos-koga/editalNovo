<?php
require('../vendor/autoload.php');

use App\Database\Connect; // SQL Server

$campo = $_POST['campo']; // Exemplo: {modalidade_edital,modalidade}
$valor = $_POST['valor'];
$carrinhoId = $_POST['id'];

var_dump($campo);
var_dump($valor);
var_dump($carrinhoId);

$con =  new Connect();
if (empty($con->getErrorMessage())) {
    $stmt = $con->pdo->prepare("
    UPDATE carrinho_json
    SET json_data = jsonb_set(json_data, :campo, to_jsonb(:valor::text), false)
    WHERE crr_nu_carrinho = :id");

    $stmt->bindValue(':campo', $campo);
    $stmt->bindValue(':valor', $valor);
    $stmt->bindValue(':id', $carrinhoId, PDO::PARAM_INT);

    $stmt->execute();
} else {
    echo $con->getErrorMessage();
}

unset($con);
