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
    <link rel="stylesheet" href="../resources/css/fontawesome/css/all.min.css" />

    <style>
        body,
        html {
            font-family: "Century gothic";
            font-size: 9pt
        }

        .divTable {
            display: table;
            margin-bottom: 10px;
            padding-top: 10px
        }

        .divTableRow {
            display: table-row;
        }

        .divTableHeading {
            background-color: #EEE;
            display: table-header-group;
        }

        .divTableCell,
        .divTableHead {
            border: 1px solid #999999;
            display: table-cell;
            padding: 3px 10px;
            vertical-align: top
        }

        .divTableHeading {
            background-color: #EEE;
            display: table-header-group;
            font-weight: bold;
        }

        .divTableHead {
            background-color: rgba(0, 0, 0, 0.1);
            font-weight: bold;
        }

        .divTableFoot {
            background-color: #EEE;
            display: table-footer-group;
            font-weight: bold;
        }

        .divTableBody {
            display: table-row-group;
        }

        .trSecao,
        .trBloco {
            cursor: default;
        }

        .text {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            /* number of lines to show */
            line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        #divBloco,
        #divSecao {
            height: 150px;
            overflow: auto;
        }

        table.dataTable tr.odd {
            background-color: #E2E4FF;
        }

        table.dataTable tr.even {
            background-color: #FFFFFF;
        }

        table.dataTable tr {
            font-family: Arial;
            font-size: 9pt
        }

        table.dataTable tr th.nopadding,
        table.dataTable tr td.nopadding {
            padding-left: 2px;
            padding-right: 2px;
        }

        .pointer {
            cursor: pointer;
        }

        :required {
            background-color: #ffff80;
        }
    </style>
    

    <script type="text/javascript" src="../resources/js/DataTables/jQuery-2.2.4/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="../resources/js/DataTables/jQueryUI-1.11.4/jquery-ui.min.js"></script>
    <script type="text/javascript" src="../resources/js/toastmessage/jquery.toastmessage.js"></script>

    <script type="text/javascript" src="../resources/js/DataTables/DataTables-1.10.15/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../resources/js/DataTables/DataTables-1.10.15/js/dataTables.jqueryui.min.js"></script>

    
    <script type="text/javascript" src="../resources/js/tinymce6/tinymce.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
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

            
            var aMenu;
            $.ajax({
                async: false,
                type: "post",
                dataType: "json",
                url: '../edital/get_menu_ajax.php',
                success: function(data) {
                    aMenu = data;
                },
            }); // fim ajax  

            var menuLen = aMenu.length;
            var tooloptions = "";
            var aItem;
            tinymce.init({
                init_instance_callback: function() {
                    $('.tox-promotion').hide();
                    $('.tox-statusbar__branding').hide();
                },
                content_css: 'estilo.css',

                

// Permite todos os elementos e atributos
valid_elements: '*[*]',
extended_valid_elements: '*[*]',

// Evita que o editor insira <p> automaticamente
newline_behavior: 'linebreak',

// Desativa a verificação e limpeza de HTML
verify_html: false,




                selector: "#txtTexto",
                width: '100%',
                min_height: 700,
                max_height: 700,
                top: 0,
                content_style: "body { font-family: 'Calibri'; font-size:11pt }",
                paste_data_images: true,
                object_resizing: "img",
                statusbar: false , 
                language: 'pt_BR',
                setup: (editor) => {

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
                }, // setup: (editor)        
                toolbar: 'table',
                menu: {
                    format: {
                        title: 'Format',
                        items: 'bold italic underline superscript subscript | forecolor '
                    },
                    custom_tags: {
                        title: 'Inserir tags',
                        items: '1 2 3 4 5 6 7 8 9 10 11 12 13 14 15'
                    }
                },

                resize: true,

                menubar: "edit insert format table custom_tags",
                plugins: "accordion advlist anchor autolink autoresize autosave charmap code codesample directionality emoticons fullscreen help image importcss insertdatetime link lists media nonbreaking pagebreak preview quickbars save searchreplace table visualblocks visualchars wordcount",

                //table_column_resizing: 'resizetable',
                //toolbar: 'table tabledelete | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',

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
                color_map: [
                    '#000000', 'Preta',
                    '#FF0000', 'Vermelha'
                ],
                importcss_append: true,
                toolbar: "undo redo | styles |bold italic underline| alignleft aligncenter alignright alignjustify | outdent indent | link image | forecolor emoticons | code"


                /* style_formats: [{
                         title: 'Negrito',
                         inline: 'b'
                     },
                     {
                         title: 'Texto em vermelho',
                         inline: 'span',
                         styles: {
                             color: '#ff0000'
                         }
                     },
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
                     },
                 ]*/

            });
        
        $('#cmdGravar').click(function() {
            tinymce.triggerSave();
            $.ajax({
                async: false,
                type: "post",                
                url: $('#frmTextoEdital').attr('action'),
                data: $('#frmTextoEdital').serialize(),
                success: function(retorno) {
                    
                },
                complete: function() {
                },
                error: {

                }
            }); // fim ajax  
        })

        }); // fim JQuery
    </script>

</head>

<body>
    <h3>Cadastro de modelo de edital</h3>
       

    <div id="divEdit" title="Inclusão de cláusula" style="height: 80%";>
        <form id="frmTextoEdital" method="post" action="edital_action_ajax.php">
            <textarea id="txtTexto" name="txtTexto" required="required"></textarea>
            <input type="hidden" id="txtID" name="txtID"  value="2"/>            
            <input type="hidden" id="txtOper" name="txtOper" value="ed" />            
            <button type="button" id="cmdGravar" ><i class="fas fa-save"></i> Gravar</button>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <button type="button" id="cmdDel"><i class="fa-solid fa-trash"></i> Excluir</button>
        </form>
    </div>

    
</body>
</html>