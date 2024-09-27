oc.registerControl('menublock', class extends oc.ControlBase {
    init() {

    }

    connect() {
        this.listen('mouseover', '[data-has-children]', this.toggleMenuDesktop);
        this.listen('mouseout', '[data-has-children]', this.hideMenu);
        this.listen('click', '[data-has-children]', this.toggleMenu);
    }

    showMenu(e) {
        const parentId = e.target.getAttribute('id');
        const menu = document.querySelector(`[data-parent-id="${parentId}"]`);

        if (menu) {
            const { top, left, height } = e.target.getBoundingClientRect();
            const menuWidth = menu.offsetWidth;


            menu.style.top = `${top + height}px`;
            menu.style.left = `${left}px`;

            if (left < 0) {
                menu.style.left = 0;
            } else if (left + menuWidth > window.innerWidth) {
                menu.style.left = `${window.innerWidth - menuWidth}px`;
            }

            menu.classList.add('isActive');
        }
    }

    hideMenu(e) {
        const parentId = e.target.getAttribute('id');
        const menu = document.querySelector(`[data-parent-id="${parentId}"]`);

        if (menu) {
            menu.classList.remove('isActive');
        }
    }

    toggleMenu(e) {
        e.preventDefault();

        if (window.innerWidth < 991) {
            this.showMenu(e);
        }
    }

    toggleMenuDesktop(e) {
        e.preventDefault();

        if (window.innerWidth > 990) {
            this.showMenu(e);
        }
    }
});
