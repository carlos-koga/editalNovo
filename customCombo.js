function toggleDropdown() {
    const dropdown = document.getElementById("dropdownContent");
    const inputBox = document.getElementById("searchInput").getBoundingClientRect();

    dropdown.style.top = inputBox.bottom + "px"; // Define a posição abaixo do input
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
	document.getElementById("searchInput").focus(); 
	
}


function filterOptions() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const options = document.getElementById("dropdownContent").getElementsByTagName("div");

    for (let i = 0; i < options.length; i++) {
        let txtValue = options[i].textContent || options[i].innerText;
        options[i].style.display = txtValue.toLowerCase().includes(input) ? "" : "none";
    }	
}


document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("dropdownContent").addEventListener("click", function(event) {
        if (event.target.tagName === "DIV") {            
			document.getElementById("searchInput").value = event.target.textContent;
            document.getElementById("searchInput").setAttribute("data-value", event.target.getAttribute("data-value"));			
			document.getElementById("selectedValue").value = event.target.getAttribute("data-value"); // Atualiza o selectedValue
            document.getElementById("dropdownContent").style.display = "none";
			$('#cmdRefresh').trigger('click');
			
        }
    });
});
