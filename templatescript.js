const sidebar = document.querySelector("#sidebar-toggle");

sidebar.addEventListener("click", function() {
    document.querySelector("#sidebar").classList.toggle("expand");
});