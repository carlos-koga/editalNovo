<?php
require('../vendor/autoload.php');

use App\Database\Connect; 

header('Content-Type: application/json');


$oper = htmlspecialchars($_POST['txtOper'], ENT_QUOTES, 'UTF-8');
if ( isset($_POST['txtNome'])) 
    $strNome = htmlspecialchars($_POST['txtNome'], ENT_QUOTES, 'UTF-8');
if ( isset($_POST['txtVersao'])) 
   $intVersao = htmlspecialchars($_POST['txtVersao'], ENT_QUOTES, 'UTF-8');
if ( isset($_POST['txtDesignacao'])) 
    $intDesignacao = htmlspecialchars($_POST['txtDesignacao'], ENT_QUOTES, 'UTF-8');
if ( isset($_POST['txtDoc'])) 
    $strDoc = htmlspecialchars($_POST['txtDoc'], ENT_QUOTES, 'UTF-8');
if ( isset($_POST['txtID'])) 
   $intId =  htmlspecialchars($_POST['txtID'], ENT_QUOTES, 'UTF-8'); 
if ( isset($_POST['txtVigencia'])) 
   $vigencia = htmlspecialchars($_POST['txtVigencia'], ENT_QUOTES, 'UTF-8'); 
   
   $modalidade        = isset($_POST['chkModalidade']) ? $_POST['chkModalidade'] : [];
   $formaContratacao  = isset($_POST['chkForma']) ? $_POST['chkForma'] : [];
   $grupos            = isset($_POST['grupos']) ? $_POST['grupos'] : [];
// converte cada elemento para int
$grupos = array_map('intval', $grupos);

   // Monta o JSON que será armazenado na coluna JSONB
$jsonToStore = json_encode(
    [
        'modalidade'        => $modalidade,
        'forma_contratacao' => $formaContratacao,
        'grupos'            => $grupos
    ],
    JSON_UNESCAPED_UNICODE
);



