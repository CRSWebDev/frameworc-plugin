oc.registerControl('imageStrip', class extends oc.ControlBase {
    init() {
        this.options = Object.assign({
            intervalDelay: 20,
            stopOnHover: false,
            direction: 'toLeft'
        }, JSON.parse(this.config.options));

        this.wrapper = this.element.querySelector('.ImageStrip-wrapper');
        this.container = this.element.querySelector('.ImageStrip-container');

        this.gap = Number(getComputedStyle(this.container).gap.replace('px', ''));
        this.offset = 0;

        this.images = this.container.querySelectorAll('.ImageStrip-image');

        this.interval = null;

        this.totalImages = this.images.length;
        this.initialImages = this.totalImages;
        this.fillImageIndex = 0;
        this.offset = 0;
        this.imagesLoaded = 0;
        this.isPaused = false;
    }

    connect() {
        this.startAfterImagesLoaded();

        if (this.options.stopOnHover) {
            this.listen('mouseenter', '.ImageStrip-wrapper', this.pause);
            this.listen('mouseleave', '.ImageStrip-wrapper', this.unpause);
        }
    }

    disconnect() {
        clearInterval(this.interval);
    }

    startAfterImagesLoaded() {
        this.images = this.container.querySelectorAll('.ImageStrip-image');

        this.images.forEach((image) => {
            if (image.complete) {
                this.imagesLoaded++;
                this.start();
            } else if (image.naturalWidth > 0) {
                this.imagesLoaded++;
                this.start();
            } else {
                image.addEventListener('load', () => {
                    this.imagesLoaded++;
                    this.start();
                });
            }
        });
    }

    start() {
        if (this.imagesLoaded === this.totalImages) {
            this.fillImages();
            this.autoplay();
        }
    }

    fillImages() {
        this.totalImages = this.images.length;
        this.initialImages = this.totalImages;
        this.fillImageIndex = 0;
        this.offset = 0;
        this.imagesLoaded = 0;

        let imagesWidth = 0
        for (let i = 0; i < this.images.length; i++) {
            imagesWidth += this.images[i].offsetWidth + this.gap;
        }

        if (imagesWidth < this.wrapper.offsetWidth + this.images[0].offsetWidth + this.gap) {
            for (let i = 0; i < this.initialImages; i++) {
                this.copyImage();
            }
        }
    }

    copyImage() {
        const image = this.images[this.fillImageIndex];
        this.container.insertAdjacentHTML('beforeend', image.outerHTML);

        this.images = this.container.querySelectorAll('.ImageStrip-image');
        this.fillImageIndex++;
    }

    pause() {
        this.isPaused = true;
    }

    unpause() {
        this.isPaused = false;
    }

    autoplay() {
        const wrapperWidth = this.wrapper.offsetWidth;
        const containerWidth = this.container.offsetWidth;

        clearInterval(this.interval);

        let offset = 0;
        this.interval = setInterval(() => {
            if (this.isPaused) {
                return;
            }

            if (this.options.direction === 'toLeft') {
                this.offset = this.offset + 1;
            } else {
                this.offset = this.offset - 1;
            }

            this.orderImages()

            this.container.style.transform = `translateX(-${this.offset}px)`;
        }, this.options.intervalDelay);
    }

    orderImages() {
        const firstImage = this.container.querySelector('.ImageStrip-image:first-child');
        const firstImageWidth = firstImage.offsetWidth;

        if (firstImageWidth + this.gap < this.offset) {
            this.container.insertAdjacentHTML('beforeend', firstImage.outerHTML);
            firstImage.remove();
            this.offset = 0;
        }
    }
});
