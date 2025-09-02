<!DOCTYPE html>
<html>

<head>
    <title></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="estilo.css" />
    <link rel="stylesheet" type="text/css" href="../resources/js/DataTables/jQueryUI-1.11.4/jquery-ui.css" />
    <link rel="stylesheet" href="../resources/css/fontawesome/css/all.min.css" />

    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 10mm 10mm 20mm;
            /* top right botton left */
        }

        mark {
            background-color: #ffff00;
        }

        .linha {
            display: inline-block;
            width: 50px;
            /* Ajuste conforme necessário */
            margin: 0 10px;
            /* Espaço ao redor */
            border: none;
            border-top: 1px solid black;
        }

        #divSimulador {
            display: none;
        }

        /* ------------------------------------------------------------- */
        /* Estilos específicos para IMPRESSÃO */
        @media print {

            /* Remover margens e padding padrão do navegador para a página */
            @page {
                size: A4 portrait;
                margin: 0.3in 1in 0.3in 1in !important;
                /* Top, Right, Bottom, Left. Ajuste conforme a altura do cabeçalho/rodapé. */
                /* margin: 25mm 20mm 25mm 20mm; */

            }

            html,
            body {
                margin: 0;
                padding: 0;
                height: auto;
                /* Importante para permitir que o conteúdo flua */
                box-sizing: border-box;
                font-family: Calibri;
                font-size: 11pt;
            }

            footer {
                width: 100%;
                height: .4in;
                position: fixed;
                bottom: 0;
                border-top: 1px solid silver;
            }

            table.paging thead td,
            table.paging tfoot td {
                height: .5in;
            }

            .no-print {
                display: none;
            }

            /* não aplica a âncora na impressão */
            a.ancora[id] {
              all: unset;
            }
            a.ancora[id]::before {
                display: none !important;
                content: none !important;
            }
            a.linkAncora {
             text-decoration: none;
            }

            /* Classe para o número da página atual */
            footer .page-number::before {
                content: "Página " counter(page);
            }

            /* Classe para o total de páginas (suporte limitado no Chrome!) */
            .total-pages::before {
                content: " de " counter(pages);
            }
        }

        /* fim @media print */

    </style>
    <script type="text/javascript" src="../resources/js/DataTables/jQuery-2.2.4/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="../resources/js/DataTables/jQueryUI-1.11.4/jquery-ui.min.js"></script>
    

    <script>
$(document).ready(function() {
    $("#divSimulador").dialog({
        autoOpen: false,
        width: 900, // Define a largura em pixels
        height: 450 // Define a altura em pixels                                                           
    });

    $('#cmdSimular').on('click', function() {
        $("#divSimulador").dialog('open');
    });

    $('#cmdAplicar').on('click', function() {
        const dados = {};
        let texto = $('main').html(); // Captura o conteúdo HTML do <main>

        $('table tr').each(function() {
            const celulas = $(this).find('td');
            if (celulas.length === 2) {
                const chaveBruta = celulas.eq(0).text().trim();
                const match = chaveBruta.match(/«(.*?)»/);
                if (match) {
                    const chave = `«${match[1]}»`;
                    const valor = celulas.eq(1).find('textarea, input').val();
                    dados[chave] = valor;
                }
            }
        });

        // Substitui as chaves no conteúdo do <main>
        for (const chave in dados) {
            const valor = dados[chave];
            //const regex = new RegExp(chave.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
            // Regex para encontrar <mark>«chave»</mark> ou apenas «chave»
            const regex = new RegExp(`<mark>\\s*${chave.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\s*</mark>|${chave.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}`, 'g');
            texto = texto.replace(regex, valor);
        }

        // Atualiza o conteúdo do <main> com as substituições
        $('main').html(texto);

        //$("mark").css("background-color", "#00ff99"); 


        $('select[data-section]').each(function() {
            const categoria = $(this).data('section');

            const optionSelecionado = $(this).find('option:selected');
            const valor = optionSelecionado.val();

            // Oculta todas as sections da categoria
            $('section.' + categoria).hide();

            if (valor === "0") {
                // Mostra todas as sections da categoria
                $('section.' + categoria).show();
            } else {
                // Mostra apenas a section com o ID correspondente

                $('section.' + categoria + '[data-value="' + valor + '"]').show();
            }

        });

        $("#divSimulador").dialog('close');

    });

    

}); // fim JQuery                    
    </script>


</head>

