<link rel="stylesheet" type="text/css" href="../resources/js/DataTables/jQueryUI-1.11.4/jquery-ui.css" />
<link rel="stylesheet" type="text/css" href="../resources/js/DataTables/DataTables-1.10.15/css/dataTables.jqueryui.min.css" />
<style>
    tfoot th {
        text-align: left;
    }

    table.dataTable {
        font-size: 10pt;
    }

    /* corpo inteiro */
    table.dataTable th,
    table.dataTable td {
        padding: 0;
    }
    table.dataTable tbody tr      { line-height: 0.5; }
    table.dataTable               { border-collapse: collapse; }



    /* cabeçalhos + células */
</style>
<script type="text/javascript" src="../resources/js/DataTables/jQuery-2.2.4/jquery-2.2.4.min.js"></script>

<script type="text/javascript" src="../resources/js/DataTables/DataTables-1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="../resources/js/DataTables/DataTables-1.10.15/js/dataTables.jqueryui.min.js"></script>

<script>
    $(document).ready(function() {

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

        tableDados = $('#tableOptions').DataTable({
            responsive: true,
            "dom": 'lfrtBip',
            "language": {
                url: '../resources/js/DataTable/pt-BR.json'
            },
            "columnDefs": [{
                "targets": 'nosort',
                "orderable": false
            }],
            order: [
                [1, 'asc']
            ]
        });

        $('#tableOptions tfoot tr th').each(function() {
            var title = $(this).text();
            var strSize = $(this).attr('size');

            // Se for filtro de texto
            if ($(this).hasClass('columnFilter') && title !== '') {
                $(this).html('<input type="text" class="columnFilter" size="' + strSize + '" placeholder="' + title + '" />');
            }
        });

        // Column filter Apply the search
        $("#tableOptions tfoot input").on('keyup change', function() {
            tableDados
                .column($(this).parent().index() + ':visible')
                .search(
                    $.fn.DataTable.ext.type.search.string(this.value)
                )
                .draw();
        });

        // Personalizar caixa de pesquisa para ignorar acento
        $("#custom-filter").on('keyup change', function() {
            tableDados
                .search(
                    $.fn.DataTable.ext.type.search.string(this.value)
                )
                .draw();
        });


    }); // fim JQuery
</script>
<?php
require('../vendor/autoload.php');

use App\Database\Connect; // SQL Server

$con =  new Connect();
if (empty($con->getErrorMessage())) {

    $sql = "SELECT anx_id, anx_titulo  
            FROM anexo WHERE anx_ativo = true ORDER BY anx_titulo";

    $stmt = $con->pdo->prepare($sql);
    $stmt->execute();

    echo '<table id="tableOptions" class="display compact">';
    echo '<thead>';
    echo '<tr>';
    echo '    <th style="width:30px" class="nosort"></th>';
    echo '    <th>Título</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tfoot>';
    echo '<tr>';
    echo '    <th></th>';
    echo '    <th class="columnFilter" size="70">Título</th>';
    echo '</tr>';
    echo '</tfoot>';
    echo '<tbody>';

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr>';
        echo '<td>';
        echo '<input type="radio" value="' . $row['anx_id'] . '" name="opcao" />';
        echo '</td>';
        echo '<td>' . $row['anx_titulo'] . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
} else {
    echo $con->getErrorMessage();
}

unset($con);
