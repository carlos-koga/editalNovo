<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de anexos</title>

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

        table.dataTable tfoot th {padding: 0 ; padding-left: 0.5em; text-align: left;}
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
                width: '100%',
                height: $(window).height(),
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
                            let id = $(this).closest('tr').attr('id');
                            window.open('anexo_action_ajax.php?txtID=' + id + '&txtOper=get', "janela_anexo");
                        });
                        $('#tableDados tbody tr td').on('click', '.edit', function() {
                            $('#cmdDel').show();
                            $('#txtOper').val('ed');
                            LINHA_ATUAL = $(this).closest('tr');
                            let intID = $(LINHA_ATUAL).attr('id');


                            $('#txtID').val(intID);
                            $.ajax({
                                method: "POST",
                                url: $('#frmEdit').attr('action'),
                                data: 'txtID=' + intID + '&txtOper=getjson',
                                success: function(retorno) {
                                    let json = retorno;
                                    $('#txtTitulo').val(json.anx_titulo);
                                    tinymce.get('txtTexto').setContent(json.anx_conteudo);
                                    if (json.anx_ativo)
                                        $('#chkAtivo').prop('checked', 'checked');
                                    else
                                        $('#chkAtivo').prop('checked', false);

                                    $('#divEdit').dialog('option', 'title', 'Editar anexo');
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
                            $('#divEdit').dialog('option', 'title', 'Clonar anexo "' + strNome + '"');
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
                $('#divEdit').dialog('option', 'title', 'Incluir novo anexo');
                $('#divEdit').dialog('open');
                $('#txtTitulo').focus();
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

            
            
            //============================= INÍCIO TINYMCE            

            tinymce.init({
                init_instance_callback: function() {
                    $('.tox-promotion').hide();
                    $('.tox-statusbar__branding').hide();
                },
                selector: "#txtTexto",
                valid_elements: '*[*]',
                extended_valid_elements: 'section[class|data-value],li[data-level]',
                width: '100%',
                min_height: 500,
                max_height: 750,
                
                top: 0,
                content_style: "body { font-family: 'Calibri'; font-size:11pt }",
                paste_data_images: true,
                object_resizing: "img",
                statusbar: false,
                language: 'pt_BR',
                allow_html_in_named_anchor: true,
                content_css: 'estilo.css',                
                setup: (editor) => {
                    // monta o botão de listas 
                    editor.ui.registry.addMenuButton('customNumList', {
                        icon: 'ordered-list',
                        tooltip: 'Lista ordenada com níveis',
                        fetch: function(callback) {
                            const items = [{
                                    type: 'menuitem',
                                    text: 'Iniciar lista padrão',
                                    onAction: function() {
                                        editor.insertContent('<ol><li data-level="2">Escreva a cláusula</li></ol>');
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
                }, // setup: (editor)

                toolbar: 'table',
                menu: {
                    format: {
                        title: 'Format',
                        items: 'bold italic superscript subscript | forecolor '
                    }                    
                },

                resize: true,
                menubar: "edit insert format table custom_tags insertFormula ",
                plugins: "accordion advlist anchor autolink autoresize autosave charmap code codesample directionality emoticons fullscreen help image insertdatetime link lists  media nonbreaking pagebreak preview quickbars save searchreplace table visualblocks visualchars wordcount table",
                
                //plugins: "table",                
                table_column_resizing: 'resizetable',
                object_resizing: true,
                //toolbar: 'table tabledelete | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
                //toolbar: 'customLiMenu bullist numlist',
                table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
                table_resize_bars: true,
                table_sizing_mode: 'auto',
                autoresize_overflow_padding: 5,
                table_default_attributes: {
                    border: '1'
                },
                table_default_styles: {
                    'border-collapse': 'collapse',                    
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
                                            }`,
                toolbar: "customNumList  levelmenu styles | undo redo  |bold italic | alignleft aligncenter alignright alignjustify | link image | forecolor emoticons | code",





            });
            //=========================== FIM TINYMCE

        }); // fim JQuery
    </script>

</head>

<body>

    <h3>Cadastro de anexo</h3>
    <form id="frmMain" method="post" action="anexo_list_ajax.php">
        <button type="button" id="cmdListar">Listar</button>
        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
        <button type="button" id="cmdNew"><i class="fa-solid fa-file-circle-plus"></i> Novo</button>
    </form>

    <div id="divDatatable"></div>

    <div id="divEdit" style="display: none;">
        <form id="frmEdit" autocomplete="off" action="anexo_action_ajax.php" method="post">
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

    <div id="divLoadingAjax" class="classLoadingAjax">
        <img src="../resources/img/ajax-loader.gif" alt="" />Processando requisição. Aguarde....
    </div>


    <div id="session-overlay"></div>


</body>

</html>