<body>
    <span class="no-print">
        <button type="button" accesskey="s" id="cmdSimular"><u>S</u>imular</button>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
        <button type="button" accesskey="i" onclick="window.print();"><u>I</u>mprinir</button>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
    </span>

    <main>
        <table class="paging">
            <tbody>
                <tr>
                    <td>
                        <?php
                        require('../vendor/autoload.php');

                        use App\Database\Connect; 
                        

                        if (isset($_GET['s'])) {
                            $tipo = 'S';
                        } else {
                            $tipo =  '';
                        }

                        if (isset($_GET['edital'])) {
                            $idEdital = $_GET['edital'];
                        } else {
                            $idEdital = 0;
                        }

                        $con =  new Connect();
                        if (empty($con->getErrorMessage())) {
                            $con->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                            if ($idEdital != 0) {
                                $stmt = $con->pdo->prepare("SELECT  edt_nome || '_' || edt_versao || '.' || edt_designacao AS nome FROM edital WHERE edt_id = :idEdital");
                                $stmt->execute([':idEdital' => $idEdital]);
                                $row  = $stmt->fetch(PDO::FETCH_ASSOC);
                                $NomeEdital = $row['nome'];
                            }

                            $i = 1;

                            // Consulta os dados
                            if ($idEdital != 0) {
                                $stmt = $con->pdo->prepare("SELECT B.cls_nu, B.cls_tx FROM banco_clausula AS B INNER JOIN (SELECT cls_nu FROM edital_clausula WHERE edt_id = :idEdital) AS E ON B.cls_nu = E.cls_nu ORDER BY cls_nu_ordem");
                                $stmt->execute([':idEdital' => $idEdital]);
                            } else {
                                $stmt = $con->pdo->prepare("SELECT cls_nu, cls_tx FROM banco_clausula ORDER BY cls_nu_ordem");
                                $stmt->execute();
                            }
                            echo '<div id="clausulas"><ol>';
                            // Exibe os resultados
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                if ($tipo != '') {
                                    echo '<span style="font-weight:bold; color:red">' . str_repeat('-', 200)  . $row['cls_nu']  . str_repeat('-', 200) . "</span>";
                                }

                                $texto = $row['cls_tx'];
                                $texto = str_replace('«', '<mark>«', $texto);
                                $texto = str_replace('»', '»</mark>', $texto);
                                echo $texto;
                            }
                            echo '</ol></div>';
                        } else {
                            echo "Erro: " . $con->getErrorMessage();
                        }
                        ?>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>&nbsp;</td>
                </tr>
            </tfoot>
        </table>
    </main>
    <?php
    if ($idEdital == 0)
        echo '<footer class="footer">Edital modelo</footer>';
    else
        echo '<footer class="footer">' . $NomeEdital  . '</footer>';
    ?>

    <div id="divSimulador" title="Simulador">
        <table>
            <tr>
                <th>Condição</th>
                <th>Valor</th>
            </tr>
            <tr>
                <td>«lote»</td>
                <td><textarea cols="50" rows="4">Lorem ipsum</textarea> </td>
            </tr>
            <tr>
                <td>«pauta»</td>
                <td><textarea cols="50" rows="10">&lt;table border="1"&gt;
  &lt;tr&gt;
    &lt;th&gt;Company&lt;/th&gt;
    &lt;th&gt;Contact&lt;/th&gt;
    &lt;th&gt;Country&lt;/th&gt;
  &lt;/tr&gt;
  &lt;tr&gt;
    &lt;td&gt;Alfreds Futterkiste&lt;/td&gt;
    &lt;td&gt;Maria Anders&lt;/td&gt;
    &lt;td&gt;Germany&lt;/td&gt;
  &lt;/tr&gt;
  &lt;tr&gt;
    &lt;td&gt;Centro comercial Moctezuma&lt;/td&gt;
    &lt;td&gt;Francisco Chang&lt;/td&gt;
    &lt;td&gt;Mexico&lt;/td&gt;
  &lt;/tr&gt;
&lt;/table&gt;</textarea></td>
            </tr>
            <tr>
                <td>lances</td>
                <td><select data-section="lances">
                        <option value="0">---Selecione---</option>
                        <option value="aberto">Aberto</option>
                        <option value="ambos">Ambos</option>
                    </select></td>
            </tr>
            <tr>
                <td>quantitivo mínimo</td>
                <td><select data-section="quantitativo_minimo">
                        <option value="0">---Selecione---</option>
                        <option value="1">Não será exigido</option>
                        <option value="2">Percentual mínimo</option>
                    </select></td>
            </tr>
            <tr>
                <td>local de entrega de exemplares</td>
                <td><select data-section="local_entrega_exemplares">
                        <option value="0">---Selecione---</option>
                        <option value="1">Não será exigido</option>
                        <option value="2">Na fase de contratação</option>
                        <option value="3">Na convocação</option>
                    </select></td>
            </tr>

            local_entrega_exemplares

        </table>
        <button type="button" id="cmdAplicar">Aplicar</button>
    </div>

</body>