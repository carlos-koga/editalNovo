function exportarParaExcel() {
    let data = [];
    document.querySelectorAll(".linha").forEach(linha => {
        let rowData = [];
        linha.querySelectorAll(".coluna").forEach(coluna => {
            rowData.push(coluna.innerText);
        });
        data.push(rowData);
    });

    let ws = XLSX.utils.aoa_to_sheet(data);
    let wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Matriz");
    XLSX.writeFile(wb, "matriz_clausulas_editais.xlsx");
}

function toggleAllContent() {
    let textoExpandido = document.querySelector("#toggleContentButton").dataset.expanded === "true";
    document.querySelectorAll(".toggle-content").forEach(div => {
        if (textoExpandido) { // se está expandido, recolhe
            let resumo = div.getAttribute("data-short");            
            if (!resumo) {
                resumo = "Sem conteúdo disponível"; // Evita erros se estiver vazio
            }
            div.innerHTML = resumo;
            div.classList.remove("expandido");
        } else { // se está recolhido, expande e alterna para Mostrar Texto Plano
            div.innerHTML = div.getAttribute("data-full");
            div.classList.add("expandido");
            document.querySelector("#toggleFormatButton").innerHTML  =  '<i class="fa-solid fa-align-justify"></i> Mostrar Texto Plano';
           document.querySelector("#toggleFormatButton").dataset.format = "html";
        }
    });
    document.querySelector("#toggleContentButton").innerHTML = textoExpandido ? '<i class="fa-solid fa-maximize"></i> Expandir Texto' : '<i class="fa-solid fa-minimize"></i> Recolher Texto';
    document.querySelector("#toggleContentButton").dataset.expanded = textoExpandido ? "false" : "true";
}

function toggleAllFormat() {
    document.querySelector("#toggleContentButton").dataset.expanded = "true";
    document.querySelector("#toggleContentButton").innerHTML  = '<i class="fa-solid fa-minimize"></i> Recolher Texto'; // Garante que o botão tenha o texto correto
    
    let formatoHtml = document.querySelector("#toggleFormatButton").dataset.format === "html";
    document.querySelectorAll(".toggle-content").forEach(div => {
        if (formatoHtml) {
            div.innerHTML = div.getAttribute("data-plain");
        } else {
            div.innerHTML = div.getAttribute("data-full");
        }
    });
    document.querySelector("#toggleFormatButton").innerHTML  = formatoHtml ? '<i class="fa-solid fa-list-ol"></i> Mostrar Formatado' : '<i class="fa-solid fa-align-justify"></i> Mostrar Texto Plano';
    document.querySelector("#toggleFormatButton").dataset.format = formatoHtml ? "plain" : "html";
}

