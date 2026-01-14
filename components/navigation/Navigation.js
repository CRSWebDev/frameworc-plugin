oc.registerControl('navigation', class extends oc.ControlBase {
    init() {
        this.options = JSON.parse(this.config.options || '{}');

        this.offset = 200;

        this.lastScrollTop = 0;
        this.scrollOffset = 50;

        this.itemPadding = 15;

        this.mobileNav = document.querySelector('.Navigation-menu--mobile');

        this.underline = this.element.querySelector('.Navigation-underline');

        this.infobar = this.element.querySelector('.Infobar');

        if (this.infobar) {
            if (localStorage.getItem('infobarClosed')) {
                this.infobar.style.display = 'none';
            } else {
                this.infobar.style.display = 'block';
            }
        }

        this.menuHide = this.options.menuHide === 1;

        /* SCROLLSPY */
        this.scrollElement = window;
        this.scrollSpyLinks = Array.from(document.querySelectorAll('.Navigation-item--anchor'));
        this.scrollSpyBoxes = this.scrollSpyLinks.map((link) => {
            let targetSelector = link.getAttribute('href');
            if (targetSelector.startsWith('/')) {
                targetSelector = targetSelector.substring(1);
            }

            return document.querySelector(targetSelector);
        });
    }

    connect() {
        window.addEventListener('scroll', this.proxy(this.handleScroll));
        this.scrollSpy(0);

        this.listen('click', '.Navigation-toggle', this.handleToggle);
        this.listen('click', '.Navigation-subNavToggle', this.handleSubNavToggle);

        this.listen('mouseenter', '.Navigation-item--level1', this.moveUnderline);
        this.listen('mouseleave', '.Navigation-menu--desktop', this.initialUnderlinePosition);
        this.listen('mouseenter', '.Navigation-extraLinkWrapper', this.initialUnderlinePosition);

        this.listen('click', '.Infobar-close', this.closeInfobar);

        this.listen('click', '.Navigation-item', this.handleToggle);

        setTimeout(() => {
            this.initialUnderlinePosition();
        }, 100);
    }

    disconnect() {
        window.removeEventListener('scroll', this.proxy(this.handleScroll));
    }

    handleScroll() {
        const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;

        this.scrollSpy(scrollTop);

        if (scrollTop >= this.offset) {
            this.element.classList.add('isScrolled');
        } else {
            this.element.classList.remove('isScrolled');
        }

        // Hide nav when scrolling down
        if (this.menuHide === false) {
            return;
        }

        if (scrollTop > this.lastScrollTop + this.scrollOffset) {
            this.element.classList.add('isHidden');
            this.lastScrollTop = scrollTop;
        }

        // Show nav when scrolling up
        if (scrollTop < this.lastScrollTop || scrollTop < this.scrollOffset) {
            this.element.classList.remove('isHidden');
            this.lastScrollTop = scrollTop;
        }
    }

    handleToggle(e) {
        if (e.target.classList.contains('Navigation-item')) {
            this.element.classList.remove('isOpen');
            this.mobileNav.classList.remove('isActive');

            return;
        }

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

    moveUnderline(e) {
        const {x, width} = e.target.getBoundingClientRect();

        this.underline.style.left = `${x + this.itemPadding}px`;
        this.underline.style.width = `${width - this.itemPadding * 2}px`
        this.underline.style.opacity = 1;
    }

    initialUnderlinePosition() {
        const activeItem = this.element.querySelector('.Navigation-item--level1.isActive');

        if (activeItem) {
            const {x, width} = activeItem.getBoundingClientRect();

            this.underline.style.left = `${x + this.itemPadding}px`;
            this.underline.style.width = `${width - this.itemPadding * 2}px`
            this.underline.style.opacity = 1;
        } else {
            this.underline.style.opacity = 0;
        }
    }

    closeInfobar() {
        localStorage.setItem('infobarClosed', true);

        this.infobar.style.display = 'none';
    }

    scrollSpy(scrollTop) {
        this.scrollSpyLinks.forEach((link) => {
            link.classList.remove('isActive');
        })

        let visibleBox = null;

        // Check if boxes are visible
        for (let i = this.scrollSpyBoxes.length - 1; i >= 0; i--) {
            const box = this.scrollSpyBoxes[i];

            if (box.offsetTop > scrollTop - window.innerHeight / 1.3) {
                visibleBox = box;
            }
        }

        if (visibleBox) {
            const link = this.scrollSpyLinks[this.scrollSpyBoxes.indexOf(visibleBox)];
            link.classList.add('isActive');
            this.moveUnderline({target: link});
        }
    }
})
