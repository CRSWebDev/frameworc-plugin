oc.registerControl('navigation', class extends oc.ControlBase {
    init() {
        this.offset = 200;

        this.mobileNav = document.querySelector('.Navigation-menu--mobile');
    }

    connect() {
        window.addEventListener('scroll', this.proxy(this.handleScroll));

        this.listen('click', '.Navigation-toggle', this.handleToggle);
        this.listen('click', '.Navigation-subNavToggle', this.handleSubNavToggle);
    }

    disconnect() {
        window.removeEventListener('scroll', this.proxy(this.handleScroll));
    }

    handleScroll() {
        const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;

        if (scrollTop >= this.offset) {
            this.element.classList.add('isScrolled');
        } else {
            this.element.classList.remove('isScrolled');
        }
    }

    handleToggle(e) {
        e.preventDefault();

        this.element.classList.toggle('isOpen');
        this.mobileNav.classList.toggle('isActive');
    }

    handleSubNavToggle(e) {
        e.preventDefault();

        let id = '';

        if (e.target.classList.contains('Navigation-subNavToggle')) {
            id = e.target.getAttribute('data-target');
        } else {
            id = e.target.closest('.Navigation-subNavToggle').getAttribute('data-target');
        }

        const subNavWrapper = document.querySelector(`#${id}`);
        const subNav = subNavWrapper.querySelector('.Navigation-subNav');

        if (subNavWrapper.classList.contains('isActive')) {
            subNavWrapper.classList.remove('isActive');
            subNavWrapper.style.height = 0;
        } else {
            subNavWrapper.classList.add('isActive');
            subNavWrapper.style.height = `${subNav.scrollHeight}px`;
        }
    }
})
