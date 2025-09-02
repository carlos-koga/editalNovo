<?php
require('../vendor/autoload.php');

use App\Database\Connect; 
header('Content-Type: text/html; charset=utf-8');

$id = $_POST['anexoId'];
$numero = $_POST['numero'];
$numeroAnexo = '<h3 style="text-align:center">Modelo ' . $numero . '</h3>';



if (!is_null($id) && $id !== "null")  {    
    $con =  new Connect();
    if (empty($con->getErrorMessage())) {
        $con->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT anx_conteudo FROM anexo WHERE anx_id = :id";            
        $stmt = $con->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $conteudo  = $stmt->fetchColumn();
        if ($conteudo != false) {
        echo '<hr>';
        echo $numeroAnexo;
        echo $conteudo;
        }

    } else {
        echo $con->getErrorMessage();
    }

    unset($con);    
}