document.addEventListener("click", function (event) {
    const menus = document.querySelectorAll(".account-menu");

    menus.forEach(function (menu) {
        const button = menu.querySelector(".account-menu-toggle");
        const panel = menu.querySelector(".account-menu-panel");
        if (!button || !panel) return;

        if (button.contains(event.target)) {
            const isHidden = panel.classList.contains("hidden");
            document.querySelectorAll(".account-menu-panel").forEach(function (p) {
                p.classList.add("hidden");
            });
            if (isHidden) {
                panel.classList.remove("hidden");
            }
            return;
        }

        if (!menu.contains(event.target)) {
            panel.classList.add("hidden");
        }
    });
});
