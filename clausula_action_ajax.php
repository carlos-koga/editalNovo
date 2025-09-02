<?php
require('../vendor/autoload.php');

use App\Database\Connect; 

header('Content-Type: application/json; charset=utf-8'); 

$idAtual = $_POST['txtIdAtual'];
$oper  = $_POST['txtOper'];
$texto = $_POST['txtTexto'];
$pai   = $_POST['hasChildren'];
if ($pai == 'pai') 
   $hasChildren = true;
else 
   $hasChildren = false;




if (($oper == 'inc') ||  ($oper == 'ed') ||  ($oper == 'replace') ||  ($oper == 'desativar')  ) {
    $texto = str_replace('<!--?xml encoding="utf-8" ?-->', '', $texto);

libxml_use_internal_errors(true);
//$dom = new DOMDocument();
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->loadHTML($texto);
libxml_clear_errors();


// pega o nível do item
$li = $dom->getElementsByTagName('li')->item(0); // Pega o primeiro <li>

if ($li) {
    $nivel = $li->getAttribute('data-level');
} else {
    $nivel = 1;
}


    //Elimina o <ol> colocado automaticamente pelo TinyMCE
    /*foreach (['ol', 'ul'] as $tag) {
        $lists = $dom->getElementsByTagName($tag);
    
        foreach ($lists as $list) {
            if (!$list->hasAttribute('class')) { // Verifica se não há classe definida
                $parent = $list->parentNode;
    
                while ($list->firstChild) {
                    $parent->insertBefore($list->firstChild, $list);
                }
    
                $parent->removeChild($list);
                //break; // Sai do loop após remover o primeiro <ol> ou <ul> sem classe
            }
        }
    } */

    $tags = ['ol', 'ul'];

    foreach ($tags as $tag) {
        $lists = $dom->getElementsByTagName($tag);

        // Como getElementsByTagName retorna uma NodeList dinâmica, convertemos para array estático
        $elements = [];
        foreach ($lists as $node) {
            $elements[] = $node;
        }

        // Itera de trás para frente
        for ($i = count($elements) - 1; $i >= 0; $i--) {
            $list = $elements[$i];

            if (!$list->hasAttributes() || !$list->hasAttribute('class')) {
                $parent = $list->parentNode;

                // Move os filhos para o pai
                while ($list->firstChild) {
                    $parent->insertBefore($list->firstChild, $list);
                }

                // Remove a tag <ol> ou <ul>
                $parent->removeChild($list);
            }
        }
    }
    
    $body = $dom->getElementsByTagName('body')->item(0);
    $output = '';
    foreach ($body->childNodes as $child) {
        $output .= $dom->saveHTML($child);
    }
    

    $texto = $output;
    $textNoHTML = strip_tags($texto);

    $resumo = mb_strlen($textNoHTML) > 70 ? mb_substr($textNoHTML, 0, 70) . '...' : $textNoHTML;
}

