<link rel="stylesheet" type="text/css" href="../resources/css/sistema.css" />
<link rel="stylesheet" type="text/css" href="../resources/js/toastmessage/jquery.toastmessage.css" />
<link rel="stylesheet" href="../resources/css/fontawesome/css/all.min.css" />
<style>
    select {
        width: 300px;
        height: 220px;
    }

    select option {
        padding: 3px;
    }

    
</style>
<script type="text/javascript" src="../resources/js/DataTables/jQuery-2.2.4/jquery-2.2.4.min.js"></script>
<script type="text/javascript" src="../resources/js/toastmessage/jquery.toastmessage.js"></script>
<script>
    function moverItem(origemId, destinoId) {
        let origem = document.getElementById(origemId);
        let destino = document.getElementById(destinoId);

        Array.from(origem.selectedOptions).forEach(option => {
            destino.appendChild(option);
        });
    }

    function moverTodos(origemId, destinoId) {
        let origem = document.getElementById(origemId);
        let destino = document.getElementById(destinoId);

        Array.from(origem.options).forEach(option => {
            if (option.style.display !== "none") { // Apenas itens visíveis
                destino.appendChild(option);
            }
        });
    }

    // Função para mover o item selecionado para cima na lista
    function moverItemCima(selectId) {
        var select = document.getElementById(selectId);
        // Percorre as opções e se encontrar uma opção selecionada (não sendo a primeira), troca de posição com a anterior
        for (var i = 0; i < select.options.length; i++) {
            if (select.options[i].selected && i > 0) {
                var option = select.options[i];
                var previousOption = select.options[i - 1];
                // Insere a opção antes da opção anterior
                select.insertBefore(option, previousOption);
                break;
            }
        }
    }

    // Função para mover o item selecionado para baixo na lista
    function moverItemBaixo(selectId) {
        var select = document.getElementById(selectId);
        // Itera de trás para frente para evitar problemas ao reordenar
        for (var i = select.options.length - 1; i >= 0; i--) {
            if (select.options[i].selected && i < select.options.length - 1) {
                var option = select.options[i];
                var nextOption = select.options[i + 1];
                // Para mover para baixo, inserimos a próxima opção antes da atual
                select.insertBefore(nextOption, option);
                break;
            }
        }
    }
</script>
<script>
    $(document).ready(function() {

        $().toastmessage({
                sticky: false,
                inEffectDuration: 600,
                stayTime: 4000,
                position: 'middle-center'
        });


        $('#cmdGravar').click(function() {
            let lista2Valores = [];
            let editalId  = $('#txtEditalId').val();


            // Pega todos os valores das options dentro de lista2
            $('#lista2 option').each(function() {
                lista2Valores.push($(this).val());
            });
            $.ajax({
                method: "POST",
                url: 'associar_secoes_save_ajax.php',
                data: {
                    lista2: lista2Valores,
                    txtEditalId: editalId 
                },
                success: function(retorno) {
                    $().toastmessage('showSuccessToast', 'Associação efetuada!');
                    window.setTimeout(function() {
                        parent.$('#divAssociarSecoes').dialog('close');
                            }, 3000);
                    

                } // fim success
            }); // fim ajax       
        })

    });
</script>
<?php
require('../vendor/autoload.php');

use App\Database\Connect;

if (isset($_GET['editalId']))
    $editalID = $_GET['editalId'];
else {
    echo 'Não foi passado nenhum edital!';
    die();
}


$con =  new Connect();
if (empty($con->getErrorMessage())) {
    $sql1 = "SELECT sec_id, sec_titulo FROM section WHERE sec_id NOT IN (SELECT sec_id FROM edital_secao WHERE edt_id = :editalId )";

    $sql2 = "SELECT S.sec_id, S.sec_titulo FROM section AS S
             INNER JOIN edital_secao AS E 
             ON S.sec_id = E.sec_id
             WHERE E.edt_id = :editalId  
             ORDER BY E.sec_ordem";
    $stmt1 = $con->pdo->prepare($sql1);
    $stmt2 = $con->pdo->prepare($sql2);
    $lista1 = $stmt1->execute([':editalId' => $editalID]);
    $lista2 = $stmt2->execute([':editalId' => $editalID]);

    echo '<table><tr>';
    echo '<th>Seções disponíveis</th>';
    echo '<th></th>';
    echo '<th>Seções aplicadas</th>';
    echo '<th></th>';
    echo '</tr>';
    echo '<tr>';
    echo '<td style="vertical-align:top">';
    echo '<select id="lista1" multiple ondblclick="moverItem(\'lista1\', \'lista2\')">';
    while ($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
        echo '<option value="' . $row['sec_id'] . '">' . $row['sec_titulo'] . '</option>';
    }
    echo '</select></td>';


    echo '<td style="vertical-align:middle; text-align:center">';
    echo '   <p><button onclick="moverItem(\'lista1\', \'lista2\')">&#8680;</button></p>';
    echo '   <p><button onclick="moverItem(\'lista2\', \'lista1\')">&#8678;</button></p>';
    echo '   <p><button onclick="moverTodos(\'lista1\', \'lista2\')">Incluir Todos &#11078;</button></p>';
    echo '   <p><button onclick="moverTodos(\'lista2\', \'lista1\')">&#11077; Retirar Todos</button></p>';
    echo '</td>';


    echo '<td style="vertical-align:top">';
    echo '<select id="lista2" multiple ondblclick="moverItem(\'lista2\', \'lista1\')">';
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        echo '<option value="' . $row['sec_id'] . '">' . $row['sec_titulo'] . '</option>';
    }
    echo '</select></td>';
    echo '<td style="vertical-align:middle; text-align:center">';
    echo '  <p><button onclick="moverItemCima(\'lista2\')">&#8679;</button></p>';
    echo '  <p><button onclick="moverItemBaixo(\'lista2\')">&#8681;</button></p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo ' <td colspan="4" style="text-align:center"><button type="button" id="cmdGravar" accesskey="g" ><i class="fa fa-save"></i> <u>G</u>ravar</button></td>';
    echo '</tr></table>';
    echo '<input type="hidden" id="txtEditalId" value="' . $editalID . '"/>';
} else {
    echo $con->getErrorMessage();
}

unset($con);
