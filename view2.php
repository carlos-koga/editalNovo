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

        footer {
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
                display: flex;
                justify-content: space-between;
                align-items: center;
                /* Opcional: para centralizar verticalmente */
            }



            .footer .left {
                text-align: left;
            }

            .footer .right {
                text-align: right;
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

    <!-- <img src="data:image/jpg;base64,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" width="158" height="50" style="display: block; margin: 0 auto;" /> -->
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
                                $sql = "SELECT  edt_nome || '_' || edt_versao || '.' || edt_designacao AS nome,
                                        edt_doc_aprovacao 
                                        FROM edital WHERE edt_id = :idEdital";
                                $stmt = $con->pdo->prepare($sql);
                                $stmt->execute([':idEdital' => $idEdital]);
                                $row  = $stmt->fetch(PDO::FETCH_ASSOC);
                                $NomeEdital = $row['nome'];
                                $docAprovacao = $row['edt_doc_aprovacao'];
                            }

                            $i = 1;

                            $stmt = $con->pdo->prepare("SELECT sec_id	FROM edital_secao  WHERE edt_id= :idEdital ORDER BY sec_ordem");
                            $stmt->execute([':idEdital' => $idEdital]);
                            $rows  = $stmt->fetchAll(PDO::FETCH_COLUMN);

                            foreach ($rows as $idSecao) {
                                $stmt = $con->pdo->prepare("SELECT sec_conteudo	FROM section WHERE sec_id= :idSecao ");
                                $stmt->execute([':idSecao' => $idSecao]);
                                $texto = $stmt->fetchColumn();
                                $texto = str_replace('«', '<mark>«', $texto);
                                $texto = str_replace('»', '»</mark>', $texto);
                                $texto = str_replace('&laquo;', '<mark>«', $texto);
                                $texto = str_replace('&raquo;', '»</mark>', $texto);
                                echo $texto;
                            }



                            // Mostra as cláusulas
                            if ($idEdital != 0) {
                                $stmt = $con->pdo->prepare("SELECT B.cls_nu, B.cls_tx FROM banco_clausula AS B INNER JOIN (SELECT cls_nu FROM edital_clausula WHERE edt_id = :idEdital) AS E ON B.cls_nu = E.cls_nu ORDER BY cls_nu_ordem");
                                $stmt->execute([':idEdital' => $idEdital]);
                            } else {
                                $stmt = $con->pdo->prepare("SELECT cls_nu, cls_tx FROM banco_clausula ORDER BY cls_nu_ordem");
                                $stmt->execute();
                            }
                            echo '<div id="clausulas"><ol class="itensClausula">';
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
                            echo $con->getErrorMessage();
                        }

                        unset($con);
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
    else {
        echo '<footer class="footer">
                  <div class="left">' . $NomeEdital . '</div>
                  <div class="right">' . $docAprovacao . '</div>
              </footer>';
    }
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

        </table>
        <button type="button" id="cmdAplicar">Aplicar</button>
    </div>
    <script>
        function calcularNumeracao(liId) {     
            let liElement = document.querySelector(`li${liId}`);
            let lista = document.querySelectorAll("#clausulas ol.itensClausula > li");            

            let level1 = 0,
                level2 = 0,
                level3 = 0,
                level4 = 0,
                level5 = 0;
            let numeracoes = {};

            lista.forEach(li => {
                let level = li.getAttribute("data-level");
                if (level === "1") {
                    level1++;
                    level2 = 0;
                    level3 = 0;
                    level4 = 0;
                    level5 = 0;
                    if (li.id) numeracoes[li.id] = `${level1}.`;
                } else if (level === "2") {
                    level2++;
                    level3 = 0;
                    level4 = 0;
                    level5 = 0;
                    if (li.id) numeracoes[li.id] = `${level1}.${level2}.`;
                } else if (level === "3") {
                    level3++;
                    level4 = 0;
                    level5 = 0;
                    if (li.id) numeracoes[li.id] = `${level1}.${level2}.${level3}.`;
                } else if (level === "4") {
                    level4++;
                    level5 = 0;
                    if (li.id) numeracoes[li.id] = `${level1}.${level2}.${level3}.${level4}.`;
                } else if (level === "5") {
                    level5++;
                    if (li.id) numeracoes[li.id] = `${level1}.${level2}.${level3}.${level4}.${level5}.`;
                }
            });
            
 console.log(numeracoes[liElement.id]);
            return numeracoes[liElement.id] || "";
        }

        document.addEventListener("DOMContentLoaded", async function() {
            // Seleciona todos os <li> e configura as variáveis para numeração
            const listaItens = document.querySelectorAll("#divAnexos ol.roman > li");
            const numerosRomanos = ["I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII", "XII", "XIV", "XV", "XVI", "XVII", "XVIII", "XIX", "XX"];
            let contadorPrincipal = 1; // Para itens de nível 1.
            let ultimaNumeroPrincipal = ""; // Armazena o numeral do último item principal.
            let subContador = {}; // Contador para subníveis, agrupados pelo item principal.
            let dadosArray = []; // Array para guardar os objetos com os dados de cada <li>

            // Itera os <li> para montar o array de dados
            for (const li of listaItens) {
                const level = parseInt(li.getAttribute("data-level"), 10);
                const anexoId = li.getAttribute("data-anexo-id");
                const texto = li.textContent.trim();
                let numeroGerado = "";

                if (level === 1) {
                    // Gera o número principal (I, II, III, ...)
                    let numeroBase = numerosRomanos[contadorPrincipal - 1] || contadorPrincipal;
                    ultimaNumeroPrincipal = numeroBase;
                    // Reinicia o contador de subníveis para esse item principal.
                    subContador[ultimaNumeroPrincipal] = 0;
                    numeroGerado = `${numeroBase}.`;
                    contadorPrincipal++;
                } else {
                    // Para subníveis, usamos o último número principal.
                    if (!ultimaNumeroPrincipal) {
                        // Caso não haja item principal anterior, assume o primeiro numeral.
                        ultimaNumeroPrincipal = numerosRomanos[0];
                        subContador[ultimaNumeroPrincipal] = 0;
                    }
                    subContador[ultimaNumeroPrincipal] += 1;
                    // Converte o contador num subnível em uma letra (1 -> A, 2 -> B, ...)
                    const letraSubnivel = String.fromCharCode(64 + subContador[ultimaNumeroPrincipal]);
                    numeroGerado = `${ultimaNumeroPrincipal}-${letraSubnivel})`;
                }

                // Exibe o resultado no console
                //console.log(`Número: ${numeroGerado} | data-anexo-id: ${anexoId} | Texto: ${texto}`);

                // Adiciona os dados ao array
                dadosArray.push({
                    anexoId: anexoId,
                    numero: numeroGerado
                });
            }

            // Seleciona o primeiro <td> do primeiro <tr> (destino dos retornos)
            const firstTd = document.querySelector("tr:first-of-type td:first-of-type");

            // Itera sobre cada objeto do array e envia as requisições de forma sequencial
            for (const dados of dadosArray) {
                // Prepara os dados em formato URL-encoded
                const formData = new URLSearchParams();
                for (const key in dados) {
                    if (Object.prototype.hasOwnProperty.call(dados, key)) {
                        formData.append(key, dados[key]);
                    }
                }

                try {
                    const response = await fetch('anexo_get_content_ajax.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error('Erro na requisição: ' + response.status);
                    }

                    const html = await response.text();
                    if (firstTd) {
                        firstTd.innerHTML += html;
                    }
                } catch (error) {
                    console.error('Erro:', error);
                }
            }

            // subtitui linkAncora pela numeração do item
            document.querySelectorAll('a.linkAncora').forEach(link => {
                //const targetId = link.getAttribute('href')?.replace('#', '');
                const targetId = link.getAttribute('href');
                console.log(targetId);
                //const liElement = document.getElementById(targetId);
                //console.log(liElement);

                
                    const numeracao = calcularNumeracao(targetId);
                    console.log(numeracao);
                    if (numeracao) {
                        link.textContent = numeracao;
                    }
                
            });

        });
    </script>
</body>

</html>


</body>