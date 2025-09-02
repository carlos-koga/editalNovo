<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de editais</title>

    <link rel="stylesheet" type="text/css" href="../resources/css/sistema.css" />
    <link rel="stylesheet" type="text/css" href="../resources/css/form.css" />
    <link rel="stylesheet" type="text/css" href="../resources/js/DataTables/jQueryUI-1.11.4/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" href="../resources/js/toastmessage/jquery.toastmessage.css" />
    <link rel="stylesheet" type="text/css" href="../resources/js/DataTables/Buttons-1.3.1/css/buttons.dataTables.min.css" />
    <link rel="stylesheet" type="text/css" href="../resources/js/DataTables/DataTables-1.10.15/css/dataTables.jqueryui.min.css" />
    <link rel="stylesheet" href="../resources/css/fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="slider_mini.css" />

    <style>
        div.dataTables_filter {
            visibility: hidden;
        }

        /* PARA PESQUISA PERSONALIZADA SEM ACENTO */

        .img-desabilitada {
            filter: grayscale(100%);
            opacity: 0.6;
            /* opcional: deixa mais "apagada" */
            cursor: not-allowed;
        }

        /* CSS para forçar a aplicação da largura */
        #tableDados {
            table-layout: fixed !important;
            font-size: 8pt;
        }


        table.dataTable tbody td {
            padding: 6px 6px;
            /* valores menores que os 10px 18px do default */
        }

        table.dataTable tfoot th {
            padding: 0;
            padding-left: 0.5em;
            text-align: left;
        }

        #tableDados td {
            word-wrap: break-word;
        }

        #tableAssociacoes {
            border-collapse: collapse;
        }

        #tableAssociacoes td {
            vertical-align: top;
            padding-right: 20px;
        }

        select.datalist {
            width: 300px;
            height: 220px;
        }

        select.datalist option {
            padding: 3px;
        }

        #tabs-1,
        #tabs-2,
        #tabs-3 {
            height: 240px;
        }

        #jsonTable {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        #jsonTable,
        #jsonTable th,
        #jsonTable td {
            border: 1px solid #ccc;
        }

        #jsonTable th,
        #jsonTable td {
            padding: 2px;
            text-align: left;
        }

        #jsonTable th {
            background-color: #f2f2f2;
        }
        .classGrupos { margin-left: 0;   /* remove a margem padrão da esquerda */
  padding-left: 1em;}
    </style>

    <script type="text/javascript" src="../resources/js/DataTables/jQuery-2.2.4/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="../resources/js/DataTables/jQueryUI-1.11.4/jquery-ui.min.js"></script>
    <script type="text/javascript" src="../resources/js/toastmessage/jquery.toastmessage.js"></script>

    <script type="text/javascript" src="../resources/js/DataTable/datatables.min.js"></script>
    <script type="text/javascript" src="../resources/js/DataTable/ColReorder-1.7.0/js/dataTables.colReorder.min.js"></script>
    <script type="text/javascript" src="../resources/js/mask/jquery.mask.min.js"></script>
    <script type="text/javascript" src="../resources/js/common.js"></script>


    <script>
        function moverItemGrupo(origemId, destinoId) {
            let origem = document.getElementById(origemId);
            let destino = document.getElementById(destinoId);

            Array.from(origem.selectedOptions).forEach(option => {
                destino.appendChild(option);
            });
        }

        function moverTodosGrupo(origemId, destinoId) {
            let origem = document.getElementById(origemId);
            let destino = document.getElementById(destinoId);

            Array.from(origem.options).forEach(option => {
                if (option.style.display !== "none") { // Apenas itens visíveis
                    destino.appendChild(option);
                }
            });
        }
    </script>
    <script>
        $(document).ready(function() {
            var tableDados;
            var LINHA_ATUAL;

            var buttonCommon = {
                exportOptions: {
                    columns: ':not(.notexport)', // ajuste conforme suas necessidades
                    format: {
                        body: function(data, row, column, node) {
                            var $chk = $('input[type=checkbox]', node);
                            // 1) Checkbox? marca “X” se checked, senão vazio
                            if ($chk.length) {
                                return $chk.prop('checked') ? 'X' : '';
                            }
                            // 2) Se for número formatado (ex.: “1.234,56”), converte para float
                            var num = data.replace(/\./g, '').replace(/,/, '.');
                            if ($.isNumeric(num)) {
                                return num;
                            }
                            // 3) Converte <li> e <br> em newline, remove resto das tags
                            return data
                                .replace(/<\/li>\s*<li>/gi, '\n') // li → newline
                                .replace(/<br\s*\/?>/gi, '\n') // br → newline
                                .replace(/<\/?ul>/gi, '') // remove <ul>
                                .replace(/<.*?>/g, '') // remove outras tags
                                .trim();
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



            $('#divEdit').dialog({
                width: 800,
                height: 470,
                autoOpen: false,
                modal: true
            });

            $('#divAssociarSecoes').dialog({
                width: 810,
                height: 390,
                autoOpen: false,
                modal: true
            });

            $('#divDuplicado').dialog({
                width: 510,
                height: 450,
                autoOpen: false,
                modal: false
            });



            $("#txtVigencia").datepicker({

                dateFormat: 'dd/mm/yy',
                dayNames: ['Domingo', 'Segunda', 'Ter\xE7a', 'Quarta', 'Quinta', 'Sexta', 'S\xE1bado', 'Domingo'],
                dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
                dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S\xE1b', 'Dom'],
                monthNames: ['Janeiro', 'Fevereiro', 'Mar\xE7o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro',
                    'Outubro', 'Novembro', 'Dezembro'
                ],
                monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set',
                    'Out', 'Nov', 'Dez'
                ],
                nextText: 'Pr\xF3ximo',
                prevText: 'Anterior'

            });

            $("#txtVigencia").mask("99/99/9999");

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

            $('#cmdListar').focus();

            $("body").bind("keydown", function(event) {
                // track enter key
                var keycode = (event.keyCode ? event.keyCode : (event.which ? event.which : event.charCode));
                if (keycode == 13) // keycode for enter key
                    if ($('#divEdit').is(':visible'))
                        $('#cmdGravar').trigger('click');
                    else
                        $('#cmdListar').trigger('click');
                else
                    return true;

            }); // fim da funçao Body


            $('#cmdListar').click(function() {
                $.fn.dataTable.ext.search = []; // Remove todos os filtros anteriores
                $.ajax({
                    method: "POST",
                    url: $('#frmMain').attr('action'),
                    data: $('#frmMain').serialize(),
                    success: function(retorno) {
                        $('#divDatatable').html(retorno);
                        $('#tableDados tbody tr td').on('click', '.view', function() {
                            let id = $(this).closest('tr').attr('id');
                            window.open('view2.php?edital=' + id, "_edital");
                        }); // fim .view click
                        $('#tableDados tbody tr td').on('click', '.edit', function() {
                            $('#cmdDel').show();
                            $('#txtOper').val('ed');
                            $('#cmdCheck').show();
                            LINHA_ATUAL = $(this).closest('tr');
                            let editalId = $(LINHA_ATUAL).attr('id');


                            $('#txtID').val(editalId);


                            let tipo = 'disponiveis';
                            // monta o corpo da requisição em x-www-form-urlencoded
                            let body = new URLSearchParams({
                                editalId,
                                tipo
                            });

                            fetch('associar_grupos_ajax.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body // mesmo que body: body
                                })
                                .then(r => r.ok ? r.text() : Promise.reject(r.statusText)) // espera HTML
                                .then(html => {
                                    document.querySelector('#grupo1').innerHTML = html; // injeta no DOM
                                })
                                .catch(err => console.error('Falha no fetch:', err));

                            tipo = 'selecionados';
                            body = new URLSearchParams({
                                editalId,
                                tipo
                            });
                            fetch('associar_grupos_ajax.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body // mesmo que body: body
                                })
                                .then(r => r.ok ? r.text() : Promise.reject(r.statusText)) // espera HTML
                                .then(html => {
                                    document.querySelector('#grupo2').innerHTML = html; // injeta no DOM
                                })
                                .catch(err => console.error('Falha no fetch:', err));


                            $.ajax({
                                method: "POST",
                                url: $('#frmEdit').attr('action'),
                                data: 'txtID=' + editalId + '&txtOper=get',
                                success: function(retorno) {
                                    $("input[name='chkModalidade[]']").prop('checked', false);
                                    $("input[name='chkForma[]']").prop('checked', false);
                                    let json = retorno;
                                    let nomeEdital = json.edt_nome;
                                    $('#txtNome').val(json.edt_nome);
                                    $('#txtDoc').val(json.edt_doc_aprovacao);                                    
                                    $('#txtVersao').val(json.edt_versao);
                                    $('#txtDesignacao').val(json.edt_designacao);
                                    $('#txtVigencia').val(json.edt_da_vigencia);                                    
                                    if (json.edt_bloqueado)
                                        $('#chkBloqueado').prop('checked', 'checked');
                                    else
                                        $('#chkBloqueado').prop('checked', false);

                                    if (json.edt_ativo)
                                        $('#chkAtivo').prop('checked', 'checked');
                                    else
                                        $('#chkAtivo').prop('checked', false);



                                    // 1) Garante que edt_aplicacao exista e seja “parseável”.
                                    let raw = json?.edt_aplicacao; // undefined | null | string | objeto

                                    // 2) Converte para objeto ou devolve {} se não houver nada válido.
                                    let app = (() => {
                                        // Se for null/undefined → retorna objeto vazio
                                        if (raw == null) return {};

                                        // Se já é objeto → devolve direto
                                        if (typeof raw === 'object') return raw;

                                        // Se é string, tenta converter
                                        if (typeof raw === 'string') {
                                            try {
                                                return raw.trim() ? JSON.parse(raw) : {};
                                            } catch (e) {
                                                console.error('edt_aplicacao é string, mas não é JSON válido:', e);
                                                return {};
                                            }
                                        }

                                        // Qualquer outro tipo inesperado
                                        return {};
                                    })();

                                    // 3) Usa as propriedades com fallback seguro
                                    (app.modalidade ?? []).forEach(m => {
                                        $(`input[name='chkModalidade[]'][value='${m}']`).prop('checked', true);
                                    });



                                    (app.forma_contratacao ?? []).forEach(function(f) {
                                        $(`input[name='chkForma[]'][value='${f}']`).prop('checked', true);
                                    });


                                    $('#divEdit').dialog('option', 'title', 'Editar edital "' + nomeEdital + '"');
                                    $('#divEdit').dialog('open');
                                    $('#txtNome').focus();
                                }
                            });
                        }) // fim .edit click

                        $('#tableDados tbody tr td').on('click', '.link', function() {
                            let id = $(this).closest('tr').attr('id');
                            let nomeEdital = $(this).closest('tr').find('td').eq(1).text();
                            $('#divAssociarSecoes').dialog('option', 'title', 'Associar seções para "' + nomeEdital + '"');
                            $('#ifrSecoes').one('load', function() {
                                $('#divAssociarSecoes').dialog('open');
                            });

                            // Define a URL do iframe (isso aciona o carregamento)
                            $('#ifrSecoes').attr('src', 'associar_secoes_iframe.php?editalId=' + id);

                        }); // fim .link click
                        $('#tableDados tbody tr td').on('click', '.rules', function() {
                            $('#divRegrasAplicacao').dialog('open');
                        }); // fim .link click


                        // column filter
                        // Criação dos filtros no rodapé
                        $('#tableDados tfoot tr th').each(function() {
                            var title = $(this).text();
                            var strSize = $(this).attr('size');

                            // Se for filtro de texto
                            if ($(this).hasClass('columnFilter') && title !== '') {
                                $(this).html('<input type="text" class="columnFilter" size="' + strSize + '" placeholder="' + title + '" />');
                            }

                            // Se for filtro de checkbox
                            if ($(this).hasClass('checkboxFilter')) {
                                $(this).html(`
    <select class="comboFilter">
        <option value="Todos">Todos</option>
        <option value="S">Sim</option>
        <option value="N">Não</option>
    </select>
`);
                            }
                        });



                        tableDados = $('#tableDados').DataTable({
                            responsive: false,
                            autoWidth: false,
                            "dom": 'lfrtBip',
                            "language": {
                                url: '../resources/js/DataTable/pt-BR.json'
                            },
                            buttons: [
                                /*$.extend(true, {}, buttonCommon, {
                                    extend: 'copyHtml5', // ou 'copy'
                                    text: '<i class="fa-solid fa-copy"></i> Copiar'
                                }), 

                                $.extend(true, {}, buttonCommon, {
                                    extend: 'csv',
                                    text: '<i class="fa fa-file-csv"></i> CSV'
                                }), */
                                $.extend(true, {}, buttonCommon, {
                                    extend: 'excelHtml5',
                                    text: '<i class="far fa-file-excel"></i> Excel'
                                }),
                                $.extend(true, {}, buttonCommon, {
                                    extend: 'pdfHtml5',
                                    text: '<i class="fa-solid fa-file-pdf"></i> PDF',
                                    pageSize: 'A4', // ou 'LEGAL', 'LETTER'…
                                    orientation: 'landscape', // muda de retrato para paisagem
                                    customize: function(doc) {
                                        // reforça no documento pdfMake
                                        doc.pageOrientation = 'landscape';
                                        doc.pageSize = 'A4';
                                        // define a fonte padrão do documento
                                        doc.defaultStyle = {
                                            font: 'Roboto',
                                            fontSize: 9
                                        };
                                        // (opcional) cabeçalhos de tabela
                                        doc.styles.tableHeader = {
                                            font: 'Roboto',
                                            bold: true,
                                            fontSize: 10,
                                            color: '#000'
                                        };
                                    }
                                }),
                                {
                                    extend: 'print',
                                    text: '<i class="fa fa-print"></i> Imprimir',
                                    exportOptions: {
                                        columns: ':not(.notexport)', // ajuste conforme suas necessidades
                                        format: {
                                            body: function(data, row, column, node) {
                                                var $chk = $('input[type=checkbox]', node);
                                                // 1) Checkbox? marca “X” se checked, senão vazio
                                                if ($chk.length) {
                                                    return $chk.prop('checked') ? 'X' : '';
                                                }
                                                // 2) Se for número formatado (ex.: “1.234,56”), converte para float
                                                var num = data.replace(/\./g, '').replace(/,/, '.');
                                                if ($.isNumeric(num)) {
                                                    return num;
                                                }

                                                return data;

                                            }
                                        }
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
                                    targets: 'notexport',
                                    width: '95px'
                                },
                                {
                                    targets: 1,
                                    width: '27px'
                                },
                                {
                                    targets: 2,
                                    width: '270px'
                                },
                                {
                                    targets: [3, 4],
                                    width: '35px'
                                },
                                {
                                    targets: 5,
                                    width: '35px'
                                },
                                {
                                    targets: 6,
                                    width: '30px'
                                },
                                {
                                    targets: 13,
                                    width: '30px'
                                },
                                {
                                    targets: 'check',
                                    width: '30px'
                                }

                            ],
                            order: [
                                [1, 'asc'],
                                [2, 'asc'],
                                [3, 'desc'],
                                [4, 'desc']
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


                        $("#tableDados tfoot tr th").on('change', '.comboFilter', function() {
                            var colIndex = $(this).parent().index() + ':visible';
                            var value = $(this).val();
                            $.fn.dataTable.ext.search = []; // Remove todos os filtros anteriores

                            if (value === "Todos") {
                                $.fn.dataTable.ext.search = [];
                                $("select.comboFilter").prop("selectedIndex", 0);
                                tableDados.draw(); // Não adiciona filtro ao escolher "Todos"
                                return;
                            } else if (value === "S") {
                                $("select.comboFilter").not(this).prop("selectedIndex", 0);
                                check = true;
                            } else if (value === "N") {
                                $("select.comboFilter").not(this).prop("selectedIndex", 0);
                                check = false;
                            }

                            // Filtra pela combobox (apenas um coluna de combo por vez)
                            if ((value === "S") || (value === "N")) {
                                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                                    var cell = tableDados.cell(dataIndex, colIndex).nodes().to$(); // Ajuste índice correto                                   
                                    var checkbox = cell.find('input[type="checkbox"]');

                                    var isChecked = checkbox.prop('checked');
                                    return isChecked === check;

                                });
                            }
                            tableDados.draw();
                        }); // fim comoFilter

                        $("#tableDados tbody").on('click', '.toggle', function() {
                            let campo = $(this).attr('id');
                            let valor = $(this).prop('checked') ? 1 : 0;
                            LINHA_ATUAL = $(this).closest('tr');
                            let idRegistro = $(LINHA_ATUAL).attr('id') || LINHA_ATUAL.data('id'); // Verifica ID ou data-id

                            $('#txtID').val(idRegistro);
                            $('#txtOper').val('toggle');

                            $.ajax({
                                method: "POST",
                                url: $('#frmEdit').attr('action'),
                                dataType: 'json',
                                data: {
                                    txtID: idRegistro,
                                    txtOper: 'toggle',
                                    txtCampo: campo,
                                    txtValor: valor
                                },
                                success: function(retorno) {
                                    if (retorno.erro === 0) {
                                        $().toastmessage('showSuccessToast', retorno.msg);
                                    } else {
                                        $().toastmessage('showToast', {
                                            text: retorno.msg,
                                            sticky: true,
                                            type: 'error'
                                        });
                                    }
                                },
                                error: function(xhr, status, errorThrown) {
                                    $().toastmessage('showToast', {
                                        text: 'Falha na requisição: ' + status,
                                        sticky: true,
                                        type: 'error'
                                    });
                                    console.error('Erro:', status, errorThrown);
                                }
                            });
                        }); // fim toggle

                        $("#tableDados tbody").on('click', '.clonar', function() {
                            let editalId = $(this).closest('tr').attr('id')
                            let strNome = $(this).closest('tr').find('td:eq(1)').html();

                            let tipo = 'disponiveis';
                            // monta o corpo da requisição em x-www-form-urlencoded
                            let body = new URLSearchParams({
                                editalId,
                                tipo
                            });

                            fetch('associar_grupos_ajax.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body // mesmo que body: body
                                })
                                .then(r => r.ok ? r.text() : Promise.reject(r.statusText)) // espera HTML
                                .then(html => {
                                    document.querySelector('#grupo1').innerHTML = html; // injeta no DOM
                                })
                                .catch(err => console.error('Falha no fetch:', err));

                            tipo = 'selecionados';
                            body = new URLSearchParams({
                                editalId,
                                tipo
                            });
                            fetch('associar_grupos_ajax.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body // mesmo que body: body
                                })
                                .then(r => r.ok ? r.text() : Promise.reject(r.statusText)) // espera HTML
                                .then(html => {
                                    document.querySelector('#grupo2').innerHTML = html; // injeta no DOM
                                })
                                .catch(err => console.error('Falha no fetch:', err));

                            $('#frmEdit')[0].reset();
                            $('cmdDel').hide();
                            $('#txtID').val(editalId);
                            $('#txtOper').val('clonar');
                            $('#divEdit').dialog('option', 'title', 'Clonar edital "' + strNome + '"');
                            $('#divEdit').dialog('open');
                            $('#txtNome').focus();

                        }); // fim clonar

                    } // fim success
                }); // fim ajax
            }); // fim listar




            $('#cmdNew').click(function() {
                $('#frmEdit')[0].reset();
                $('#txtOper').val('inc');
                $('#txtID').val(0);
                $('#cmdCheck').show();
                $('#cmdDel').hide();
                let editalId = 0;

                let tipo = 'disponiveis';
                            // monta o corpo da requisição em x-www-form-urlencoded
                            let body = new URLSearchParams({
                                editalId,
                                tipo
                            });
                fetch('associar_grupos_ajax.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body // mesmo que body: body
                                })
                                .then(r => r.ok ? r.text() : Promise.reject(r.statusText)) // espera HTML
                                .then(html => {
                                    document.querySelector('#grupo1').innerHTML = html; // injeta no DOM
                                })
                                .catch(err => console.error('Falha no fetch:', err));


                $('#divEdit').dialog('option', 'title', 'Incluir novo edital');
                $('#divEdit').dialog('open');
                $('#txtNome').focus();
            })

            $('#cmdGravar').click(function() {
                let ok = true;
                $('#frmEdit .classCaixaTextoObrg').each(function() {
                    if ($(this).val().trim() === '')
                        ok = false;
                });
                if (ok === false) {
                    $().toastmessage('showWarningToast', 'Preencha os campos obrigatórios!');
                    return;
                }

                $('#grupo2 option').prop('selected', true);
                if ($('#grupo2 option:selected').length == 0) {
                    $().toastmessage('showNoticeToast', 'Selecione ao menos um grupo.');
                    return;
                }

                if (document.querySelectorAll('input[name="chkModalidade[]"]:checked').length === 0) {
                    $().toastmessage('showNoticeToast', 'Selecione ao menos uma modalidade.');
                    return;
                }

                if (document.querySelectorAll('input[name="chkForma[]"]:checked').length === 0) {
                    $().toastmessage('showNoticeToast', 'Selecione ao menos uma forma de contratação.');
                    return;
                }


                $.ajax({
                    method: "POST",
                    url: $('#frmEdit').attr('action'),
                    data: $('#frmEdit').serialize(),
                    success: function(retorno) {
                        if (retorno.erro === 0) {
                            $().toastmessage('showSuccessToast', retorno.msg);

                            window.setTimeout(function() {
                                $('#divEdit').dialog('close');
                            }, 3000);
                            $('#cmdListar').trigger('click');
                        } else if (retorno.erro === -99) { // sessão expirada
                            $().toastmessage('showToast', {
                                text: "Sua sessão expirou! Você será redirecionado à tela de login!",
                                sticky: true,
                                type: 'notice'
                            });
                            setTimeout(function() {
                                window.top.location.href = "../index.php";
                            }, 5000);

                        } else {
                            $().toastmessage('showToast', {
                                text: retorno.msg,
                                sticky: true,
                                type: 'error'
                            });
                        }
                    },
                    error: function(xhr, status, errorThrown) {
                        $().toastmessage('showToast', {
                            text: 'Falha na requisição: ' + status,
                            sticky: true,
                            type: 'error'
                        });
                        console.error('Erro:', status, errorThrown);
                    }
                })
            }) // cmdGravar

            $('#cmdDel').click(function() {
                $('#txtOper').val('del');
                $.ajax({
                    method: "POST",
                    url: $('#frmEdit').attr('action'),
                    data: $('#frmEdit').serialize(),
                    success: function(retorno) {
                        if (retorno.erro === 0) {
                            $().toastmessage('showSuccessToast', retorno.msg);
                            tableDados.row(LINHA_ATUAL).remove().draw();
                            $('#divEdit').dialog('close');
                        } else {
                            $().toastmessage('showToast', {
                                text: retorno.msg,
                                sticky: true,
                                type: 'error'
                            });
                        }

                    },
                    error: function(xhr, status, errorThrown) {
                        $().toastmessage('showToast', {
                            text: 'Falha na requisição: ' + status,
                            sticky: true,
                            type: 'error'
                        });
                        console.error('Erro:', status, errorThrown);
                    }
                })
            }) // fim cmdDel

            $('#cmdCheckDuplicate').click(function() {
                $('#grupo2 option').prop('selected', true);
                if ($('#grupo2 option:selected').length == 0) {
                    $().toastmessage('showNoticeToast', 'Selecione ao menos um grupo.');
                    return;
                }

                if (document.querySelectorAll('input[name="chkModalidade[]"]:checked').length === 0) {
                    $().toastmessage('showNoticeToast', 'Selecione ao menos uma modalidade.');
                    return;
                }

                if (document.querySelectorAll('input[name="chkForma[]"]:checked').length === 0) {
                    $().toastmessage('showNoticeToast', 'Selecione ao menos uma forma de contratação.');
                    return;
                }


                $.ajax({
                    method: "POST",
                    url: 'edital_check_dupl_ajax.php',
                    data: $('#frmEdit').serialize() ,
                    success: function(retorno) {                        
                        if ((!retorno) ||
                            (Array.isArray(retorno) && retorno.length === 0) ||
                            ($.isPlainObject(retorno) && $.isEmptyObject(retorno))) {
                            $().toastmessage('showNoticeToast', 'Não há duplicidade de modalidade, forma de contratação e grupo.');
                            return;
                        }


                        let jsonData = Array.isArray(retorno) ? retorno[0] : retorno;

                        // Seleciona o <tbody> da tabela
                        const tbody = document.querySelector('#jsonTable tbody');
                        tbody.innerHTML = '';

                        // Percorre cada chave do objeto e cria uma linha
                        Object.keys(jsonData).forEach(key => {
                            const row = document.createElement('tr');

                            // Cria a célula da chave
                            const cellKey = document.createElement('td');
                            cellKey.textContent = key;
                            row.appendChild(cellKey);

                            // Cria a célula do valor
                            const cellValue = document.createElement('td');
                            // Se o valor conter tags HTML (por exemplo, <br> ou <ul>),
                            // utilize innerHTML para renderizá-las
                            cellValue.innerHTML = jsonData[key];
                            row.appendChild(cellValue);

                            // Adiciona a linha à tabela
                            tbody.appendChild(row);
                        });
                        $('#divDuplicado').dialog('open');

                    },
                    error: function(xhr, status, errorThrown) {
                        $().toastmessage('showToast', {
                            text: 'Falha na requisição: ' + status,
                            sticky: true,
                            type: 'error'
                        });
                        //console.error('Erro:', status, errorThrown);
                    }
                })
            }) // cmdCheck

            $("#tabs").tabs(function() {
                ;
            });

        }); // fim JQuery
    </script>