$con =  new Connect();
if (empty($con->getErrorMessage())) {
    $con->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $con->pdo->prepare("SELECT set_config('application.user', 'Koga', false)");
    $stmt->execute();


    if ($oper == 'inc') {
        $stmt = $con->pdo->prepare("SELECT cls_nu_ordem FROM banco_clausula WHERE cls_nu = :id_atual");
        $stmt->execute([':id_atual' => $idAtual]);
        $ordem_atual = $stmt->fetchColumn();
        

        $stmt = $con->pdo->prepare("SELECT cls_nu_ordem FROM banco_clausula WHERE cls_nu_ordem > :ordem_atual ORDER BY cls_nu_ordem LIMIT 1");
        $stmt->execute([':ordem_atual' => $ordem_atual]);
        $next_order = $stmt->fetchColumn();
        if ($next_order == false) {
            $next_order = $ordem_atual + 10;
        }
        

        try {
            $con->pdo->beginTransaction();
            $stmt = $con->pdo->prepare("ALTER TABLE banco_clausula DISABLE TRIGGER trg_log_trigger;");
            $stmt->execute();
            $stmt = $con->pdo->prepare("UPDATE banco_clausula SET cls_nu_ordem = cls_nu_ordem + 10 WHERE cls_nu_ordem >= :next_order;");            
            $stmt->execute([':next_order' => $next_order]);
            $stmt = $con->pdo->prepare("ALTER TABLE banco_clausula ENABLE TRIGGER trg_log_trigger;");
            $stmt->execute();
            $stmt = $con->pdo->prepare("INSERT INTO banco_clausula (cls_tx, cls_nu_ordem, level) VALUES(:clausula, :ordem, :nivel)");
            $stmt->execute([':clausula' => $texto, ':ordem' => $next_order, ':nivel' => $nivel]);
            $clsNu = $con->pdo->lastInsertId('public.banco_clausula_cls_nu_seq');            
            $con->pdo->commit();

            echo json_encode([
                "erro" => 0,
                "msg" => renderRow($clsNu, $nivel, $texto, $resumo, $hasChildren, $stack = [], $isNew = true, $isAtivo = true) 
              ]);

        } catch (Exception $e) {
            $con->pdo->rollBack();
            echo json_encode([
                "erro" => 1,
                "msg" => "Erro Inclusão realizado com sucesso",
                "description" => $e->getMessage()
            ]);            
        }
    } elseif ($oper == 'ed') {
        $stmt = $con->pdo->prepare("UPDATE banco_clausula SET cls_da_update = CURRENT_TIMESTAMP, cls_tx=:clausula, level=:nivel  WHERE cls_nu = :id_atual");
        $stmt->execute([':clausula' => $texto, ':nivel' => $nivel, ':id_atual' => $idAtual]);
        echo json_encode([
            "erro" => 0,
            "msg" => renderRow($idAtual, $nivel, $texto, $resumo, $hasChildren, $stack = [], $isNew = false, $isAtivo = true)  
        ]);
    } elseif ($oper == 'del') {
        $justificativa = $_POST['txtJustificativa'];
        $con->pdo->beginTransaction();
        $stmt = $con->pdo->prepare("ALTER TABLE banco_clausula DISABLE TRIGGER trg_log_trigger;");
        $stmt->execute();

        $stmt = $con->pdo->prepare("UPDATE banco_clausula SET cls_obs = :justificativa, cls_da_inativacao = CURRENT_TIMESTAMP WHERE cls_nu = :id_atual");
        $stmt->execute([':justificativa' => $justificativa,
                        ':id_atual' => $idAtual]);

        $stmt = $con->pdo->prepare("ALTER TABLE banco_clausula ENABLE TRIGGER trg_log_trigger;");
        $stmt->execute();                

        $stmt = $con->pdo->prepare("DELETE FROM banco_clausula WHERE cls_nu = :id_atual");
        $stmt->execute([':id_atual' => $idAtual]);
        
        $stmt = $con->pdo->prepare("DELETE FROM edital_clausula WHERE cls_nu = :id_atual");
        $stmt->execute([':id_atual' => $idAtual]);
        $stmt = $con->pdo->prepare("ALTER TABLE banco_clausula ENABLE TRIGGER trg_log_trigger;");
        $stmt->execute();
        $con->pdo->commit();
        echo json_encode([
            "erro" => 0,
            "msg" => 'Exclusão realizada com sucesso'
        ]);
    } elseif ($oper == 'desativar') {
        $stmt = $con->pdo->prepare("UPDATE banco_clausula SET cls_da_inativacao = CURRENT_TIMESTAMP, ativo = false WHERE cls_nu = :id_atual");
        $stmt->execute([':id_atual' => $idAtual]);
        
        echo json_encode([
            "erro" => 0,
            "msg" => renderRow($idAtual, $nivel, $texto, $resumo, $hasChildren, $stack = [], $isNew = false, $isAtivo = false) 
        ]);
    } elseif ($oper == 'chk') {
        $estado = $_POST['estado'];
        $idEdital = $_POST['txtNuEdital'];

        if ($estado == 'true') {
            $stmt = $con->pdo->prepare("INSERT INTO edital_clausula (edt_id, cls_nu) VALUES (:idEdital , :id_atual) ON CONFLICT (edt_id, cls_nu) DO NOTHING");
            $stmt->execute([':idEdital' => $idEdital, ':id_atual' => $idAtual]);
        } elseif ($estado == 'false') {
            $stmt = $con->pdo->prepare("DELETE FROM edital_clausula WHERE edt_id = :idEdital  AND cls_nu = :id_atual");
            $stmt->execute([':idEdital' => $idEdital, ':id_atual' => $idAtual]);
        }

        echo json_encode([
            "erro" => 0,
            "msg" => ''
        ]);
    } elseif ($oper == 'replace') {
        $stmt = $con->pdo->prepare("SELECT cls_nu_ordem FROM banco_clausula WHERE cls_nu = :id_atual");
        $stmt->execute([':id_atual' => $idAtual]);
        $ordem_atual = $stmt->fetchColumn();

        try {
            $con->pdo->beginTransaction();
            
            //$stmt = $con->pdo->prepare("ALTER TABLE banco_clausula DISABLE TRIGGER trg_log_trigger;");
            //$stmt->execute(); 

            // Vou usar a mesma ordem.
            //$stmt = $con->pdo->prepare("UPDATE banco_clausula SET cls_nu_ordem = cls_nu_ordem + 10 WHERE cls_nu_ordem >= :next_order");
            //$stmt->execute([':next_order' => $next_order]);
            //$stmt = $con->pdo->prepare("ALTER TABLE banco_clausula ENABLE TRIGGER trg_log_trigger;");
            //$stmt->execute(); 
            $stmt = $con->pdo->prepare("INSERT INTO banco_clausula (cls_tx, cls_nu_ordem, level) VALUES(:clausula, :ordem, :nivel)");
            $stmt->execute([':clausula' => $texto, ':ordem' =>  $ordem_atual, ':nivel' => $nivel]);
            $clsNu = $con->pdo->lastInsertId("banco_clausula_cls_nu_seq");

            // Desativa claúsula
            $obs = "Substituído por " . $clsNu;
            $stmt = $con->pdo->prepare("UPDATE banco_clausula SET ativo = false , cls_da_inativacao = CURRENT_TIMESTAMP , cls_obs = :obs WHERE cls_nu = :id_atual;");
            $stmt->execute([':obs' => $obs ,
                            ':id_atual' => $idAtual]);

            $stmt = $con->pdo->prepare("UPDATE edital_clausula AS EC
                                            SET cls_nu = :novo
                                            FROM edital AS E
                                            WHERE EC.edt_id = E.edt_id
                                            AND E.edt_ativo = true
                                            AND E.edt_bloqueado = false AND cls_nu = :id_atual;");
            $stmt->execute([':novo' => $clsNu,
                            ':id_atual' => $idAtual]);
            $con->pdo->commit();
            

            echo json_encode([
                "erro" => 0,
                "msg" => renderRow($clsNu, $nivel, $texto, $resumo, $hasChildren, $stack = [], $isNew = false, $isAtivo = true) 
                ]);

        } catch (Exception $e) {
            $con->pdo->rollBack();
            echo json_encode([
                "erro" => 1,
                "msg" => "Erro ao substituir cláusula",
                "description" => $e->getMessage()
            ]);                        
        }



    }
}  else {
    echo json_encode([
        "erro" => 1,
        "msg" => "Erro ao conectar ao banco de dados",
        "description" => $con->getErrorMessage()
    ]);                            
}


function renderRow($clsNu, $nivel, $texto, $resumo, $hasChildren, $stack = [], $isNew = false, $isAtivo = true ) {
    $rowId = "row$clsNu";

    // Calcular padding e classe
    $padding = ($nivel - 1) * 20;
    $hiddenClass = $nivel > 1 ? "hidden" : "";

    // Determinar o pai (se houver)
    $parentClsNu = $stack[$nivel - 1] ?? null;
    $parentAttr = $parentClsNu ? "data-parent='row$parentClsNu'" : "";

      
    // Gerar HTML
    $html = "<tr id='$rowId' data-id='$rowId' $parentAttr>";
    $html .= "<td style='padding-left: {$padding}px;";
    
    if (!$isAtivo )
       $html .= "color:red;";
    
    $html .= "'>";

    if ($hasChildren) {
        $html .= "<span class='toggle-icon' onclick=\"toggleChildren('$rowId')\">▶</span>";
    } else {
        $html .=  "<span class='toggle-icon'></span>";
    }

    $html .= '<span class="view" id=\'' . $clsNu . '\' data-content="' . htmlspecialchars($texto)  .  '">' . htmlspecialchars($resumo) . '</span>';
    $html .= "</td>";
    
    $html .= '<td style="width:220px">';
    if (!$isAtivo ) {
        $html .= '<img src="edit.webp" class="img-desabilitada" id=\'' . $clsNu . '\' title="Alterar, desativar ou excluir" />&emsp;';
        $html .= '<img src="add_below.png" class="img-desabilitada" id=\'' . $clsNu . '\' title="Adicionar novo item abaixo deste"/>&emsp;';
        $html .= '<img src="row_up.png" class="img-desabilitada" id=\'' . $clsNu . '\' title="Mover para cima"/>&emsp;';
        $html .= '<img src="row_down.png" class="img-desabilitada" id=\'' . $clsNu . '\' title="Mover para baixo" />';
        $html .= '&emsp;&emsp;&emsp;<img src="replace.png" class="img-desabilitada" id=\'' . $clsNu . '\' title="Substituir este item"/>';
    } else {
        $html .= '<img src="edit.webp" class="action edit" id=\'' . $clsNu . '\' title="Alterar, desativar ou excluir" />&emsp;';
        $html .= '<img src="add_below.png" class="action addrow" id=\'' . $clsNu . '\' title="Adicionar novo item abaixo deste"/>&emsp;';
        $html .= '<img src="row_up.png" class="action move" data-direcao="up" id=\'' . $clsNu . '\' title="Mover para cima"/>&emsp;';
        $html .= '<img src="row_down.png" class="action move" data-direcao="down" id=\'' . $clsNu . '\' title="Mover para baixo" />';
        $html .= '&emsp;&emsp;&emsp;<img src="replace.png" class="action replace" id=\'' . $clsNu . '\' title="Substituir este item"/>';
    }
    $html .= '</td>';
    $html .= "</tr>";
    return $html;
}