$con =  new Connect();
if (empty($con->getErrorMessage())) {
    $con->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $con->pdo->prepare("SELECT set_config('application.user', 'Koga', false)");
    $stmt->execute();
    /*if ( ($oper == 'ed') || ($oper == 'del') ) {
        $sql = "SELECT [login] ";
        $sql .= " FROM [edital_modelo] ";
        $sql .= " WHERE [login] = :login AND [sid] = :sid ";
        $stmt = $con->pdo->prepare($sql);
        $stmt->execute([
                ':login' => $login,
                ':sid' => $sid
            ]);        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);        
        if (!$resultado) {
          echo  "Houve violação do valor-chave!";
          exit;
        }   
    } */

    if ($oper == 'inc') {
        try {
            $sql = "INSERT INTO edital (edt_nome, edt_versao, edt_designacao, edt_doc_aprovacao, edt_da_vigencia, edt_aplicacao  ) ";
            $sql .= " VALUES (:nome, :versao, :designacao, :doc, :vigencia, :jsonData)";
            $stmt = $con->pdo->prepare($sql);
            $stmt->execute([                
                ':nome' => $strNome,                
                ':versao' => $intVersao,
                ':designacao' => $intDesignacao,
                ':doc' => $strDoc,
                ':vigencia' => $vigencia,
                ':jsonData' => $jsonToStore
            ]);
            echo json_encode([
                "erro" => 0,
                "msg" => "Inclusão realizado com sucesso"
            ]);
        
        } catch (PDOException $e) {
            echo json_encode([
                "erro" => 1,
                "msg" => "Erro ao incluir edital: " . $e->getMessage()
            ]);        
            
        }
    } elseif ($oper == 'ed') {
        try {
            $sql = "UPDATE edital SET edt_nome = :nome, edt_versao = :versao, edt_designacao = :designacao, edt_doc_aprovacao = :doc , edt_da_vigencia = :vigencia, edt_aplicacao = :jsonData ";
            $sql .= " WHERE edt_id = :id ";
            $stmt = $con->pdo->prepare($sql);
            $stmt->execute([                
                ':nome' => $strNome,                
                ':versao' => $intVersao,
                ':designacao' => $intDesignacao,
                ':doc' => $strDoc,
                ':vigencia' => $vigencia,
                ':jsonData' => $jsonToStore,
                ':id' => $intId
            ]);
            echo json_encode([
                "erro" => 0,
                "msg" => "Alteração realizada com sucesso"
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                "erro" => 1,
                "msg" => "Erro ao alterar edital: " . $e->getMessage()
            ]);
        }
    } elseif ($oper == 'del') {
        try {
            $sql = "DELETE FROM edital ";
            $sql .= " WHERE edt_id = :id";
            $con->pdo->beginTransaction(); 
            $stmt = $con->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $intId
            ]);

            $stmt = $con->pdo->prepare("ALTER TABLE edital_clausula DISABLE TRIGGER trg_log_trigger;");
            $stmt->execute();

            $sql = "DELETE FROM edital_clausula ";
            $sql .= " WHERE edt_id = :id";
            $stmt = $con->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $intId
            ]);

            $sql = "DELETE FROM edital_secao ";
            $sql .= " WHERE edt_id = :id";
            $stmt = $con->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $intId
            ]);

            
            $stmt = $con->pdo->prepare("ALTER TABLE edital_clausula ENABLE TRIGGER trg_log_trigger;");
            $stmt->execute();

            $con->pdo->commit(); 
            echo json_encode([
                "erro" => 0,
                "msg" => "Exclusão realizada com sucesso"
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                "erro" => 1,
                "msg" => "Erro ao excluir edital: " . $e->getMessage()
            ]);
            
        }
    } elseif ($oper == 'toggle') {
         $elemento = $_POST['txtCampo'];
         $valor = $_POST['txtValor'];
         if ($elemento === 'chkAtivo') {
             $sql = "UPDATE edital SET edt_ativo = :valor ";  
             if ( $valor == 1 ) {
                $msgOk = 'Edital ativado com sucesso';
                $msgNotOk = 'Erro ao ativar edital';
              } else {
                $msgOk = 'Edital desativado com sucesso';
                $msgNotOk = 'Erro ao desativar edital';
              }
         } elseif ($elemento === 'chkBloqueado') {
              $sql = "UPDATE edital SET edt_bloqueado = :valor ";
              if ( $valor == 1 ) {
                $msgOk = 'Edital bloqueado com sucesso';
                $msgNotOk = 'Erro ao bloquear edital';
              } else {
                $msgOk = 'Edital desbloqueado com sucesso';
                $msgNotOk = 'Erro ao desbloquear edital';
              }
         }              
         
         try {                
                $sql .= " WHERE edt_id = :id ";
                $stmt = $con->pdo->prepare($sql);
                $stmt->execute([                                    
                    ':valor' => $valor,
                    ':id' => $intId
                ]);
                echo json_encode([
                    "erro" => 0,
                    "msg" => $msgOk
                ]);
            } catch (PDOException $e) {
                echo json_encode([
                    "erro" => 1,
                    "msg" => $msgNotOk . $e->getMessage()
                ]);
            }    

    } elseif ($oper == 'get') {
        $sql = "SELECT edt_nome, edt_versao, edt_designacao, edt_bloqueado, edt_doc_aprovacao, edt_ativo, 
                       edt_da_criacao, TO_CHAR(edt_da_vigencia, 'DD/MM/YYYY')  AS edt_da_vigencia,
                       edt_aplicacao ";
        $sql .= " FROM edital ";
        $sql .= " WHERE edt_id = :id ";
        $stmt = $con->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $intId
            ]);        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);        
        echo json_encode($resultado);
    } elseif ($oper == 'clonar') {
        try {
            $sql = "INSERT INTO edital (edt_nome,  edt_versao, edt_designacao, edt_doc_aprovacao, edt_da_vigencia, edt_aplicacao) ";
            $sql .= " VALUES (:nome, :versao, :designacao, :doc, :vigencia, :jsonData)";
            $con->pdo->beginTransaction(); 
            $stmt = $con->pdo->prepare($sql);
            $stmt->execute([                
                ':nome' => $strNome,                
                ':versao' => $intVersao,
                ':designacao' => $intDesignacao,
                ':doc' => $strDoc,
                ':vigencia' => $vigencia,
                ':jsonData' => $jsonToStore
            ]);                        
            
            if (!isset($_POST['txtID'])) {
                throw new Exception("ID do edital a ser clonado não foi informado.");
            }
            
          
            $ultimoId = $con->pdo->lastInsertId('public.edital_edt_id_seq');          
            $stmt = $con->pdo->prepare("ALTER TABLE edital_clausula DISABLE TRIGGER trg_log_trigger;");
            $stmt->execute();
            $sql = "INSERT INTO edital_clausula (edt_id, cls_nu) ";
            $sql .= " SELECT :idAtual, cls_nu FROM  edital_clausula WHERE edt_id = :novoId";
            $stmt = $con->pdo->prepare($sql);
            $stmt->execute([                
                ':novoId' => $intId,
                ':idAtual' => $ultimoId                
            ]);

            // duplica as 
            $stmt = $con->pdo->prepare("
                                    INSERT INTO edital_secao (edt_id, sec_id, sec_ordem)
                                    SELECT :novoId, sec_id, sec_ordem
                                    FROM edital_secao
                                    WHERE edt_id = :idAtual 
                                    ");
            $stmt->execute([
                'idAtual' => $ultimoId,
                'novoId' => $intId
            ]);


            $stmt = $con->pdo->prepare("ALTER TABLE edital_clausula ENABLE TRIGGER trg_log_trigger;");
            $stmt->execute();
            echo json_encode([
                "erro" => 0,
                "msg" => "Edital clonado com sucesso"
            ]);
            $con->pdo->commit(); 
        } catch (PDOException $e) {
            $con->pdo->rollBack(); 
            echo json_encode([
                "erro" => 1,
                "msg" => "Erro ao criar/clonar edital: " . $e->getMessage()
            ]);
            
        }
    
    } else {
        echo json_encode([
            "erro" => 1,
            "msg" => 'Operação "' . $oper . '" não reconhecida!'
        ]);
    }
} else {
    echo json_encode([
        "erro" => 1,
        "msg" => 'Erro de conexão com o banco de dados: ' . $con->getErrorMessage()
    ]);        
    
}

unset($con);




 
