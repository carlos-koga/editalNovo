<?php
require('../vendor/autoload.php');

use App\Database\Connect; 

header('Content-Type: text/html; charset=utf-8');

if (isset($_POST['txtID'])) {
   $idAtual = $_POST['txtID'];
} elseif (isset($_GET['txtID'])) {
    $idAtual = $_GET['txtID'];
}    

if (isset($_POST['txtOper'])) {
   $oper  = $_POST['txtOper'];
} elseif (isset($_GET['txtOper'])) {
   $oper  = $_GET['txtOper'];   
}   

if (isset($_POST['txtTitulo']) )
    $titulo = $_POST['txtTitulo'];

if (isset($_POST['txtTexto']) )
    $texto = $_POST['txtTexto'];


if (($oper == 'inc') ||  ($oper == 'ed')  ) {
    $texto = str_replace('<!--?xml encoding="utf-8" ?-->', '', $texto);


    $textNoHTML = strip_tags($texto);

    $resumo = mb_strlen($textNoHTML) > 70 ? mb_substr($textNoHTML, 0, 70) . '...' : $textNoHTML;
}

$con =  new Connect();
if (empty($con->getErrorMessage())) {
    $con->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $con->pdo->prepare("SELECT set_config('application.user', 'Koga', false)");
    $stmt->execute();


    if ($oper == 'inc') {        

        try {
            $stmt = $con->pdo->prepare("INSERT INTO anexo (anx_titulo, anx_conteudo) VALUES(:titulo, :conteudo)");
            $stmt->execute([':titulo' => $titulo, ':conteudo' => $texto]);            
            
        } catch (Exception $e) {            
            echo "Erro: " . $e->getMessage();
        }
    } elseif ($oper == 'ed') {
        $stmt = $con->pdo->prepare("UPDATE anexo SET anx_titulo=:titulo, anx_conteudo=:conteudo  WHERE anx_id = :id_atual");
        $stmt->execute([':titulo' => $titulo, ':conteudo' => $texto, ':id_atual' => $idAtual]);
        //renderRow($idAtual, $nivel, $texto, $resumo, $stack = [], $isNew = false) ;
    } elseif ($oper == 'del') {
        $con->pdo->beginTransaction();
        $stmt = $con->pdo->prepare("DELETE FROM anexo WHERE anx_id = :id_atual");
        $stmt->execute([':id_atual' => $idAtual]);        
        $con->pdo->commit();
    } elseif ($oper == 'toggle') {
        $elemento = $_POST['txtCampo'];         
        $valor = $_POST['txtValor'];
        $sql = "UPDATE anexo SET anx_ativo = :valor ";  
        try {                
                $sql .= " WHERE anx_id = :id ";
                $stmt = $con->pdo->prepare($sql);
                $stmt->execute([                                    
                    ':valor' => $valor,
                    ':id' => $idAtual
                ]);
            } catch (PDOException $e) {
                echo "Erro ao ativar/desativar anexo: " . $e->getMessage();
            }    

        
    } elseif ($oper == 'get') {
        $stmt = $con->pdo->prepare("SELECT anx_conteudo FROM anexo WHERE anx_id = :id_atual");
        $stmt->execute([':id_atual' => $idAtual]);
        $conteudo  = $stmt->fetchColumn();
        echo $conteudo;

    } elseif ($oper == 'getjson') {
        $stmt = $con->pdo->prepare("SELECT anx_titulo, anx_conteudo, anx_ativo FROM anexo WHERE anx_id = :id_atual");
        $stmt->execute([':id_atual' => $idAtual]);        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($resultado);

    }
}  else {
    echo $con->getErrorMessage();
}
