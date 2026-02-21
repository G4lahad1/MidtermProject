document.addEventListener("DOMContentLoaded", function() {
    // Select the elements
    const sidebar = document.querySelector(".sidebar");
    const sidebarBtn = document.querySelector(".sidebarBtn");

    // Debugging: Check if elements exist
    if (!sidebar) {
        console.error("Error: Element with class '.sidebar' not found!");
        return;
    }
    if (!sidebarBtn) {
        console.error("Error: Element with class '.sidebarBtn' not found!");
        return;
    }

    // Add click event listener
    sidebarBtn.addEventListener("click", () => {
        sidebar.classList.toggle("active");
        
        // Change the menu icon
        if(sidebar.classList.contains("active")){
            sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
        }else{
            sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
        }
    });
});