<?php
    require('../vendor/autoload.php');

    use App\Database\Connect;

    if (isset($_POST['lista2'])) {
        $lista2 = $_POST['lista2'];
    
        // Certifique-se de que é um array
        if (is_array($lista2) && !empty($lista2)) {
            $placeholders = implode(',', array_fill(0, count($lista2), '?'));
        }        
            
                       

    $con = new Connect();

    // Array para armazenar os dados completos que serão passados para o JavaScript
    $jsClauseData = [];

    if (empty($con->getErrorMessage())) {

        // Buscar nomes distintos
        $sqlNomes = "SELECT DISTINCT 
        E.edt_nome || '_' || E.edt_versao || '.' || E.edt_designacao AS nome
    FROM edital_clausula AS C
    RIGHT JOIN edital AS E ON C.edt_id = E.edt_id 
    WHERE E.edt_id IN ($placeholders)
    ORDER BY 1";

        $stmt = $con->pdo->prepare($sqlNomes);
        $stmt->execute($lista2);
        $nomes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Construção da consulta principal
        $colunas = [];
        foreach ($nomes as $nome) {
            $alias = str_replace('"', '""', $nome);
            $colunas[] = "MAX(CASE WHEN A.nome = " . $con->pdo->quote($nome) . " THEN 'X' ELSE '' END) AS \"$alias\"";
        }
        $colunas_sql = implode(",\n", $colunas);

        // Consulta final
        $sql = "
        SELECT 
            B.cls_nu,
            LEFT(REGEXP_REPLACE(
                REGEXP_REPLACE(B.cls_tx, E'\r?\n', '', ''),
                '<[^>]+>',
                '',
                'g'
            ),70) AS resumo,          
            B.cls_tx AS completo,          
            $colunas_sql
        FROM banco_clausula AS B
        LEFT JOIN (
            SELECT 
                C.cls_nu,
                E.edt_nome || '_' || edt_versao || '.' || E.edt_designacao AS nome
            FROM edital_clausula AS C
            INNER JOIN edital AS E 
            ON C.edt_id = E.edt_id
            WHERE E.edt_id IN ($placeholders)
        ) AS A ON A.cls_nu = B.cls_nu        
        GROUP BY B.cls_nu, B.cls_tx
        ORDER BY B.cls_nu";

        $stmt = $con->pdo->prepare($sql);
        $stmt->execute($lista2); 
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Botões de controle


        // Exibir tabela HTML usando <div>
        if (!empty($resultados)) {
            echo '<ol><div class="tabela">';

            echo '<div class="linha cabecalho">';
            echo '<div class="coluna cabecalho" style="width:80px; flex:none">ID</div>';
            echo '<div class="coluna texto cabecalho" style="width:450px">Cláusula</div>';
            foreach ($nomes as $nome) {
                echo "<div class='coluna vertical'>" . htmlspecialchars($nome) . "</div>";
            }
            echo "</div>";

            foreach ($resultados as $linha) {
                echo '<div class="linha">';
                echo "<div class='coluna' style='width:80px;flex:none'>" . htmlspecialchars($linha['cls_nu']) . "</div>";

                echo "<div class='coluna toggle-content' style='width:450px'
                    data-short='" . htmlspecialchars($linha['resumo']) . "' 
                    data-full='" . htmlspecialchars($linha['completo']) . "' 
                    data-plain='" . strip_tags($linha['completo']) . "'>
                    " . htmlspecialchars($linha['resumo']) . "
                  </div>";

                foreach ($nomes as $nome) {
                    echo "<div class='coluna center'>" . (trim($linha[$nome]) === 'X' ? 'X' : '') . "</div>";
                }
                echo "</div>";
            }

            echo '</div></ol>';
        } else {
            echo "Nenhum dado encontrado.";
        }
    } else {
        echo $con->getErrorMessage();
    }

    unset($con);
}
    ?>