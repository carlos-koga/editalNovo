<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de seções</title>

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
        table.dataTable tfoot th {padding: 0 ; padding-left: 0.5em; text-align: left;}

        /* PARA PESQUISA PERSONALIZADA SEM ACENTO */

        .img-desabilitada {
            filter: grayscale(100%);
            opacity: 0.6;
            /* opcional: deixa mais "apagada" */
            cursor: not-allowed;
        }

        #tableOptions {
            border-collapse: collapse;
            width: 100%;
            font-size: 8pt;
        }

        /* jQuery-UI modal overlay */
        .ui-widget-overlay {
            z-index: 1000;
        }

        /* TinyMCE 5 usa .tox-dialog-wrap e .tox-dialog__backdrop, TinyMCE 4 usava .mce-window */
        .tox-dialog-wrap,
        .tox-dialog__backdrop,
        .mce-window {
            z-index: 1001 !important;
        }
    </style>

    <script type="text/javascript" src="../resources/js/DataTables/jQuery-2.2.4/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="../resources/js/DataTables/jQueryUI-1.11.4/jquery-ui.min.js"></script>
    <script type="text/javascript" src="../resources/js/toastmessage/jquery.toastmessage.js"></script>

    <script type="text/javascript" src="../resources/js/DataTables/DataTables-1.10.15/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../resources/js/DataTables/DataTables-1.10.15/js/dataTables.jqueryui.min.js"></script>
    <script type="text/javascript" src="../resources/js/tinymce6/tinymce.min.js"></script>


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



            $('#divEdit').dialog({
                width: '90%',
                height: 670,
                autoOpen: false,
                modal: false
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


            /*$.ajax({
                method: "POST",
                url: 'anexo_list_opcoes_ajax.php',
                success: function(retorno) {
                    $('#dialog-opcoes').html(retorno)
                }
            }); */
            $('#cmdListar').focus();

            $("body").on("keydown", function(event) {
                // Verifica se a tecla Enter foi pressionada
                var keycode = event.which || event.keyCode;
                if (keycode === 13) {
                    // Se divEdit estiver visível
                    if ($('#divEdit').is(':visible')) {
                        // Verifica se o elemento focado (document.activeElement) está contido dentro de #divEdit
                        if ($.contains($('#divEdit')[0], document.activeElement)) {
                            // Se estiver focado, dispara o cmdGravar
                            $('#cmdGravar').trigger('click');
                        }
                    } else {
                        // Se NÃO estiver focado, dispara o cmdListar
                        $('#cmdListar').trigger('click');
                    }
                    return true;
                }
            });


            $('#cmdListar').click(function() {
                event.preventDefault();
                $('#divLoadingAjax').show();
                $.fn.dataTable.ext.search = []; // Remove todos os filtros anteriores
                $.ajax({
                    method: "POST",
                    url: $('#frmMain').attr('action'),
                    data: $('#frmMain').serialize(),
                    success: function(retorno) {
                        $('#divLoadingAjax').hide();
                        $('#divDatatable').html(retorno);
                        $('#tableDados tbody tr td').on('click', '.view', function() {
                            let intID = $(this).closest('tr').attr('id');
                            $('#cmdDel').hide();
                            $('#cmdGravar').hide();
                            // Para tornar o conteúdo não editável
                            tinymce.get('txtTexto').getBody().setAttribute('contenteditable', false);
                            $.ajax({
                                method: "POST",
                                url: $('#frmEdit').attr('action'),
                                data: 'txtID=' + intID + '&txtOper=getjson',
                                success: function(retorno) {
                                    let json = retorno;
                                    $('#txtTitulo').val(json.sec_titulo);
                                    tinymce.get('txtTexto').setContent(json.sec_conteudo);
                                    if (json.sec_ativo)
                                        $('#chkAtivo').prop('checked', 'checked');
                                    else
                                        $('#chkAtivo').prop('checked', false);

                                    $('#divEdit').dialog('option', 'title', 'Editar seção');
                                    $('#divEdit').dialog('open');
                                    $('#txtTitulo').focus();
                                }
                            });

                        });

                        $('#tableDados tbody tr td').on('click', '.edit', function() {
                            $('#cmdDel').show();
                            $('#txtOper').val('ed');


                            // Se desejar reativar a edição:
                            tinymce.get('txtTexto').getBody().setAttribute('contenteditable', true);
                            LINHA_ATUAL = $(this).closest('tr');
                            let intID = $(LINHA_ATUAL).attr('id');


                            $('#txtID').val(intID);
                            $.ajax({
                                method: "POST",
                                url: $('#frmEdit').attr('action'),
                                data: 'txtID=' + intID + '&txtOper=getjson',
                                success: function(retorno) {
                                    let json = retorno;
                                    $('#txtTitulo').val(json.sec_titulo);
                                    tinymce.get('txtTexto').setContent(json.sec_conteudo);
                                    if (json.sec_ativo)
                                        $('#chkAtivo').prop('checked', 'checked');
                                    else
                                        $('#chkAtivo').prop('checked', false);

                                    $('#divEdit').dialog('option', 'title', 'Editar seção');
                                    $('#divEdit').dialog('open');
                                    $('#txtTitulo').focus();
                                }
                            });
                        })


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
                                data: {
                                    txtID: idRegistro,
                                    txtOper: 'toggle',
                                    txtCampo: campo,
                                    txtValor: valor
                                },
                                success: function(retorno) {
                                    console.log("Retorno do servidor:", retorno);
                                },
                                error: function(xhr, status, error) {
                                    try {
                                        var err = JSON.parse(xhr.responseText);
                                        var msgErro = err.Message || "Erro desconhecido.";
                                    } catch (e) {
                                        var msgErro = "Falha ao processar resposta do servidor.";
                                    }

                                    $().toastmessage('showToast', {
                                        text: msgErro,
                                        sticky: true,
                                        type: 'error'
                                    });
                                }
                            });
                        }); // fim toggle

                        $("#tableDados tbody").on('click', '.clonar', function() {
                            let id = $(this).closest('tr').attr('id')
                            let strNome = $(this).closest('tr').find('td:eq(1)').html();
                            $('#frmEdit')[0].reset();
                            $('cmdDel').hide();
                            $('#txtID').val(id);
                            $('#txtOper').val('clonar');
                            $('#divEdit').dialog('option', 'title', 'Clonar seção "' + strNome + '"');
                            $('#divEdit').dialog('open');
                            $('#txtTitulo').focus();

                        }); // fim clonar

                    } // fim success
                }); // fim ajax
            }); // fim listar




            $('#cmdNew').click(function() {
                $('#frmEdit')[0].reset();
                $('#txtOper').val('inc');
                $('#cmdDel').hide();
                $('#divEdit').dialog('option', 'title', 'Incluir novo seção');
                $('#divEdit').dialog('open');
                $('#txtTitulo').focus();
            })

            $('#cmdGravar').click(function() {
                event.preventDefault();
                let ok = true;
                $('#frmEdit .classCaixaTextoObrg').each(function() {
                    if ($(this).val().trim() === '')
                        ok = false;
                });
                if (ok === false) {
                    $().toastmessage('showWarningToast', 'Preencha os campos obrigatórios!');
                    return;
                }

                tinyMCE.triggerSave();

                $.ajax({
                    method: "POST",
                    url: $('#frmEdit').attr('action'),
                    data: $('#frmEdit').serialize(),
                    success: function(retorno) {
                        if (retorno == '') {
                            $().toastmessage('showSuccessToast', 'Gravação realizada com sucesso!');
                            window.setTimeout(function() {
                                $('#divEdit').dialog('close');
                            }, 3000);
                            $('#cmdListar').trigger('click');

                        } else
                        if (retorno == -99) { // sessão expirada
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
                                text: retorno,
                                sticky: true,
                                type: 'error'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        var err = JSON.parse(xhr.responseText);
                        $().toastmessage('showToast', {
                            text: err.Message,
                            sticky: true,
                            type: 'error'
                        });
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
                        if (retorno == '') {
                            $().toastmessage('showSuccessToast', 'Exclusão realizada com sucesso!');
                            tableDados.row(LINHA_ATUAL).remove().draw();
                            $('#divEdit').dialog('close');
                        } else {
                            $().toastmessage('showToast', {
                                text: retorno,
                                sticky: true,
                                type: 'error'
                            });
                        }

                    },
                    error: function(xhr, status, error) {
                        var err = JSON.parse(xhr.responseText);
                        $().toastmessage('showToast', {
                            text: err.Message,
                            sticky: true,
                            type: 'error'
                        });
                    }
                })
            }) // fim cmdDel


            var aMenu;
            $.ajax({
                async: false,
                type: "post",
                dataType: "json",
                url: 'get_menu_ajax.php',
                success: function(data) {
                    aMenu = data;
                },
            }); // fim ajax  
            var menuLen = aMenu.length;
            var tooloptions = "";
            var aItem;
            //============================= INÍCIO TINYMCE            

            tinymce.init({
                init_instance_callback: function() {
                    $('.tox-promotion').hide();
                    $('.tox-statusbar__branding').hide();
                },
                selector: "#txtTexto",
                valid_elements: '*[*]',
                extended_valid_elements: 'section[class|data-value],li[data-level|data-anexo-id],div[id|style]',
                width: '100%',
                min_height: 500,
                max_height: 500,
                keep_styles: false,
                top: 0,
                content_style: "body { font-family: 'Calibri'; font-size:11pt }",
                paste_data_images: true,
                object_resizing: "img",
                statusbar: false,
                language: 'pt_BR',
                allow_html_in_named_anchor: true,
                content_css: 'estilo_secao.css',
                setup: (editor) => {
                    // monta o menu de tags
                    for (var i in aMenu) {
                        let aOptions = [];
                        let item = aMenu[i];
                        let thisItem = item;
                        let menu_name = thisItem.menu_name;
                        let menu_text = thisItem.menu_text;
                        tooloptions += menu_name + ' ';
                        let aSubmenu = thisItem.json_agg;
                        for (var k in aSubmenu) {
                            let subitem = aSubmenu[k];
                            let submenu_text = subitem.submenu_text;
                            let submenu_action = subitem.submenu_action;
                            aOptions.push({
                                type: 'menuitem',
                                text: submenu_text,
                                onAction: () => editor.insertContent(submenu_action)
                            });
                        }
                        editor.ui.registry.addNestedMenuItem(menu_name, {
                            text: menu_text,
                            getSubmenuItems: () => aOptions
                        })
                    } // fim for

                    // subitens de fórmula
                    editor.ui.registry.addMenuItem('insert_dia_semana', {
                        text: 'Dia da semana',
                        onAction: function() {
                            editor.insertContent('【DiaSemana("","S")】');
                        }
                    });

                    editor.ui.registry.addMenuItem('insert_extenso', {
                        text: 'Número por extenso',
                        onAction: function() {
                            editor.insertContent('【Extenso("")】');
                        }
                    });


                    // monta o botão de listas 
                    editor.ui.registry.addMenuButton('customNumList', {
                        icon: 'ordered-list',
                        tooltip: 'Lista ordenada com níveis',
                        fetch: function(callback) {
                            const items = [{
                                    type: 'menuitem',
                                    text: 'Iniciar lista padrão',
                                    onAction: function() {
                                        editor.insertContent('<ol><li data-level="1">Escreva a cláusula</li></ol>');
                                    }
                                },
                                {
                                    type: 'menuitem',
                                    text: 'Iniciar lista alfabética',
                                    onAction: function() {
                                        editor.insertContent('<ol class="alpha"><li data-level="1">Item a</li><li data-level="2">Item a-1</li></ol>');
                                    }
                                },
                                {
                                    type: 'menuitem',
                                    text: 'Iniciar lista romana',
                                    onAction: function() {
                                        editor.insertContent('<ol class="roman"><li data-level="1">Item I</li><li data-level="2">Item I-A</li></ol>');
                                    }
                                },
                                {
                                    type: 'nestedmenuitem',
                                    text: 'Inserir item com nível',
                                    getSubmenuItems: function() {
                                        return [1, 2, 3, 4, 5].map(function(level) {
                                            return {
                                                type: 'menuitem',
                                                text: 'Nível ' + level,
                                                onAction: function() {
                                                    editor.insertContent('<li data-level="' + level + '">Item nível ' + level + '</li>');
                                                }
                                            };
                                        });
                                    }
                                }
                            ];
                            callback(items);
                        }
                    })

                    editor.ui.registry.addMenuButton('levelmenu', {
                        text: 'Nível',
                        fetch: function(callback) {
                            const items = [1, 2, 3, 4, 5].map(n => ({
                                type: 'menuitem',
                                text: 'Nível ' + n,
                                onAction: function() {
                                    const node = editor.selection.getNode();
                                    if (node.nodeName === 'LI') {
                                        node.setAttribute('data-level', n);
                                    }
                                }
                            }));
                            callback(items);
                        }
                    });

                    // Adiciona um item de menu customizado
                    editor.ui.registry.addMenuItem('inserirDivAnexos', {
                        text: 'Inserir <div id="divAnexos">',
                        onAction: function() {
                            // Insere exatamente a tag desejada no ponto de inserção atual
                            editor.insertContent('<div id="divAnexos"><h3 style="text-align: center;">AP&Ecirc;NDICES e ANEXOS</h3><h4>AP&Ecirc;NDICE 01 -MODELOS DE ATESTADOS, DECLARA&Ccedil;&Otilde;ES E PROPOSTA</h4></div>');
                        }
                    });

                    editor.ui.registry.addMenuItem('customDialogMenu', {
                        text: 'Selecionar anexo',
                        onAction: function() {
                            abrirMeuDialog(editor);
                        }
                    });


                }, // setup: (editor)

                toolbar: 'table',
                menu: {
                    format: {
                        title: 'Format',
                        items: 'bold italic superscript subscript | forecolor '
                    },
                    custom: {
                        title: 'Anexos',
                        items: 'inserirDivAnexos customDialogMenu'
                    },
                    custom_tags: {
                        title: 'Inserir tags',
                        items: '1 2 3 4 5 6 7 8 9 10 11 12 13 14 15'
                    },
                    insertFormula: {
                        title: 'Inserir fórmula',
                        items: 'insert_dia_semana insert_extenso'
                    },
                    customMenu: {
                        title: 'Cláusulas condicionais',
                        items: 'customMenu'
                    }
                },

                resize: true,
                menubar: "edit insert format custom table custom_tags insertFormula ",
                plugins: "accordion advlist anchor autolink autoresize autosave charmap code codesample directionality emoticons fullscreen help image insertdatetime link lists  media nonbreaking pagebreak preview quickbars save searchreplace table visualblocks visualchars wordcount",
                //plugins: "table",
                //table_column_resizing: 'resizetable',
                //toolbar: 'table tabledelete | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
                //toolbar: 'customLiMenu bullist numlist',
                table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
                //table_sizing_mode: 'relative',
                autoresize_overflow_padding: 5,
                table_default_attributes: {
                    border: '1'
                },
                table_default_styles: {
                    'border-collapse': 'collapse',
                    'width': '600px',
                    'padding': '0'
                },


                custom_colors: false,
                importcss_append: false,
                importcss_file_filter: /do-not-import\.css/, // truque para não importar nada
                style_formats_merge: false,
                style_formats: [{
                        title: 'Subtítulo Centralizado',
                        block: 'h4',
                        styles: {
                            'text-align': 'center'
                        }
                    },

                    {
                        title: 'Negrito',
                        inline: 'b'
                    },
                    {
                        title: 'Sublinhado',
                        inline: 'u'
                    },

                    {
                        title: 'Texto em vermelho',
                        inline: 'span',
                        styles: {
                            color: '#ff0000'
                        }
                    }
                    /*,
                    {
                        title: 'Cabeçalho em vermelho',
                        block: 'h1',
                        styles: {
                            color: '#ff0000'
                        }
                    },
                    {
                        title: 'Example 1',
                        inline: 'span',
                        classes: 'example1'
                    },
                    {
                        title: 'Example 2',
                        inline: 'span',
                        classes: 'example2'
                    },
                    {
                        title: 'Table styles'
                    },
                    {
                        title: 'Table row 1',
                        selector: 'tr',
                        classes: 'tablerow1'
                    },*/
                ],
                content_style: `
                                            h4 {
                                            margin-top: 1em;
                                            margin-bottom: 1em;
                                            }
   
    #divAnexos {
      border: 2px dashed purple;
      padding: 8px;
    }    
    li[data-anexo-id] { color: blue; font-weight: bold; }
  `,
                toolbar: "customNumList  levelmenu styles customDialogButton | undo redo  |bold italic | bullist alignleft aligncenter alignright alignjustify | link image | forecolor emoticons | code"
            }); //=========================== FIM TINYMCE

            // Função para abrir o dialog e tratar o resultado
            function abrirMeuDialog(editor) {
                // Obtém o nó atualmente selecionado no editor
                var node = editor.selection.getNode();
                if (node.nodeName.toLowerCase() !== 'li') {
                    $().toastmessage('showToast', {
                                    text: 'Posicione o cursor dentro de um item de lista para usar esta opção!',
                                    type: 'warning', // equivale a showWarningToast
                                    stayTime: 6000, // 6 s; mude para o valor que quiser
                                    sticky: false // true = só some quando clicar
                                });                    
                    return;
                }

                // Abre o dialog do jQuery UI
                $("#dialog-opcoes").dialog({
                    modal: true,
                    width: 1100,
                    height: 600,
                    buttons: {
                        "Confirmar": function() {
                            const $iframe = $('#dialog-opcoes iframe');

                            // 2. documento interno (só funciona se for mesma origem!)
                            const $doc = $iframe.contents();

                            const $radioSel = $doc.find('#tableOptions input[type="radio"]:checked');
                            const opcaoSelecionada = $radioSel.val();
                            //const titulo = $radioSel.closest('tr').find('td').eq(1).text();
                            const editor = tinymce.activeEditor;
                            const node = editor.selection.getNode();

                            // Verifica se o cursor está dentro de um <li>
                            const liNode = node.nodeName === 'LI' ? node : editor.dom.getParent(node, 'li');

                            if (liNode) {
                                //const text = liNode.textContent?.trim();

                                // Cria o HTML do <span>
                                //const spanHTML = editor.dom.encode(text);

                                // Substitui o <li> inteiro pelo <span>
                                //liNode.innerHTML = spanHTML;                            
                                editor.dom.setAttrib(node, 'data-anexo-id', opcaoSelecionada);
                                $(this).dialog("close");                                                        
                                editor.focus();
                            } else {
                                $().toastmessage('showToast', {
                                    text: 'Selecione uma opção antes de confirmar.',
                                    type: 'warning', // equivale a showWarningToast
                                    stayTime: 6000, // 6 s; mude para o valor que quiser
                                    sticky: false // true = só some quando clicar
                                });

                            }
                        },
                        "Cancelar": function() {
                            $(this).dialog("close");
                        }
                    },
                    open: function() {
                        // Se necessário, limpe a seleção anterior dos radio buttons
                        // $('#formOpcao input[name="opcao"]').prop('checked', false);
                    }
                });
            }



        }); // fim JQuery
    </script>

