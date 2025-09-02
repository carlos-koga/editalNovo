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




</head>

<body>
    <span class="no-print">
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


                        $tipo =  '';
                        $carrinho = $_GET['carrinho'];


                        $con =  new Connect();
                        if (empty($con->getErrorMessage())) {
                            $con->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $sql = "SELECT json_data FROM carrinho_json WHERE crr_nu_carrinho = :carrinho";
                            $stmt = $con->pdo->prepare($sql);
                            $stmt->execute([':carrinho' => $carrinho]);
                            $row  = $stmt->fetchColumn();

                            $json_data = json_decode($row, true);

                            if ($json_data === null) {
                                die("Erro ao decodificar JSON");
                            }
                            // Construindo o filtro dinâmico para to_tsquery
                            $filtros = [
                                $json_data["modalidade_edital"]["modalidade"],
                                $json_data["modalidade_edital"]["forma_contratacao"],
                                $json_data["modalidade_edital"]["grupo"]
                            ];

                            // Montando a string to_tsquery separando os termos com '&'
                            $tsquery = implode(" & ", array_map(fn($val) => "'" . $val . "'", $filtros));

                            // Query SQL dinâmica
                            $sql = "SELECT edt_id 
                                    FROM edital 
                                    WHERE to_tsvector(
                                            'portuguese', 
                                            concat_ws(' ', edt_aplicacao->>'modalidade', edt_aplicacao->>'forma_contratacao', edt_aplicacao->>'grupos')
                                        ) @@ to_tsquery('portuguese',  :tsquery)
                                    AND edt_ativo = true;";

                            $stmt = $con->pdo->prepare($sql);
                            $stmt->execute([':tsquery' => $tsquery]);
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $idEdital = $row['edt_id'] ?? null; // Evita erro caso edt_id não exista
                            var_dump($idEdital);


                            if (is_null($idEdital)) {
                                echo 'Edital de combinação de modalidade=' .  $json_data["modalidade_edital"]["modalidade"]  . ', forma de contratação:' . $json_data["modalidade_edital"]["forma_contratacao"] . ' e grupo:' . $json_data["modalidade_edital"]["grupo"]
                             . ' não encontrado!';
                                die();
                            }                             



                            $sql = "SELECT  edt_nome || '_' || edt_versao || '.' || edt_designacao AS nome,
                                        edt_doc_aprovacao 
                                        FROM edital WHERE edt_id = :idEdital";
                            $stmt = $con->pdo->prepare($sql);
                            $stmt->execute([':idEdital' => $idEdital]);
                            $row  = $stmt->fetch(PDO::FETCH_ASSOC);
                            $NomeEdital = $row['nome'];
                            $docAprovacao = $row['edt_doc_aprovacao'];



                            $stmt = $con->pdo->prepare("SELECT sec_id	FROM edital_secao  WHERE edt_id= :idEdital ORDER BY sec_ordem");
                            $stmt->execute([':idEdital' => $idEdital]);
                            $rows  = $stmt->fetchAll(PDO::FETCH_COLUMN);

                            foreach ($rows as $idSecao) {
                                $stmt = $con->pdo->prepare("SELECT sec_conteudo	FROM section WHERE sec_id= :idSecao ");
                                $stmt->execute([':idSecao' => $idSecao]);
                                $edital_content = $stmt->fetchColumn();
                                // O TinyMCE grava caracteres como HTML Entity
                                // Substituição de HTML Entities
                                $edital_content = str_replace(["&laquo;", "&raquo;", "&brvbar;"], ["«", "»", "¦"], $edital_content);

                                // Substituição dinâmica das tags «»
                                $edital_montado = preg_replace_callback(
                                    "/«([^«»]+)»/",
                                    function ($x) use ($json_data) {
                                        // Se a chave existir diretamente em $json_data, substitui
                                        if (isset($json_data[$x[1]])) {
                                            return $json_data[$x[1]];
                                        }

                                        // Se a chave existir dentro de "modalidade_edital", substitui
                                        if (isset($json_data["modalidade_edital"][$x[1]])) {
                                            return $json_data["modalidade_edital"][$x[1]];
                                        }

                                        // Caso contrário, mantém o valor original
                                        return $x[0];
                                    },
                                    $edital_content
                                );



                                // faz a susbtituição e avaliação de função definida pelo usuário
                                $edital = preg_replace_callback(
                                    "/¦([^¦]+)¦/",
                                    function ($x) {
                                        $func = 'return ' . $x[1] . ';';
                                        return eval($func);
                                    },
                                    $edital_montado
                                );

                                $edital = str_replace('«', '<mark>«', $edital);
                                $edital = str_replace('»', '»</mark>', $edital);
                                $edital = str_replace('&laquo;', '<mark>«', $edital);
                                $edital = str_replace('&raquo;', '»</mark>', $edital);

                                echo $edital;
                            }



                            // Mostra as cláusulas
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

                                $edital_content = $row['cls_tx'];
                                $edital_content = str_replace(["&laquo;", "&raquo;", "&brvbar;"], ["«", "»", "¦"], $edital_content);

                                // Substituição dinâmica das tags «»
                                $edital_montado = preg_replace_callback(
                                    "/«([^«»]+)»/",
                                    function ($x) use ($json_data) {
                                        // Se a chave existir diretamente em $json_data, substitui
                                        if (isset($json_data[$x[1]])) {
                                            return $json_data[$x[1]];
                                        }

                                        // Se a chave existir dentro de "modalidade_edital", substitui
                                        if (isset($json_data["modalidade_edital"][$x[1]])) {
                                            return $json_data["modalidade_edital"][$x[1]];
                                        }

                                        // Caso contrário, mantém o valor original
                                        return $x[0];
                                    },
                                    $edital_content
                                );



                                // Trata as cláusulas condicinais nas <section>
                                $valoresPermitidos = $json_data["sections"] ?? [];
                                // Adiciona `style="display:none"` em todas as sections
                                $edital_montado = preg_replace_callback(
                                    '/<section class="([^"]+)" data-value="([^"]+)">(.*?)<\/section>/s',
                                    function ($matches) use ($valoresPermitidos) {
                                        $sectionClass = trim($matches[1]); // Nome da classe
                                        $sectionValue = trim($matches[2]); // Valor do data-value (agora tratado como string)

                                        // Verifica se a seção existe no JSONB e se o valor é compatível
                                        if (isset($valoresPermitidos[$sectionClass]) && (string) $valoresPermitidos[$sectionClass] === $sectionValue) {
                                            //return '<section class="' . $sectionClass . '" data-value="' . $sectionValue . '" style="display:block;">' . $matches[3] . '</section>';
                                            return $matches[3];
                                        }

                                        //return '<section class="' . $sectionClass . '" data-value="' . $sectionValue . '" style="display:none;">' . $matches[3] . '</section>';
                                    },
                                    $edital_montado
                                );

                                $edital_montado = str_replace('«', '<mark>«', $edital_montado);
                                $edital_montado = str_replace('»', '»</mark>', $edital_montado);
                                $edital_montado = str_replace('&laquo;', '<mark>«', $edital_montado);
                                $edital_montado = str_replace('&raquo;', '»</mark>', $edital_montado);


                                echo $edital_montado;
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


    <script>
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
        });
    </script>
</body>

</html>


</body>