</head>


<body>

    <h3>Cadastro de edital</h3>
    <form id="frmMain" method="post" action="edital_list_ajax.php">
        <button type="button" id="cmdListar">Listar</button>
        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
        <button type="button" id="cmdNew"><i class="fa-solid fa-file-circle-plus"></i> Novo</button>
    </form>

    <div id="divDatatable"></div>

    <div id="divEdit" style="display: none;">
        <form id="frmEdit" autocomplete="off" action="edital_action_ajax.php" method="post">
            <div id="tabs">
                <ul>
                    <li><a href="#tabs-1">Dados gerais</a></li>
                    <li><a href="#tabs-2">Aplica-se a</a></li>
                    <li><a href="#tabs-3">Grupos associados</a></li>
                </ul>
                <div id="tabs-1">
                    <p><label for="txtNome">Nome:</label>
                        <input type="text" id="txtNome" name="txtNome" required="required" size="50" maxlength="60" class="classCaixaTextoObrg" />
                    </p>


                    <p><label for="txtVersao">Versão:</label>
                        <input type="number" id="txtVersao" name="txtVersao" required="required" min="1" max="99" class="classCaixaTextoObrg" />
                        <label for="txtDesignacao">Designação:</label>
                        <input type="number" id="txtDesignacao" name="txtDesignacao" required="required" min="0" max="99" class="classCaixaTextoObrg" />
                    </p>


                    <!-- Bloqueado: <label class="switch">
                <input type="checkbox" id="chkBloqueado" name="chkBloqueado">
                <span class="slider round"></span>
            </label>
            &emsp;&emsp;&emsp;&emsp;
            Ativo: <label class="switch">
                <input type="checkbox" id="chkAtivo" name="chkAtivo" checked="checked">
                <span class="slider round"></span>
            </label> -->

                    <p><label for="txtDoc">Doc aprovação:</label>
                        <input type="text" id="txtDoc" name="txtDoc" required="required" size="50" maxlength="50" />
                    </p>

                    <p><label for="txtVigencia">Início da vigência:</label>
                        <input type="text" id="txtVigencia" name="txtVigencia" required="required" size="10" maxlength="10" class="classCaixaTextoObrg calendar" />
                    </p>
                </div>
                <div id="tabs-2">
                    <table id="tableAssociacoes">
                        <tr>
                            <td><span class="classRotuloCampo">Modalidade</span></td>
                            <td><span class="classRotuloCampo">Forma de contratação</span></td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="chkModalidade[]" value="DLE">Dispensa de Licitação por Valor Eletrônica<br>
                                <input type="checkbox" name="chkModalidade[]" value="DL">Dispensa de Licitação não Eletrônica<br>
                                <input type="checkbox" name="chkModalidade[]" value="INEX">Inexilegibilidade<br>
                                <input type="checkbox" name="chkModalidade[]" value="LCA">Licitação Correios Aberta<br>
                                <input type="checkbox" name="chkModalidade[]" value="LCF">Licitação Correios Fechada<br>
                                <input type="checkbox" name="chkModalidade[]" value="XE">Pregão eletrônico
                            </td>
                            <td><input type="checkbox" name="chkForma[]" value="SRP">SRP-Sistema de Registro de Preços<br>
                                <input type="checkbox" name="chkForma[]" value="C">Convencional
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="tabs-3">
                    <table>
                        <tr>
                            <td><span class="classRotuloCampo">Grupos disponíveis</span></td>
                            <th></th>
                            <td><span class="classRotuloCampo">Grupos selecionados</span></td>
                            <th></th>
                        </tr>
                        <tr>
                            <td style="vertical-align:top">
                                <select id="grupo1" class="datalist" multiple ondblclick="moverItemGrupo('grupo1', 'grupo2')">
                                </select>
                            </td>


                            <td style="vertical-align:middle; text-align:center">
                                <p><button type="button"  onclick="moverItemGrupo('grupo1', 'grupo2')">&#8680;</button></p>
                                <p><button type="button"  onclick="moverItemGrupo('grupo2', 'grupo1')">&#8678;</button></p>
                                <p><button type="button"  onclick="moverTodosGrupo('grupo1', 'grupo2')">Incluir Todos &#11078;</button></p>
                                <p><button type="button" onclick="moverTodosGrupo('grupo2', 'grupo1')">&#11077; Retirar Todos</button></p>
                            </td>


                            <td style="vertical-align:top">
                                <select id="grupo2" name="grupos[]" class="datalist" multiple ondblclick="moverItemGrupo('grupo2', 'grupo1')">
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div> <!-- fim tabs -->

            <p>&nbsp;</p>
            <p><button type="button" id="cmdGravar" accesskey="g">
                    <i class="fa fa-save"></i> <u>G</u>ravar</button>
                &emsp;&emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp;
                <button type="button" id="cmdDel" accesskey="e">
                    <i class="fa fa-trash"></i> <u>E</u>xcluir</button>
                &emsp;&emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp;
                <button type="button" id="cmdCheckDuplicate" accesskey="v">
                    <i class="fa-solid fa-check-double"></i> <u>V</u>erificar duplicidade</button>
            </p>
            <input type="hidden" id="txtOper" name="txtOper" value="" />
            <input type="hidden" id="txtID" name="txtID" />

        </form>
    </div>

    <div id="divAssociarSecoes" title="Associação de Seções">
        <iframe id="ifrSecoes" src="associar_secoes_iframe.php" width="100%" height="90%" frameborder="0" style="overflow:hidden; border:0;" scrolling="no"></iframe>
    </div>


    <div id="divDuplicado" title="Edital com duplicaidade">
        <table id="jsonTable">
            <thead>
                <tr>
                    <th>Chave</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <!-- As linhas serão adicionadas aqui -->
            </tbody>
        </table>
    </div>


    <div id="divLoadingAjax" class="classLoadingAjax">
        <img src="../resources/img/ajax-loader.gif" alt="" />Processando requisição. Aguarde....
    </div>


    <div id="session-overlay"></div>


</body>

</html>