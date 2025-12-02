oc.registerControl('tabs', class extends oc.ControlBase {
    connect() {
        this.images.forEach((image) => {
            image.addEventListener('load', this.proxy(this.checkLoadedImage));
        });

        addEventListener('resize', this.proxy(this.resize));

        this.listen('click', '.Tabs-dot', (e) => {
            this.setSlide(e.target.getAttribute('data-slide'));
        });
    }

    disconnect() {
        removeEventListener('resize', this.proxy(this.resize));

        this.images.forEach((image) => {
            removeEventListener('load', this.proxy(this.checkLoadedImage));
        });
    }

    init() {
        this.el = this.element.querySelector('.Tabs');
        this.id = this.element.getAttribute('id');
        this.container = this.el.querySelector('.Tabs-container');
        this.track = this.el.querySelector('.Tabs-track');
        this.slides = this.el.querySelectorAll('.Tabs-slide');
        this.dots = this.el.querySelectorAll('.Tabs-dot');

        this.images = this.el.querySelectorAll('img');
        this.numImagesLoaded = 0;

        this.arrowPrev = this.el.querySelector('.Tabs-arrow--prev');
        this.arrowNext = this.el.querySelector('.Tabs-arrow--next');

        this.options = Object.assign({
            mode: 'fade'
        }, JSON.parse(this.config.options));

        this.currentSlide = 0;
        this.totalSlides = this.slides.length;

        this.setup();

        this.setSlide(this.options.startIndex);
    }

    setup() {
        if (this.options.mode === 'fade') {
            this.el.classList.add('Tabs--fade');
            this.track.style.height = `${this.slides[this.currentSlide].offsetHeight}px`;
        }

        this.update();
    }

    resize() {
        window.requestAnimationFrame(() => {
            this.setup();
        });
    }

    setSlide(index) {
        this.currentSlide = parseInt(index);

        this.update();
    }

    checkLoadedImage() {
        this.numImagesLoaded++;

        if (this.numImagesLoaded === this.images.length) {
            this.setup();
        }
    }

    update() {
        this.slides.forEach((slide) => {
            slide.classList.remove('isActive');
            slide.classList.remove('isVisible');
        });

        this.slides[this.currentSlide].classList.add('isActive');

        const slideHeight = `${this.slides[this.currentSlide].offsetHeight}px`;
        if (this.track.style.height > slideHeight) {
            this.track.style.transitionDelay = this.options.transitionDuration;
        } else {
            this.track.style.transitionDelay = '0s';
        }

        this.track.style.height = slideHeight;

        this.dots.forEach((dot) => {
            dot.classList.remove('isActive');
        });

        this.dots[this.currentSlide].classList.add('isActive');
    }
});
