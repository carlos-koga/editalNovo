<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banco de cláusulas</title>


    <link rel="stylesheet" type="text/css" href="../resources/css/sistema.css" />
    <link rel="stylesheet" type="text/css" href="../resources/css/form.css" />
    <link rel="stylesheet" type="text/css" href="../resources/js/DataTables/jQueryUI-1.11.4/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" href="../resources/js/toastmessage/jquery.toastmessage.css" />
    <link rel="stylesheet" type="text/css" href="../resources/js/DataTables/Buttons-1.3.1/css/buttons.dataTables.min.css" />
    <link rel="stylesheet" type="text/css" href="../resources/js/DataTables/DataTables-1.10.15/css/dataTables.jqueryui.min.css" />
    <link rel="stylesheet" href="../resources/css/fontawesome/css/all.min.css" />



    <style>
        body {
            font-family: Century Gothic;
            font-size: 11pt;
        }

        table {
            width: 800px;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            padding: 10px;
            text-align: justify;
            border: 1px solid #ccc;
        }

        /*tr:nth-child(even) { background-color: #f9f9f9; }
        tr:nth-child(odd) { background-color: #e0e0e0; } */
        .even {
            background-color: #f9f9f9;
        }

        .odd {
            background-color: #e0e0e0;
        }

        .hidden {
            display: none;
        }

        .oculto {
            display: none !important;
        }


        .toggle-icon {
            display: inline-block;
            width: 20px;
            font-weight: bold;
            margin-right: 5px;
            cursor: pointer;
        }

        .action {
            cursor: pointer;
        }

        .view {
            cursor: zoom-in;
        }

        .no-close .ui-dialog-titlebar-close {
            display: none;
        }

        .img-desabilitada {
            filter: grayscale(100%);
            opacity: 0.6;
            /* opcional: deixa mais "apagada" */
            cursor: not-allowed;
        }

        .bloqueado {
            color: red;
        }
    </style>

    <script type="text/javascript" src="../resources/js/DataTables/jQuery-2.2.4/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="../resources/js/DataTables/jQueryUI-1.11.4/jquery-ui.min.js"></script>
    <script type="text/javascript" src="../resources/js/toastmessage/jquery.toastmessage.js"></script>

    <script type="text/javascript" src="../resources/js/DataTables/DataTables-1.10.15/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../resources/js/DataTables/DataTables-1.10.15/js/dataTables.jqueryui.min.js"></script>
    <script type="text/javascript" src="../resources/js/tinymce6/tinymce.min.js"></script>
    <script type="text/javascript" src="customCombo.js"></script>

    <script type="text/javascript">
        function toggleChildren(parentId) {
            const parentRow = document.getElementById(parentId);
            const icon = parentRow.querySelector('.toggle-icon');
            const children = document.querySelectorAll(`[data-parent='${parentId}']`);
            let isExpanding = false;

            children.forEach(child => {
                if (child.classList.contains('hidden')) {
                    child.classList.remove('hidden');
                    isExpanding = true;
                } else {
                    child.classList.add('hidden');
                    const childId = child.getAttribute('data-id');
                    const subChildren = document.querySelectorAll(`[data-parent='${childId}']`);
                    subChildren.forEach(sub => sub.classList.add('hidden'));
                }
            });

            if (icon) icon.textContent = isExpanding ? "▼" : "▶";
            reapplyZebra();
        }

        function expandAll() {
            document.querySelectorAll('tr[data-parent]').forEach(row => row.classList.remove('hidden'));
            document.querySelectorAll('.toggle-icon').forEach(icon => {
                if (icon.textContent.trim() === "▶") icon.textContent = "▼";
            });
            reapplyZebra();
        }

        function collapseAll() {
            document.querySelectorAll('tr[data-parent]').forEach(row => row.classList.add('hidden'));
            document.querySelectorAll('.toggle-icon').forEach(icon => {
                if (icon.textContent.trim() === "▼") icon.textContent = "▶";
            });
            reapplyZebra();
        }

        function reapplyZebra() {
            $('#pesquisa').val('');
            $('#pesquisa').trigger('keyup');
            const rows = Array.from(document.querySelectorAll("table tr"))
                .filter(row => row.offsetParent !== null && !row.querySelector("th")); // ignora cabeçalho e ocultos

            rows.forEach((row, index) => {
                row.classList.remove("even", "odd");
                row.classList.add(index % 2 === 0 ? "even" : "odd");
            });
        }

        $(document).ready(function() {
            var LINHA_ATUAL;
            $().toastmessage({
                sticky: false,
                inEffectDuration: 600,
                stayTime: 4000,
                position: 'middle-center'
            });

            $("#divViewClausula").dialog({
                autoOpen: false,
                modal: false,
                width: 1150,
                height: 500,
                hide: {
                    effect: "explode",
                    duration: 200
                },
                open: function () {
                    $('#justificativa-box').hide(); // Garante que esteja oculta toda vez que abrir
                    $('#txtJustificativa').val('');
                }
            });

            function refresh() {
                $('#treeView').html('');
                $('#divLoadingAjax').show();
                $.ajax({
                    type: "post",
                    dataType: "html",
                    url: 'treeView_list_ajax.php',
                    data: 'edital=' + $('#selectedValue').val(),
                    success: function(retorno) {
                        $('#divLoadingAjax').hide();
                        $('#treeView').html(retorno);
                        reapplyZebra();

                        $('#clausulas').on('click', 'tbody tr td .view', function() {
                            thisElement = $(this);
                            var id = $(this).attr('id');
                            // Para tornar o conteúdo não editável
                            tinymce.get('txtTexto').getBody().setAttribute('contenteditable', false);
                            $.ajax({
                                type: "post",
                                dataType: "html",
                                url: 'treeView_get_clausula_ajax.php',
                                data: 'id=' + id,
                                success: function(retorno) {
                                    //tinymce.get('txtTexto').setContent( retorno );
                                    tinymce.get('txtTexto').setContent('<ol>' + retorno + '</ol>');
                                    $('#divViewClausula').dialog("option", "title", 'Visualização do item id=' + id);
                                    $('#divViewClausula').dialog("option", "position", {
                                        my: "left top", // O canto superior esquerdo do dialog
                                        at: "right top", // Alinha com o canto superior direito do elemento de acionamento
                                        of: thisElement // O elemento de acionamento (o botão clicado)
                                    }).dialog("open"); // Abre o dialog
                                    $('#cmdGravar').hide();
                                    $('#cmdDel').hide();
                                    $('#cmdDesativar').hide();
                                }
                            });
                        });

                        $('#clausulas').on('click', 'tbody tr td .edit', function() {
                            thisElement = $(this);
                            LINHA_ATUAL = $(this).closest('tr'); // Captura a linha onde o botão foi clicado
                            const span = $(LINHA_ATUAL).find('.toggle-icon');
                            if (span.length && span.attr('onclick')) {
                               $('#hasChildren').val('pai')
                            } else {
                                $('#hasChildren').val('') 
                            }
                            let id_atual = $(this).attr('id');
                            $('#txtIdAtual').val(id_atual);
                            $('#txtOper').val('ed');
                            tinymce.get('txtTexto').getBody().setAttribute('contenteditable', true);
                            $.ajax({
                                type: "post",
                                dataType: "html",
                                url: 'treeView_get_clausula_ajax.php',
                                data: 'id=' + id_atual,
                                success: function(retorno) {
                                    //tinymce.get('txtTexto').setContent(retorno);
                                    tinymce.get('txtTexto').setContent('<ol>' + retorno + '</ol>');
                                    $('#divViewClausula').dialog("option", "title", 'Edição do item id=' + id_atual);
                                    $('#divViewClausula').dialog("option", "position", {
                                        my: "left top", // O canto superior esquerdo do dialog
                                        at: "right top", // Alinha com o canto superior direito do elemento de acionamento
                                        of: thisElement // O elemento de acionamento (o botão clicado)
                                    }).dialog("open"); // Abre o dialog
                                    $('#cmdGravar').show();
                                    $('#cmdDel').show();
                                    $('#cmdDesativar').show();
                                }
                            });
                        });

                        $('#clausulas').on('click', 'tbody tr td .move', function() {
                            let thisElement = $(this); // Captura o botão clicado corretamente
                            let direcao = thisElement.attr('data-direcao');
                            let id_atual = thisElement.attr('id');
                            $.ajax({
                                type: "post",
                                dataType: "html",
                                url: 'clausula_troca_ordem_ajax.php',
                                data: 'id_atual=' + id_atual + '&direcao=' + direcao,
                                success: function(retorno) {
                                    if (retorno == '1') {
                                        let linha = thisElement.closest("tr")[0]; // Agora 'thisElement' está correto
                                        let tabela = document.getElementById("clausulas");

                                        let parent = linha.parentNode;
                                        let linhasVisiveis = Array.from(parent.children).filter(row => row.style.display !== "none");
                                        let indiceAtual = linhasVisiveis.indexOf(linha);

                                        if (!linha || indiceAtual === -1) {
                                            console.error("Erro: linha não encontrada ou índice inválido.");
                                            $().toastmessage('showErrorToast', 'Linha não encontrada ou índice inválido.!');
                                            return;
                                        }

                                        try {
                                            if (direcao === "up" && indiceAtual > 1) {
                                                parent.insertBefore(linha, linhasVisiveis[indiceAtual - 1]);
                                            } else if (direcao === "down" && indiceAtual < linhasVisiveis.length - 1) {
                                                parent.insertBefore(linhasVisiveis[indiceAtual + 1], linha);
                                            }
                                            reapplyZebra();
                                        } catch (error) {
                                            console.error("Erro ao mover linha:", error);
                                        }
                                    } else {
                                        $().toastmessage('showErrorToast', 'Não foi possível mover esta linha!');
                                    }
                                }
                            });
                        }); // fim move


                        $('#clausulas').on('click', 'tbody tr td .addrow', function() {
                            thisElement = $(this);
                            LINHA_ATUAL = $(this).closest('tr'); // Captura a linha onde o botão foi clicado
                            let id_atual = $(this).attr('id');
                            $('#txtIdAtual').val(id_atual);
                            $('#txtOper').val('inc');                            
                            $('#hasChildren').val('')                             
                            tinymce.get('txtTexto').setContent('');
                            $('#divViewClausula').dialog("option", "title", 'Inclusão de nova cláusula');
                            $('#divViewClausula').dialog("option", "position", {
                                my: "left top", // O canto superior esquerdo do dialog
                                at: "right top", // Alinha com o canto superior direito do elemento de acionamento
                                of: thisElement // O elemento de acionamento (o botão clicado)
                            }).dialog("open"); // Abre o dialog
                            $('#cmdGravar').show()
                            $('#cmdDel').hide();
                            $('#cmdDesativar').hide();

                        }) // fim addrow

                        $('.chkEdital').on('change', function() {
                            const checkbox = $(this);
                            const row = checkbox.closest('tr');
                            const idAtual = checkbox.attr('id');
                            const isChecked = checkbox.prop('checked');
                            const editalId = $('#selectedValue').val();

                            // Atualiza campos ocultos (se você usa no servidor)
                            $('#txtIdAtual').val(idAtual);
                            $('#txtOper').val('chk');
                            $('#txtNuEdital').val(editalId);

                            // 🔥 Envia AJAX para este checkbox (pai ou filho)
                            $.ajax({
                                type: "post",
                                dataType: "html",
                                url: $('#frmEdit').attr('action'),
                                data: $('#frmEdit').serialize() + '&estado=' + isChecked,
                                success: function(retorno) {
                                    console.log(`Atualizado: ${idAtual}`);
                                }
                            });

                            // 🌱 Se for pai, propaga a marcação visualmente e chama o AJAX de cada filha individualmente
                            const dataId = row.data('id');
                            if (dataId) {
                                $(`tr[data-parent="${dataId}"] .chkEdital`).each(function() {
                                    $(this).prop('checked', isChecked); // só marca visualmente

                                    const filha = $(this);
                                    const idFilha = filha.attr('id');
                                    $('#txtIdAtual').val(idFilha);

                                    // Gera e envia AJAX individual para cada filha
                                    $.ajax({
                                        type: "post",
                                        dataType: "html",
                                        url: $('#frmEdit').attr('action'),
                                        data: $('#frmEdit').serialize() + `&estado=${isChecked}`,
                                        success: function(retorno) {
                                            console.log(`Atualizado (filha): ${idFilha}`);
                                        }
                                    });
                                });
                            }
                        }); // fim chkEdital

                        $('#clausulas').on('click', 'tbody tr td .replace', function() {
                            let thisElement = $(this); // Captura o botão clicado corretamente 
                            LINHA_ATUAL = $(this).closest('tr'); // Captura a linha onde o botão foi clicado
                            const span = $(LINHA_ATUAL).find('.toggle-icon');
                            if (span.length && span.attr('onclick')) {
                               $('#hasChildren').val('pai')
                            } else {
                                $('#hasChildren').val('') 
                            }                           
                            let id_atual = thisElement.attr('id');
                            $('#txtIdAtual').val(id_atual);
                            $('#txtOper').val('replace');
                            $.ajax({
                                type: "post",
                                dataType: "html",
                                url: 'treeView_get_clausula_ajax.php',
                                data: 'id=' + id_atual,
                                success: function(retorno) {
                                    //tinymce.get('txtTexto').setContent(retorno);
                                    tinymce.get('txtTexto').setContent('<ol>' + retorno + '</ol>');
                                    $('#divViewClausula').dialog("option", "title", 'Substituição do item id=' + id_atual);
                                    $('#divViewClausula').dialog("option", "position", {
                                        my: "left top", // O canto superior esquerdo do dialog
                                        at: "right top", // Alinha com o canto superior direito do elemento de acionamento
                                        of: thisElement // O elemento de acionamento (o botão clicado)
                                    }).dialog("open"); // Abre o dialog
                                    $('#cmdGravar').show();
                                    $('#cmdDel').hide();
                                    $('#cmdDesativar').hide();
                                }
                            });
                        }) // fim replace    

                    } // fim success
                }); // fim ajax
            }

            
            $("#cmdDesativar").on("click", function() {                
                $('#lblJustificativa').html('Justificativa para a desativação');
                $('#cmdConfirmarDesativacao').html('Confirmar desativação')
                $("#justificativa-box").slideDown(); // Apenas exibe o campo
            });

            $('#cmdDel').click(function() {
                $().toastmessage('showToast', {
                                    text: 'A exclusão é permanente e afeta todos editais que possuem esta cláusula.',
                                    type: 'warning', // equivale a showWarningToast                                    
                                    sticky: true // true = só some quando clicar
                                });                
                $('#lblJustificativa').html('Justificativa para a EXCLUSÃO');
                $('#cmdConfirmarDesativacao').html('Confirmar exclusão')
                $("#justificativa-box").slideDown(); // Apenas exibe o campo                
            }); // fim cmdDel

            $("#cmdConfirmarDesativacao").on("click", function() {
                const justificativa = $("#txtJustificativa").val().trim();

                if (justificativa === "") {
                    $().toastmessage('showWarningToast', 'Informe a justificativa.');
                    return;
                } else {           
                    if ( $('#cmdConfirmarDesativacao').html() == 'Confirmar exclusão' ) 
                       $('#txtOper').val('del');      
                    else if ( $('#cmdConfirmarDesativacao').html() == 'Confirmar desativação' ) 
                       $('#txtOper').val('desativar');      
                    else 
                       $('#txtOper').val('ed');      
                    
                    $('#cmdGravar').trigger('click');
                }
            });


            $('#cmdRefresh').click(function() {
                refresh();
            });

            //$('#cmdRefresh').trigger('click');

            $("#pesquisa").on("keyup", function() {
                var termo = $(this).val().toLowerCase();

                $("#clausulas tr").each(function() {
                    var conteudo = $(this).find(".view").data("content") || "";
                    if (conteudo.toLowerCase().includes(termo)) {
                        $(this).removeClass("oculto");
                    } else {
                        $(this).addClass("oculto");
                    }
                });
            });

            $('#cmdPreview').click(function() {
                window.open('view_clausula.php?edital=' + $('#selectedValue').val(), 'preview');
            })

            $('#cmdPreviewEstrutura').click(function() {
                window.open('view_clausula.php?s&edital=' + $('#selectedValue').val(), 'preview');
            })

            $('#cmdMatriz').click(function() {
                window.open('crosstab.php', 'matriz');
            })






            //============================= variáveis para o menu do TinyMCE
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
                extended_valid_elements: 'ol[class],section[class|data-value],li[id|data-level|style],p,a[id|class|name|href|target|rel|title|data-*]',                
                schema: 'html5', 
                inline_styles: true,
                width: '100%',
                height: '100%',
                top: 0,
                keep_styles: false,
                paste_data_images: true,
                object_resizing: "img",
                statusbar: false,
                language: 'pt_BR',
                allow_html_in_named_anchor: true,
                content_css: ['estilo.css',
                    '../resources/css/fontawesome/css/all.min.css'
                ],
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
                                    if (node.nodeName.toLowerCase() === 'li') {
                                        node.setAttribute('data-level', n);
                                    }
                                }
                            }));
                            callback(items);
                        }
                    });

                    editor.ui.registry.addMenuItem('customMenu', {
                        text: 'Adicionar Section',
                        onAction: function() {
                            editor.windowManager.open({
                                title: 'Incluir Section',
                                body: {
                                    type: 'panel',
                                    items: [{
                                            type: 'input',
                                            name: 'class',
                                            label: 'Classe'
                                        },
                                        {
                                            type: 'input',
                                            name: 'data-value',
                                            label: 'Valor'
                                        }
                                    ]
                                },
                                buttons: [{
                                        type: 'cancel',
                                        text: 'Cancelar'
                                    },
                                    {
                                        type: 'submit',
                                        text: 'Inserir',
                                        primary: true
                                    }
                                ],
                                onSubmit: function(api) {
                                    const data = api.getData();
                                    const selectedNode = editor.selection.getNode();

                                    if (selectedNode.nodeName.toLowerCase() === 'section') {
                                        const newSection = editor.dom.create('section', {
                                            class: data.class,
                                            'data-value': data['data-value']
                                        }, 'Escreva as cláusulas aqui');

                                        editor.dom.add(selectedNode.parentNode, newSection, selectedNode.nextSibling);
                                    } else {
                                        editor.insertContent(`<section class="${data.class}" data-value="${data['data-value']}">Escreva as cláusulas aqui</section><p></p>`);
                                    }

                                    api.close();
                                }
                            });
                        }
                    });


                    editor.ui.registry.addMenuItem('customAnchor', {
                        text: 'Inserir Âncora',
                        onAction: function() {
                            const selectionNode = editor.selection.getNode();

                            editor.windowManager.open({
                                title: 'Inserir Âncora',
                                body: {
                                    type: 'panel',
                                    items: [{
                                        type: 'input',
                                        name: 'anchorId',                                        
                                        label: 'ID da Âncora',
                                        inputMode: 'text',
                                        placeholder: 'Não use acentos, nem espaços, nem símbolos. Aceita _'
                                    }]
                                },
                                buttons: [{
                                        type: 'cancel',
                                        text: 'Cancelar'
                                    },
                                    {
                                        type: 'submit',
                                        text: 'Inserir',
                                        primary: true
                                    }
                                ],
                                onChange: function (api, details) {
                                    if (details.name === 'anchorId') {
                                        let valor = api.getData().anchorId;

                                        // Define o travessão como caractere literal ou código Unicode
                                        const travessao = "_"; 

                                        // Remove acentos
                                        valor = valor.normalize("NFD").replace(/[\u0300-\u036f]/g, "");

                                        // Garante que o travessão seja tratado corretamente
                                        const regex = new RegExp(`[^a-zA-Z0-9${travessao}]`, "g");

                                        valor = valor.replace(regex, "");

                                        api.setData({ anchorId: valor });
                                    }
                                },
                                onSubmit: function(api) {
                                    const data = api.getData();
                                    const anchorId = data.anchorId;
                                    const selectedText = editor.selection.getContent({
                                        format: 'text'
                                    }) || 'Texto da âncora';

                                    // Insere a âncora com o ID informado
                                    const anchorTag = `<a id="${anchorId}" class="ancora" title="${anchorId}">${selectedText}</a>`;
                                    editor.insertContent(anchorTag);

                                    // Aguarda a inserção e sincroniza o <li> mais próximo
                                    setTimeout(() => {
                                        const node = editor.selection.getNode();
                                        const liElement = node.closest('li');
                                        if (liElement) {
                                            liElement.setAttribute('id', anchorId);
                                        }
                                    }, 0); // aguarda o DOM atualizar após inserção

                                    api.close();
                                }
                            });
                        }
                    });





                    editor.ui.registry.addMenuItem('inserirLinkItem', {
                        text: 'Inserir link para item',
                        onAction: function() {
                            abrirDialogAncora(editor);
                        }
                    });





                }, // setup: (editor)

                toolbar: 'table',
                menu: {
                    format: {
                        title: 'Format',
                        items: 'bold italic superscript subscript | forecolor '
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
                        title: 'Miscelâneas',
                        items: 'customMenu customAnchor inserirLinkItem'
                    }
                },

                resize: true,
                menubar: "edit insert format table custom_tags insertFormula customMenu ",
                plugins: "accordion advlist autolink autoresize autosave charmap code  directionality emoticons fullscreen help image insertdatetime link lists  nonbreaking pagebreak preview quickbars save searchreplace table visualblocks visualchars wordcount",
                // retirados plugins anchor media codesample 
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
                                body {
                                    font-family: 'Calibri';
                                    font-size: 11pt;
                                }
                                h4 {
                                    margin-top: 1em;
                                    margin-bottom: 1em;
                                }                                
                                a.mce-item-anchor {
                                    background: none !important;
                                    background-image: none !important;
                                    padding-left: 0 !important;
                                }
                                a.ancora[id] {
                                    padding-left: 20px !important;
                                    position: relative !important;
                                    text-decoration: none !important;
                                }
                                a.ancora[id]::before {
                                    content: "\\f13d" !important;
                                    font-family: "Font Awesome 6 Free" !important;
                                    font-weight: 900 !important;
                                    position: absolute;
                                    left: 0;
                                    color: navy;
                                    font-size: 14px;
                                    }`,

                toolbar: "customNumList  levelmenu styles |customMenu |undo redo  |bold italic | alignleft aligncenter alignright alignjustify | link image | forecolor emoticons | code",





            });
            //=========================== FIM TINYMCE

            

            // Função para abrir o dialog e tratar o resultado
            function abrirDialogAncora(editor) {
                // Obtém o nó atualmente selecionado no editor
                var node = editor.selection.getNode();


                // Abre o dialog do jQuery UI
                $("#dialog-ancora").dialog({
                    modal: true,
                    width: 970,
                    height: 600,
                    buttons: {
                        "Confirmar": function() {
                            const $iframe = $('#dialog-ancora iframe');

                            // 2. documento interno (só funciona se for mesma origem!)
                            const $doc = $iframe.contents();

                            const $radioSel = $doc.find('#tableOptions input[type="radio"]:checked');
                            const opcaoSelecionada = $radioSel.val();
                            const titulo = $radioSel.closest('tr').find('td').eq(1).text();

                            if (opcaoSelecionada) {
                                // Exemplo: insere o texto da opção após o conteúdo existente do <li>
                                var novoConteudo = '<a href="#' + opcaoSelecionada + '" class="linkAncora">#' + opcaoSelecionada + '</a>';
                                //editor.dom.setHTML(node, novoConteudo);
                                editor.insertContent(novoConteudo);
                                editor.dom.setAttrib(node, 'data-anexo-id', opcaoSelecionada);
                                $(this).dialog("close");
                                // Retoma o foco no editor
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
                        const iframe = document.getElementById('frmOpcoes');
                        const originalSrc = iframe.getAttribute('src');

                        // Força recarregamento redefinindo o src
                        iframe.setAttribute('src', originalSrc);
                    }
                });
            }



            $('#cmdGravar').click(function() {
                tinymce.triggerSave();
                $.ajax({
                    type: "post",
                    dataType: "json",
                    url: $('#frmEdit').attr('action'),
                    data: $('#frmEdit').serialize(),
                    success: function(retorno) {
                        if ($('#txtOper').val() == 'inc') {
                            if (retorno.erro == 0) {
                                $('#divViewClausula').dialog('close');                                
                                let novaLinha = $(retorno.msg).hide().css('background-color', '#d1ffd1'); // fundo verde-claro
                                LINHA_ATUAL.after(novaLinha);
                                novaLinha.fadeIn(400).animate({ backgroundColor: "#ffffff" }, 1200);
                            } else {
                                $().toastmessage('showErrorToast', retorno.msg);
                                console.error(retorno.description);
                            }
                        } else if ($('#txtOper').val() == 'ed') {
                            if (retorno.erro == 0) {
                                $('#divViewClausula').dialog('close');
                                LINHA_ATUAL.replaceWith(retorno.msg);
                            } else {
                                $().toastmessage('showErrorToast', retorno.msg);
                                console.error(retorno.description);
                            }
                        } else if ($('#txtOper').val() == 'del') {
                            if (retorno.erro == 0) {
                                $('#divViewClausula').dialog('close');
                                $().toastmessage('showSuccessToast', retorno.msg);
                                LINHA_ATUAL.remove();
                            } else {
                                $().toastmessage('showErrorToast', retorno.msg);
                                console.error(retorno.description);
                            }
                        } else if ($('#txtOper').val() == 'desativar') {
                            if (retorno.erro == 0) {
                                $('#divViewClausula').dialog('close');
                                LINHA_ATUAL.replaceWith(retorno.msg);                                
                            } else {
                                $().toastmessage('showErrorToast', retorno.msg);
                                console.error(retorno.description);
                            }
                        } else if ($('#txtOper').val() == 'replace') {
                            if (retorno.erro == 0) {
                                $('#divViewClausula').dialog('close');
                                let novaLinha = $(retorno.msg).hide().css('background-color', '#d1ffd1'); // fundo verde-claro
                                LINHA_ATUAL.after(novaLinha);
                                novaLinha.fadeIn(400).animate({ backgroundColor: "#ffffff" }, 1200);

                                // Altera o estilo da primeira <td>
                                let primeiraTd = LINHA_ATUAL.find('td').first().css('color', 'red');
                                
                                // Substitui as classes das imagens
                                LINHA_ATUAL.find('img').attr('class', 'img-desabilitada');
                            } else {
                                $().toastmessage('showErrorToast', retorno.msg);
                                console.error(retorno.description);
                            }
                        } else {
                            $('#divViewClausula').dialog('close');
                            $().toastmessage('showErrorToast', retorno.msg);
                        }

                    } // fim success
                }); // fim ajax    

            }); // fim $('#cmdGravar')

            


            $('#cmdComboRefresh').click(function() {
                $.ajax({
                    type: "post",
                    dataType: "html",
                    url: 'edital_get_nomes_ajax.php',
                    success: function(retorno) {
                        $("#dropdownContent").html(retorno);
                        const firstOption = document.querySelector("#dropdownContent div");
                        document.getElementById("searchInput").value = firstOption.textContent;
                        document.getElementById("selectedValue").value = firstOption.getAttribute("data-value");
                        document.getElementById("selectedValue").value = firstOption.getAttribute("data-value");
                        document.getElementById("searchInput").focus();
                    },
                    complete: function() {
                        $('#cmdRefresh').trigger('click');
                    }
                });

            });

            $('#cmdComboRefresh').trigger('click');

            $('#cboEdital').change(function() {
                $('#cmdRefresh').trigger('click');
            })

        }); // fim JQuery
    </script>
</head>

<body>
    <div class="container">
        <label for="searchInput">Edital:</label>
        <div class="dropdown">
            <input type="text" id="searchInput" placeholder="Pesquise..." onkeyup="filterOptions()">
            <div id="dropdownContent" class="dropdown-content">
            </div>
        </div>
        <span class="classPointer" onclick="toggleDropdown()">▼</span>
        <button id="cmdComboRefresh"><i class="fa-solid fa-arrows-rotate"></i></button>
        <input type="hidden" id="selectedValue" value="0">
    </div>

    <div style="padding:3px">
        <button id="cmdRefresh"><i class="fa-solid fa-arrows-rotate"></i> Atualizar página</button>&emsp;&emsp;&emsp;
        <button id="cmdPreview"><i class="fa-solid fa-eye"></i> Visualizar cláusulas</button>&emsp;&emsp;&emsp;
        <button id="cmdPreviewEstrutura"><i class="fa-solid fa-folder-tree"></i> Visualizar estrutura</button>&emsp;&emsp;&emsp;
        <button id="cmdMatriz"><i class="fa-solid fa-xmarks-lines"></i> Matriz clausulas X editais</button>
    </div>

    <div style="padding:3px">
        <button onclick="expandAll()"><i class="fa-solid fa-maximize"></i> Expandir tudo</button>&emsp;&emsp;&emsp;
        <button onclick="collapseAll()"><i class="fa-solid fa-minimize"></i> Contrair tudo</button>
        <br>
        <input type="text" id="pesquisa" size="60" placeholder="Digite uma palavra ou frase para pesquisar. EXPANDA TUDO primeiro" />
    </div>


    <div id="treeView"></div>

    <div id="divViewClausula" title="" style="display: none;">
        <form id="frmEdit" method="post" action="clausula_action_ajax.php">
            <textarea id="txtTexto" name="txtTexto" required="required"></textarea>
            <button id="cmdGravar" type="button" accesskey="g" style="display: none" ;><u>G</u>ravar</button>
            &emsp;&emsp;&emsp;&emsp;
            <button id="cmdDesativar" type="button" accesskey="d" style="display: none" ;><u>D</u>esativar</button>
            &emsp;&emsp;&emsp;&emsp;
            <button id="cmdDel" type="button" accesskey="x" style="display: none" ;>E<u>x</u>cluir</button>
            <input type="hidden" id="txtOper" name="txtOper" />
            <input type="hidden" id="txtIdAtual" name="txtIdAtual" />
            <input type="hidden" id="txtNuEdital" name="txtNuEdital" />
            <input type="hidden" id="hasChildren" name="hasChildren" />
            <div id="justificativa-box" style="display: none; margin-top: 15px;">
                <label for="justificativa" id="lblJustificativa">Justificativa:</label>
                <textarea id="txtJustificativa" name="txtJustificativa" rows="3" style="width: 100%;"></textarea>
                <div style="margin-top:10px; text-align: right;">
                    <button type="button" id="cmdConfirmarDesativacao">Confirmar</button>
                </div>
            </div>


        </form>
    </div>

    <div id="divConfirm"></div>

    <div id="dialog-ancora" title="Selecionar uma âncora" style="display: none;">
        <iframe id="frmOpcoes" src="ancora_list_opcoes_iframe.php" width="100%" height="90%" frameborder="0" style="overflow:hidden; border:0;" scrolling="no"></iframe>
    </div>

    <div id="divLoadingAjax" class="classLoadingAjax">
        <img src="../resources/img/ajax-loader.gif" border="0" alt="" />Processando requisição. Aguarde....
    </div>


</body>
<link rel="stylesheet" type="text/css" href="customCombo.css" />

</html>