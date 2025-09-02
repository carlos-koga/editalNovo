<!DOCTYPE html>
<html>

<head>
    <title>Matriz cláusulas X editais</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../resources/css/sistema.css" />
    <link rel="stylesheet" type="text/css" href="../resources/css/form.css" />
    <link rel="stylesheet" href="../resources/css/fontawesome/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="estilo.css" />
    <link rel="stylesheet" type="text/css" href="crosstab.css" />


    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script type="text/javascript" src="../resources/js/DataTables/jQuery-2.2.4/jquery-2.2.4.min.js"></script>
    <script type="text/javascript" src="../resources/js/DataTables/jQueryUI-1.11.4/jquery-ui.min.js"></script>
    <script type="text/javascript" src="../resources/js/toastmessage/jquery.toastmessage.js"></script>
    <script src="crosstab.js"></script>
    <style>
        body {
            margin-top: 0px;
            width:90%
        }

        .container {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        select {
            width: 400px;
            height: 250px;
        }

        select option {
            padding: 3px;
        }

        .buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .filters {
            display: flex;
            gap: 10px;
        }

        .bloqueado {
            display: none;
            color: red
        }
    </style>

    <script>
        function moverItem(origemId, destinoId) {
            let origem = document.getElementById(origemId);
            let destino = document.getElementById(destinoId);

            Array.from(origem.selectedOptions).forEach(option => {
                destino.appendChild(option);
            });
        }

        function moverTodos(origemId, destinoId) {
            let origem = document.getElementById(origemId);
            let destino = document.getElementById(destinoId);

            Array.from(origem.options).forEach(option => {
                if (option.style.display !== "none") { // Apenas itens visíveis
                    destino.appendChild(option);
                }
            });
        }

        function filtrarLista() {
            /*let chkSRP = document.getElementById("chkSRP").checked;
            let chkP = document.getElementById("chkP").checked;
            let chkC = document.getElementById("chkC").checked; */
            let chkShowBloqueado = document.getElementById("chkShowBloqueado").checked;

            document.querySelectorAll("#lista1 option").forEach(option => {
                //let tipo = option.getAttribute("data-type");
                let bloqueado = option.classList.contains("bloqueado");

                // Exibir apenas se o tipo estiver marcado; Bloqueado só aparece se o tipo permitir
                //let mostrar = (tipo === "SRP" && chkSRP) ||
                //    (tipo === "P" && chkP) ||
                //    (tipo === "C" && chkC);

                // Se o item estiver bloqueado, só mostramos se chkShowBloqueado estiver marcado
                option.style.display = (!bloqueado || chkShowBloqueado) ? "block" : "none";
            });
        }
    </script>


    <script>
        $(document).ready(function() {

            $.ajax({
                url: 'edital_get_combo_editais_ajax.php',
                type: 'post',
                success: function(response) {
                    $('#lista1').html(response);
                    document.querySelectorAll("select option").forEach(option => {
                        option.addEventListener("mouseover", function() {
                            this.style.backgroundColor = "#d3d3d3"; // Cor de destaque
                        });

                        option.addEventListener("mouseout", function() {
                            this.style.backgroundColor = ""; // Remove o destaque ao sair
                        });
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Erro:', error);
                }
            });

            $("#plus").click(function() {
                let divFiltro = document.getElementById("divFiltro");
                let plusIcon = document.querySelector("#plus i");

                if (divFiltro.style.display === "none") {
                    divFiltro.style.display = "block"; // Mostra o filtro
                    plusIcon.className = "fa-regular fa-square-caret-up"; // Ícone de expandir
                } else {
                    divFiltro.style.display = "none"; // Oculta o filtro
                    plusIcon.className = "fa-regular fa-square-caret-down"; // Ícone de recolher
                }
            });


            $('#cmdListar').click(function() {
                $("#plus").trigger('click');
                let lista2Valores = [];

                // Pega todos os valores das options dentro de lista2
                $('#lista2 option').each(function() {
                    lista2Valores.push($(this).val());
                });
                $.ajax({
                    method: "POST",
                    url: 'crosstab_list_ajax.php',
                    data: {
                        lista2: lista2Valores
                    },
                    success: function(retorno) {
                        $('#divMatriz').html(retorno);
                        $('.botoes-container').show();
                    } // fim success
                }); // fim ajax          
            }); // fim cmdListar

        }); // fimJQuery
    </script>
</head>
<body>
    <fieldset id="fldFiltro" class="classFieldSet">
        <legend><span id="plus" class="classPointer"><i class="fa-regular fa-square-caret-up"></i></span> Filtro para a matriz cláusulas X editais</legend>
        <div id="divFiltro">
            <div class="filters">
                <!-- <label><input type="checkbox" id="chkSRP" checked="checked" onchange="filtrarLista()">SRP</label>
                <label><input type="checkbox" id="chkP" checked="checked" onchange="filtrarLista()">P</label>
                <label><input type="checkbox" id="chkC" checked="checked" onchange="filtrarLista()">C</label>
                &emsp;&emsp; -->
                <label><input type="checkbox" id="chkShowBloqueado" onchange="filtrarLista()"> Mostrar Bloqueados</label>
            </div> 

            <div class="container">
                <select id="lista1" multiple ondblclick="moverItem('lista1', 'lista2')">
                </select>

                <div class="buttons">
                    <div class="buttons">
                        <button onclick="moverItem('lista1', 'lista2')">→</button>
                        <button onclick="moverItem('lista2', 'lista1')">←</button>
                        <button onclick="moverTodos('lista1', 'lista2')">Incluir Todos</button>
                        <button onclick="moverTodos('lista2', 'lista1')">Retirar Todos</button>
                    </div>
                </div>

                <select id="lista2" multiple ondblclick="moverItem('lista2', 'lista1')">
                </select>
                <button type="button" id="cmdListar" accesskey="l"><u>L</u>istar</button>
            </div>
        </div>
    </fieldset>

    <div class="botoes-container">
        <button id="toggleContentButton" onclick="toggleAllContent()" data-expanded="false"><i class="fa-solid fa-maximize"></i> Expandir Texto</button>
        &emsp;&emsp;&emsp;
        <button id="toggleFormatButton" onclick="toggleAllFormat()" data-format="html"><i class="fa-solid fa-align-justify"></i> Mostrar Texto Plano</button>
        &emsp;&emsp;&emsp;
        <button onclick="exportarParaExcel()"><i class="fa-solid fa-file-excel"></i> Exportar para Excel</button>

    </div>

    <div id="divMatriz"></div>

</body>

</html>