</head>

<body>

    <h3>Cadastro de seção</h3>
    <form id="frmMain" method="post" action="secao_list_ajax.php">
        <button type="button" id="cmdListar" accesskey="l"><u>L</u>istar</button>
        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
        <button type="button" id="cmdNew" accesskey="n"><i class="fa-solid fa-file-circle-plus"></i> <u>N</u>ovo</button>
    </form>

    <div id="divDatatable"></div>

    <div id="divEdit" style="display: none;">
        <form id="frmEdit" autocomplete="off" action="secao_action_ajax.php" method="post">
            <p><label for="txtTitulo">Título:</label>
                <input type="text" id="txtTitulo" name="txtTitulo" required="required" size="130" maxlength="130" class="classCaixaTextoObrg" />
            </p>

            <textarea id="txtTexto" name="txtTexto" required="required"></textarea>

            <p><button type="button" id="cmdGravar" accesskey="g">
                    <i class="fa fa-save"></i> <u>G</u>ravar</button>
                &emsp;&emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp; &emsp;
                <button type="button" id="cmdDel" accesskey="e">
                    <i class="fa fa-trash"></i> <u>E</u>xcluir</button>
            </p>
            <input type="hidden" id="txtOper" name="txtOper" value="" />
            <input type="hidden" id="txtID" name="txtID" />

        </form>
    </div>

    <div id="dialog-opcoes" title="Selecionar anexo" style="display: none;">
        <iframe id="frmOpcoes" src="anexo_list_opcoes_iframe.php" width="100%" height="90%" frameborder="0" style="overflow:hidden; border:0;" scrolling="no"></iframe>
    </div>

    <div id="divLoadingAjax" class="classLoadingAjax">
        <img src="../resources/img/ajax-loader.gif" alt="" />Processando requisição. Aguarde....
    </div>


    <div id="session-overlay"></div>


</body>

</html>