document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.querySelector('.toggle-btn');
    const sidebar = document.querySelector('.sidebar');
    
    if(toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            if(window.innerWidth <= 768) {
                sidebar.classList.toggle('mobile-open');
            } else {
                sidebar.classList.toggle('collapsed');
            }
        });
    }

    // Accordion Menu Logic
    const menuToggles = document.querySelectorAll('.menu-toggle');
    menuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parentLi = this.parentElement;
            const subMenu = parentLi.querySelector('.sub-menu');
            
            // Close other open menus (optional, if you want only one open at a time)
            document.querySelectorAll('.menu-item-has-children.open').forEach(openLi => {
                if(openLi !== parentLi) {
                    openLi.classList.remove('open');
                    openLi.querySelector('.sub-menu').style.display = 'none';
                }
            });
            
            // Toggle current menu
            parentLi.classList.toggle('open');
            if(parentLi.classList.contains('open')) {
                subMenu.style.display = 'block';
            } else {
                subMenu.style.display = 'none';
            }
        });
    });

    // Auto-open active menu on page load
    const activeLink = document.querySelector('.sub-menu a.active');
    if (activeLink) {
        const parentLi = activeLink.closest('.menu-item-has-children');
        if (parentLi) {
            parentLi.classList.add('open');
            parentLi.querySelector('.sub-menu').style.display = 'block';
        }
    }
});
