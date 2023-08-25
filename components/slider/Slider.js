oc.registerControl('slider', class extends oc.ControlBase {
    connect() {
        this.images.forEach((image) => {
            image.addEventListener('load', this.proxy(this.checkLoadedImage));
        });

        addEventListener('resize', this.proxy(this.resize));

        if (this.options.arrows) {
            this.listen('click', '.Slider-arrow--prev', this.prev);
            this.listen('click', '.Slider-arrow--next', this.next);
        }

        if (!this.options.disableDrag) {
            this.listen('touchstart', '.Slider-container', this.touchStart);
            this.listen('touchmove', '.Slider-container', this.touchMove);
            this.listen('touchend', '.Slider-container', this.touchEnd);

            this.listen('mousedown', '.Slider-container', this.dragStart);
            this.listen('mousemove', '.Slider-container', this.dragMove);
            this.listen('mouseup', '.Slider-container', this.dragEnd);
            this.listen('mouseleave', '.Slider-container', this.dragEnd);
        }

        if (this.options.dots) {
            this.listen('click', '.Slider-dot', (e) => {
                this.setSlide(e.target.getAttribute('data-slide'));
            });
        }

        if (this.options.syncWith) {
            if (this.options.syncTranslate) {
                addEventListener(`fwcSlider${this.options.syncWith}Move`, this.proxy(this.updateSyncTranslate));
                addEventListener(`fwcSlider${this.options.syncWith}MoveEnd`, this.proxy(this.updateSyncTranslateEnd));
            }

            addEventListener(`fwcSlider${this.options.syncWith}Change`, this.proxy(this.updateSync));
        }
    }

    disconnect() {
        removeEventListener('resize', this.proxy(this.resize));

        this.images.forEach((image) => {
            removeEventListener('load', this.proxy(this.checkLoadedImage));
        });

        if (this.options.syncWith) {
            if (this.options.syncTranslate) {
                removeEventListener(`fwcSlider${this.options.syncWith}Move`, this.proxy(this.updateSyncTranslate));
                removeEventListener(`fwcSlider${this.options.syncWith}MoveEnd`, this.proxy(this.updateSyncTranslateEnd));
            }

            removeEventListener(`fwcSlider${this.options.syncWith}Change`, this.proxy(this.updateSync));
        }
    }

    init() {
        this.el = this.element.querySelector('.Slider');
        this.id = this.element.getAttribute('id');
        this.container = this.el.querySelector('.Slider-container');
        this.track = this.el.querySelector('.Slider-track');
        this.slides = this.el.querySelectorAll('.Slider-slide');
        this.dots = this.el.querySelectorAll('.Slider-dot');

        this.images = this.el.querySelectorAll('img');
        this.numImagesLoaded = 0;

        this.arrowPrev = this.el.querySelector('.Slider-arrow--prev');
        this.arrowNext = this.el.querySelector('.Slider-arrow--next');

        this.options = Object.assign({
            perView: 2.5,
            gap: 30,
            transitionDuration: '0.3s',
            focusedSlidePosition: 'center', // left, center, right
            startIndex: 0,
            breakpoints: {
                768: {
                    perView: 1,
                },
                1024: {
                    perView: 1,
                }
            },
            syncWith: null,
            autoplay: false,
            loop: true,
            arrows: true,
            dots: true,
            mode: 'slide', // slide, fade
            disableDrag: false
        }, JSON.parse(this.config.options));

        if (this.options.autoplay) {
            this.options.loop = true;
        }

        this.initialOptions = Object.assign({}, this.options);

        this.currentBreakpoint = 9999;

        this.currentSlide = 0;

        this.touchThreshold = 80;
        this.currentTranslate = 0;

        this.offset = 0;

        this.dragging = false;
        this.dragThreshold = 20;

        this.totalSlides = this.slides.length;

        if (this.options.perView > 1) {
            this.totalSlides = this.slides.length + this.options.perView - 1;
        }

        // if (this.options.syncWith) {
        //     const parent = document.getElementById(this.options.syncWith);
        //     const sliderEl = parent.querySelector('.Slider');
        // }

        this.setup();

        this.setSlide(this.options.startIndex);

        if (this.options.autoplay) {
            this.startAutoplay();
        }
    }

    setup() {
        if (this.options.breakpoints) {
            const breakpoints = Object.keys(this.options.breakpoints).sort((a, b) => a - b);
            const foundBreakpoint = breakpoints.find((breakpoint) => window.innerWidth <= breakpoint);

            if (foundBreakpoint && foundBreakpoint !== this.currentBreakpoint) {
                this.options = Object.assign(this.options, this.options.breakpoints[foundBreakpoint]);
                this.currentBreakpoint = foundBreakpoint;
            } else if (!foundBreakpoint) {
                this.options = Object.assign(this.options, this.initialOptions);
                this.currentBreakpoint = 9999;
            }
        }

        if (this.options.mode === 'fade') {
            this.el.classList.add('Slider--fade');
            this.track.style.height = `${this.slides[this.currentSlide].offsetHeight}px`;
        }

        this.track.style.gap = `${this.options.gap}px`;
        this.track.style.transitionDuration = this.options.transitionDuration;
        this.slideWidth = (this.track.offsetWidth - this.options.gap * this.options.perView) / this.options.perView;
        this.moveAmount = this.slideWidth + this.options.gap;

        this.slides.forEach((slide) => {
            slide.style.flexBasis = `${this.slideWidth}px`;
        });

        if (this.options.focusedSlidePosition === 'center') {
            this.offset = (this.el.offsetWidth - this.moveAmount) / 2;
            this.totalSlides = this.slides.length + this.options.perView - 1;
        } else if (this.options.focusedSlidePosition === 'right') {
            this.offset = this.el.offsetWidth - this.moveAmount;
            this.totalSlides = this.slides.length + this.options.perView - 1;
        }

        this.update();
    }

    resize() {
        window.requestAnimationFrame(() => {
            this.setup();
        });
    }

    dragStart(e) {
        this.currentTranslate = this.currentSlide * -this.moveAmount;
        this.dragStartX = e.clientX;
        this.dragging = true;
    }

    dragMove(e) {
        if (this.dragging) {
            this.dragMoveX = e.clientX;

            if (this.dragMoveX - this.dragStartX > this.dragThreshold || this.dragMoveX - this.dragStartX < -this.dragThreshold) {
                this.move('drag');
            }
        }
    }

    dragEnd(e) {
        if (!this.dragging) return;
        this.dragging = false;
        this.track.style.transitionDuration = this.options.transitionDuration;
        if (this.dragMoveX - this.dragStartX > this.touchThreshold) {
            this.prev();
        } else if (this.dragMoveX - this.dragStartX < -this.touchThreshold) {
            this.next();
        } else {
            this.update();
        }

        this.dispatch(`fwcSlider${this.options.id}MoveEnd`, {target: window, prefix: false})
    }

    touchStart(e) {
        this.currentTranslate = this.currentSlide * -this.moveAmount;
        this.touchStartX = e.touches[0].clientX;
    }

    touchMove(e) {
        this.touchMoveX = e.touches[0].clientX;
        this.move('touch');
    }

    touchEnd(e) {
        this.track.style.transitionDuration = this.options.transitionDuration;
        if (this.touchMoveX - this.touchStartX > this.touchThreshold) {
            this.prev();
        } else if (this.touchMoveX - this.touchStartX < -this.touchThreshold) {
            this.next();
        } else {
            this.update();
        }

        window.dispatchEvent(new CustomEvent(`fwcSlider${this.id}MoveEnd`));
    }

    move(type, value = 0, syncedPerView = 1) {
        let translate;
        this.track.style.transitionDuration = '0s';

        if (type === 'translate') {
            translate = value * (syncedPerView / this.options.perView);
        } else if (type === 'touch') {
            translate =  this.currentTranslate + (this.touchMoveX - this.touchStartX);
        } else {
            translate = this.currentTranslate + (this.dragMoveX - this.dragStartX);
        }

        this.track.style.transform = `translateX(${translate}px)`;

        if (type !== 'translate') {
            this.dispatch(`fwcSlider${this.options.id}Move`, {target: window, prefix: false, detail: {translate: translate, perView: this.options.perView}});
        }
    }

    startAutoplay() {
        this.autoplayInterval = setInterval(() => {
            this.next();
        }, this.options.autoplay);
    }

    next() {
        if (this.currentSlide === Math.ceil( this.totalSlides - this.options.perView)) {
            if (this.options.loop) {
                this.currentSlide = this.options.startIndex;
            }
            this.update();
            return;
        }

        this.currentSlide++;
        this.update();
    }

    prev() {
        const zeroIndex = this.options.startIndex;
        if (this.currentSlide === zeroIndex) {
            if (this.options.loop) {
                this.currentSlide = Math.ceil( this.totalSlides - this.options.perView);
            }
            this.update();
            return;
        }

        this.currentSlide--;
        this.update();
    }

    setSlide(index, fireEvent = true) {
        if (index < 0) {
            index = 0;
        } else if (index > Math.ceil(this.totalSlides - this.options.perView)) {
            index = this.totalSlides - this.options.perView;
        }

        this.currentSlide = index;

        this.update(fireEvent);
    }

    checkLoadedImage() {
        this.numImagesLoaded++;

        if (this.numImagesLoaded === this.images.length) {
            this.setup();
        }
    }

    update(fireEvent = true) {
        if (this.options.mode === 'slide') {
            const translate = `translateX(${((this.moveAmount) * this.currentSlide - this.offset) * -1}px)`;
            this.track.style.transform = translate;
        }

        this.slides.forEach((slide) => {
            slide.classList.remove('isActive');
            slide.classList.remove('isVisible');
        });

        this.slides[this.currentSlide].classList.add('isActive');

        if (this.options.perView > 1) {
            for (let i = 0; i < this.options.perView; i++) {
                const visibleSlide = this.slides[this.currentSlide + i];
                if (visibleSlide) {
                    visibleSlide.classList.add('isVisible');
                }
            }
        } else {
            this.slides[this.currentSlide].classList.add('isVisible');
        }

        if (this.options.mode === 'fade') {
            const slideHeight = `${this.slides[this.currentSlide].offsetHeight}px`;
            if (this.track.style.height > slideHeight) {
                this.track.style.transitionDelay = this.options.transitionDuration;
            } else {
                this.track.style.transitionDelay = '0s';
            }

            this.track.style.height = slideHeight;
        }

        if (this.options.dots) {
            this.dots.forEach((dot) => {
                dot.classList.remove('isActive');
            });

            this.dots[this.currentSlide].classList.add('isActive');
        }

        if (this.options.arrows && !this.options.loop) {
            this.arrowPrev.classList.remove('isDisabled');
            this.arrowNext.classList.remove('isDisabled');

            if (this.currentSlide === 0) {
                this.arrowPrev.classList.add('isDisabled');
            } else if (this.currentSlide === Math.ceil( this.totalSlides - this.options.perView)) {
                this.arrowNext.classList.add('isDisabled');
            }
        }

        if (fireEvent) {
            this.dispatch(`fwcSlider${this.options.id}Change`, {target: window, prefix: false, detail: {currentSlide: this.currentSlide}});
        }
    }

    updateSync(e) {
        this.setSlide(e.detail.currentSlide, false);
    }

    updateSyncTranslate(e) {
        this.move('translate', e.detail.translate, e.detail.perView);
    }

    updateSyncTranslateEnd(e) {
        this.track.style.transitionDuration = this.options.transitionDuration;
    }
});
