oc.registerControl('slider', class extends oc.ControlBase {
    init() {
        this.id = this.element.getAttribute('id');
        this.dots = this.element.querySelectorAll('.Slider-dot');

        this.arrowPrev = this.element.querySelector('.Slider-arrow--prev');
        this.arrowNext = this.element.querySelector('.Slider-arrow--next');

        this.options = Object.assign({
            perPage: 2,
            gap: 30,
            focus: 0,
            start: 0,
            breakpoints: {
                768: {
                    perPage: 1,
                },
                1024: {
                    perPage: 2,
                }
            },
            omitEnd: true,
            perMove: 1,
            mediaQuery: 'max',
            autoplay: false,
            type: 'slide',
            arrows: true,
            dots: true,
            autoWidth: false,
            disableDrag: false,
            classes: {
                pagination: 'Slider-dots',
                page: 'Slider-dot',
            },
        }, JSON.parse(this.config.options));

        if (this.options.autoplay) {
            this.options.type = 'loop';
        }
    }

    connect() {
        this.splide = new Splide(this.element.querySelector('.splide'), this.options);

        window.fwcSliders = window.fwcSliders || {};
        window.fwcSliders[this.id] = this.splide;

        if (this.options.arrows) {
            this.listen('click', '.Slider-arrow--prev', this.prev);
            this.listen('click', '.Slider-arrow--next', this.next);
        }

        if (this.options.syncWith) {
            setTimeout(() => {
                this.splide.sync(window.fwcSliders[this.options.syncWith]);
                this.splide.mount();
            }, 100);
        } else {
            setTimeout(() => {
                this.splide.mount();
            }, 150);
        }
    }

    disconnect() {
        this.splide.destroy(true);

        window.fwcSliders[this.id] = null;
        delete window.fwcSliders[this.id];
    }

    prev() {
        this.splide.go('<');
    }

    next() {
        this.splide.go('>');
    }
});
