<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>


    <link rel="stylesheet" type="text/css" href="../resources/css/sistema.css" />
    <link rel="stylesheet" type="text/css" href="../resources/css/form.css" />
    <link rel="stylesheet" type="text/css" href="../resources/js/DataTables/jQueryUI-1.11.4/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" href="../resources/js/toastmessage/jquery.toastmessage.css" />
    <link rel="stylesheet" type="text/css" href="../resources/js/DataTables/Buttons-1.3.1/css/buttons.dataTables.min.css" />
    <link rel="stylesheet" type="text/css" href="../resources/js/DataTables/DataTables-1.10.15/css/dataTables.jqueryui.min.css" />

    <script type="text/javascript" src="../resources/js/DataTables/jQuery-2.2.4/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="../resources/js/DataTables/jQueryUI-1.11.4/jquery-ui.min.js"></script>
    <script type="text/javascript" src="../resources/js/toastmessage/jquery.toastmessage.js"></script>

    <script type="text/javascript" src="../resources/js/DataTables/DataTables-1.10.15/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../resources/js/DataTables/DataTables-1.10.15/js/dataTables.jqueryui.min.js"></script>
    <script>
        $(document).ready(function() {
            var tableDados;
            var LINHA_ATUAL;

            var buttonCommon = {
                exportOptions: {
                    format: {
                        body: function(data, row, column, node) {
                            // Strip $ from salary column to make it numeric
                            return $.isNumeric(data.replace(/\./g, "").replace(/,/, ".")) ? data.replace(/\./g, "").replace(/,/, ".") : data;
                        }
                    }
                }
            };


            $().toastmessage({
                sticky: false,
                inEffectDuration: 600,
                stayTime: 4000,
                position: 'middle-center'
            });


            jQuery.extend(jQuery.fn.dataTableExt.oSort, {
                "date-euro-pre": function(a) {
                    if ($.trim(a) != '') {
                        var x
                        var frTimea
                        var frDatea2
                        // adaptado para data sem hora
                        var frDatea = $.trim(a).split(' ');
                        if (frDatea[1] == undefined) {
                            frDatea2 = $.trim(a).split('/');
                            x = (frDatea2[2] + frDatea2[1] + frDatea2[0]) * 1;
                        } else {
                            frTimea = frDatea[1].split(':');
                            frDatea2 = frDatea[0].split('/');
                            x = (frDatea2[2] + frDatea2[1] + frDatea2[0] + frTimea[0] + frTimea[1] + frTimea[2]) * 1;
                        }
                    } else {
                        x = 10000000000000; // = l'an 1000 ...
                    }

                    return x;
                },

                "date-euro-asc": function(a, b) {
                    return a - b;
                },

                "date-euro-desc": function(a, b) {
                    return b - a;
                }
            });

            jQuery.extend(jQuery.fn.dataTableExt.oSort, {
                'locale-compare-asc': function(a, b) {
                    return a.localeCompare(b, 'cs', {
                        sensitivity: 'case'
                    })
                },
                'locale-compare-desc': function(a, b) {
                    return b.localeCompare(a, 'cs', {
                        sensitivity: 'case'
                    })
                }
            })

            jQuery.fn.dataTable.ext.type.search['locale-compare'] = function(data) {
                return removeAccents(data);
            }

            function removeAccents(data) {
                return data
                    .replace(/έ/g, 'ε')
                    .replace(/ό/g, 'ο')
                    .replace(/ώ/g, 'ω')
                    .replace(/ά/g, 'α')
                    .replace(/[ίϊΐ]/g, 'ι')
                    .replace(/ή/g, 'η')
                    .replace(/\n/g, ' ')
                    .replace(/[áÁâÂàÀãÃ]/g, 'a')
                    .replace(/[éÉèÈêÊ]/g, 'e')
                    .replace(/[íÍïÏîÎìÍ]/g, 'i')
                    .replace(/[óÓöÖõÕôÔ]/g, 'o')
                    .replace(/[úÚüÜûÛΰ]/g, 'u')
                    .replace(/[çÇ]/g, 'c')
                    .replace(/[ñÑ]/g, 'c')
            }


            var searchType = jQuery.fn.DataTable.ext.type.search;

            searchType.string = function(data) {
                return !data ?
                    '' :
                    typeof data === 'string' ?
                    removeAccents(data) :
                    data;
            };

            searchType.html = function(data) {
                return !data ?
                    '' :
                    typeof data === 'string' ?
                    removeAccents(data.replace(/<.*?>/g, '')) :
                    data;
            };



            $.fn.dataTable.ext.search = []; // Remove todos os filtros anteriores

            // column filter
            // Criação dos filtros no rodapé
            $('#tableDados tfoot tr th').each(function() {
                var title = $(this).text();
                var strSize = $(this).attr('size');

                // Se for filtro de texto
                if ($(this).hasClass('columnFilter') && title !== '') {
                    $(this).html('<input type="text" class="columnFilter" size="' + strSize + '" placeholder="' + title + '" />');
                }
            });



            tableDados = $('#tableDados').DataTable({
                responsive: true,
                "dom": 'lfrtBip',
                "language": {
                    url: '../resources/js/DataTable/pt-BR.json'
                },
                buttons: [
                    $.extend(true, {}, buttonCommon, {
                        extend: 'csv',
                        text: '<i class="fa fa-file-csv"></i> CSV'
                    }),
                    $.extend(true, {}, buttonCommon, {
                        extend: 'excelHtml5',
                        text: '<i class="far fa-file-excel"></i> Excel'
                    }),
                    $.extend(true, {}, buttonCommon, {
                        extend: 'pdf',
                        text: '<i class="fa-solid fa-file-pdf"></i> PDF'
                    }),

                    {
                        extend: 'print',
                        text: '<i class="fa fa-print"></i> Imprimir',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    } // fim extend print   
                ],
                "columnDefs": [{
                        "targets": 'nosort',
                        "orderable": false
                    }, {
                        "targets": 'DDMMYYYY',
                        "sType": "date-euro"
                    },
                    {
                        targets: "integer",
                        type: 'num',
                        className: 'dt-body-center'
                    },
                    {
                        targets: "currency",
                        type: 'currency-br',
                        className: 'dt-body-right'
                    },
                    {
                        targets: "_all",
                        type: 'locale-compare'
                    },
                    {
                        targets: 'editable-coluna',
                        createdCell: function(td, cellData, rowData, row, col) {
                            $(td).attr('contenteditable', true);
                            $(td).addClass('editable');
                        }
                    }
                ],
                order: [
                    [1, 'asc'],
                ],
                "aLengthMenu": [
                    [10, 20, 30, 40, 50, 100, 200, -1],
                    [10, 20, 30, 40, 50, 100, 200, "Todos"]
                ]
            }); // fim Datatable



            // Personalizar caixa de pesquisa para ignorar acento
            $("#custom-filter").on('keyup change', function() {
                tableDados
                    .search(
                        $.fn.DataTable.ext.type.search.string(this.value)
                    )
                    .draw();
            });

            // Column filter Apply the search
            $("#tableDados tfoot input").on('keyup change', function() {
                tableDados
                    .column($(this).parent().index() + ':visible')
                    .search(
                        $.fn.DataTable.ext.type.search.string(this.value)
                    )
                    .draw();
            });

            $('#tableDados tbody tr td').on('click', '.view', function() {                
                const row = $(this).closest('tr'); // encontra a <tr> da célula clicada
                const carrinho = row.find('td').eq(1).text().trim(); // pega a 2ª coluna (index 1)
                window.open('edital_simulado_view.php?carrinho=' + carrinho, "_simulado" );
            });

            $('#tableDados').on('blur', 'td.editable', function() {
                const newValue = $(this).html().trim();
                const cell = $('#tableDados').DataTable().cell(this);
                const cellIndex = cell.index();
                const rowData = cell.row(cellIndex.row).data();

                // Pega o <th> correspondente a essa coluna e extrai o atributo data-campo
                const campo = $('#tableDados thead th').eq(cellIndex.column).data('campo');

                if (!campo) {
                    console.warn('Campo JSON não definido para essa coluna.');
                    return;
                }

                $.ajax({
                    url: 'update_json_ajax.php',
                    type: 'POST',
                    data: {
                        id: rowData[1], // o carrinho
                        campo: campo, // Ex: {modalidade_edital,modalidade}
                        valor: newValue
                    },
                    success: function(response) {
                        console.log('Atualizado com sucesso:', response);
                    },
                    error: function() {
                        alert('Erro ao atualizar!');
                    }
                });
            });



        }); // fim JQuery
    </script>

