<!DOCTYPE html>
<html>

<head>
    <title>Documento com Rodapé em Todas as Páginas</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="estilo.css" />

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
</head>

<body>

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
                        $tipo = '';
                    }

                    $con =  new Connect();
                    if (empty($con->getErrorMessage() )) {
                        $con->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        // Cabeçalho os dados
                        $stmt = $con->pdo->prepare("SELECT sec_conteudo	FROM section WHERE sec_id=1");
                        $stmt->execute();
                        $texto = $stmt->fetchColumn();
                       // echo $texto;

                        // Consulta os dados
                        $stmt = $con->pdo->prepare("SELECT cls_nu, cls_tx FROM banco_clausula ORDER BY cls_nu_ordem");
                        $stmt->execute();
                        echo '<ol>';
                        // Exibe os resultados
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $texto = $row['cls_tx'];
                            // Adicionada a verificação !empty($texto)
                            if (!empty($texto)) {
                                if ($tipo != '') {
                                    echo '<div style="border-bottom: 1px dashed red; margin: 10px 0; padding-bottom: 5px;">';
                                    echo '<strong style="color:red; display: block; text-align: center;">Cláusula ' . $row['cls_nu'] . '</strong>';
                                    echo '</div>';
                                }

                                $texto = str_replace('«', '<mark>«', $texto);
                                $texto = str_replace('»', '»</mark>', $texto);
                                echo '<li>' . $texto . '</li>';
                            }
                        }
                        echo '</ol>';
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
    <footer class="footer">Meu Rodapé da Empresa </footer>
    

</body>

</html>