</head>

<body>
    <?php
    require('../vendor/autoload.php');

    use App\Database\Connect; // SQL Server

    $con =  new Connect();
    if (empty($con->getErrorMessage())) {
        $sql = "SELECT 
    cj.crr_nu_carrinho,
    cj.json_data->>'arte' AS arte,
    cj.json_data->>'lote' AS lote,
    cj.json_data->>'pauta' AS pauta,
    cj.json_data->>'objeto' AS objeto,
    cj.json_data->>'carrinho' AS carrinho,
    cj.json_data->>'ano_edital' AS ano_edital,
    cj.json_data->>'tipo_objeto' AS tipo_objeto,
    cj.json_data->>'numero_edital' AS numero_edital,
    cj.json_data->>'modalidade_ano' AS modalidade_ano,
    cj.json_data->>'objeto_sucinto' AS objeto_sucinto,
    cj.json_data->>'email_licitacao' AS email_licitacao,
    cj.json_data->>'data_hora_inicio' AS data_hora_inicio,
    cj.json_data->>'regra_tributaria' AS regra_tributaria,
    cj.json_data->>'data_hora_disputa' AS data_hora_disputa,
    cj.json_data->'modalidade_edital'->>'modalidade' AS modalidade,
    cj.json_data->'modalidade_edital'->>'modalidade_tipo' AS modalidade_tipo,
    cj.json_data->'modalidade_edital'->>'forma_contratacao' AS forma_contratacao,
    cj.json_data->'modalidade_edital'->>'grupo' AS grupo,
    g.grp_no AS nome_grupo,
    cj.json_data->>'data_hora_abertura' AS data_hora_abertura,
    cj.json_data->>'endereco_licitacao' AS endereco_licitacao,
    cj.json_data->>'telefone_licitacao' AS telefone_licitacao,
    cj.json_data->>'criterio_julgamento' AS criterio_julgamento
  FROM
    carrinho_json cj
  LEFT JOIN grupo g ON (cj.json_data->'modalidade_edital'->>'grupo')::int = g.grp_nu;";

        $stmt = $con->pdo->prepare($sql);
        $stmt->execute();

        echo '<table id="tableDados" class="stripe" >';
        echo '<thead>';
        echo '<tr>';
        echo '    <th style="width:30px" class="nosort"></th>';
        echo '    <th>Carrinho</th>';
        echo '    <th class="editable-coluna" data-campo="{modalidade_edital,modalidade}">Mod.</th>';
        echo '    <th>Modalidade</th>';
        echo '    <th class="editable-coluna" data-campo="{modalidade_edital,forma_contratacao}">Forma<br>contratação</th>';
        echo '    <th class="editable-coluna" data-campo="{modalidade_edital,grupo}">Nº Grupo</th>';
        echo '    <th>Grupo</th>';
        echo '    <th class="editable-coluna" >Tipo objeto</th>';
        echo '    <th class="editable-coluna" data-campo="{objeto}">Objeto</th>';
        echo '    <th class="editable-coluna" data-campo="{objeto_sucinto}">Objeto<br>sucinto</th>';        
        echo '    <th class="editable-coluna" data-campo="{numero_edital}">Nº edital</th>';
        echo '    <th class="editable-coluna" data-campo="{ano_edital}">Ano<br>edital</th>';      
        
        echo '    <th class="editable-coluna" data-campo="{endereco_licitacao}">Local CPL</th>';      
        echo '    <th class="editable-coluna" data-campo="{telefone_licitacao}">Fone CPL</th>';      
        echo '    <th class="editable-coluna" data-campo="{email_licitacao}">Email CPL</th>';      
        echo '    <th class="editable-coluna" data-campo="{lote}">Lote</th>';      
        echo '    <th class="editable-coluna" data-campo="{pauta}">Pauta</th>';      
          
        echo '</tr>';
        echo '</thead>';
        /* echo '<tfoot>';
  echo '<tr>';
  echo '    <th></th>';
  echo '    <th class="columnFilter" size="5">Âncora</th>';
  echo '    <th class="columnFilter" size="70">Cláusula</th>';
  echo '</tr>';
  echo '</tfoot>'; */
        echo '<tbody>';

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr>';
            echo '<td><img src="eye.png" title="Visualizar edital" class="view classPointer" /></td>';
            echo '<td>' . $row['crr_nu_carrinho'] . '</td>';
            echo '<td>' . $row['modalidade'] . '</td>';
            echo '<td>' . $row['modalidade_tipo'] . '</td>';
            echo '<td>' . $row['forma_contratacao'] . '</td>';
            echo '<td>' . $row['grupo'] . '</td>';
            echo '<td>' . $row['nome_grupo'] . '</td>';
            echo '<td>' . $row['tipo_objeto'] . '</td>';
            echo '<td>' . $row['objeto'] . '</td>';
            echo '<td>' . $row['objeto_sucinto'] . '</td>';            
            echo '<td>' . $row['numero_edital'] . '</td>';
            echo '<td>' . $row['ano_edital'] . '</td>';            
            echo '<td>' . $row['endereco_licitacao'] . '</td>';      
            echo '<td>' . $row['telefone_licitacao'] . '</td>';      
            echo '<td>' . $row['email_licitacao'] . '</td>';      
            echo '<td>' . $row['lote'] . '</td>';      
            echo '<td>' . $row['pauta'] . '</td>';      
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo $con->getErrorMessage();
    }

    unset($con);
    ?>
</body>

</html>