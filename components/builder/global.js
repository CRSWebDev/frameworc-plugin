oc.registerControl('global', class extends oc.ControlBase {
    init() {
        this.lightbox = this.element.querySelector('#lightbox');
        this.lightboxImage = this.lightbox.querySelector('.Lightbox-image');
        this.lightboxGallery = [];
        this.lightboxLoader = this.lightbox.querySelector('.Lightbox-loader');
        this.activeIndex = 0;
        this.dragThreshold = 30;
        this.dragStart = 0;
    }

    connect() {
        this.listen('mousedown', '[data-lightbox]', this.mouseDown);
        this.listen('mouseup', '[data-lightbox]', this.mouseUp);
        this.listen('click', '.Lightbox-close', this.hideLightbox);
        this.listen('click', '.Lightbox-prev', this.prevImage);
        this.listen('click', '.Lightbox-next', this.nextImage);
        this.listen('click', '.Lightbox-overlay', this.hideLightbox);

        addEventListener('keydown', this.proxy(this.keydownHandler));
    }

    disconnect() {
        removeEventListener('keydown', this.proxy(this.keydownHandler));
    }

    mouseDown(e) {
        this.isDrag = false;
        this.dragStart = {
            x: e.clientX,
            y: e.clientY,
        };
    }

    mouseUp(e) {

        if (
            this.dragStart.x < e.clientX + this.dragThreshold
            && this.dragStart.x > e.clientX - this.dragThreshold
            && this.dragStart.y < e.clientY + this.dragThreshold
            && this.dragStart.y > e.clientY - this.dragThreshold
        ) {
            this.showLightbox(e);
        }
    }

    keydownHandler(e) {
        if (this.lightbox.classList.contains('isActive')) {
            e.preventDefault();

            switch (e.key) {
                case 'Escape':
                    this.hideLightbox();
                    break;
                case 'ArrowLeft':
                    this.prevImage();
                    break;
                case 'ArrowRight':
                    this.nextImage();
                    break;
            }
        }
    }

    showLightbox(e) {
        e.preventDefault();

        document.body.style.overflow = 'hidden';

        const images = e.target.closest('[data-gallery]').querySelectorAll('[data-lightbox]');
        this.lightboxGallery = Array.from(images).map((image) => image.getAttribute('data-lightbox'));

        const target = e.target.getAttribute('data-lightbox');
        this.activeIndex = e.target.getAttribute('data-lightbox-index');

        this.lightboxImage.src = target;
        this.lightbox.classList.add('isActive');
    }

    hideLightbox() {
        document.body.style.overflow = '';

        this.lightbox.classList.remove('isActive');
    }

    prevImage() {
        if (this.activeIndex > 0) {
            this.activeIndex--;
        } else {
            this.activeIndex = this.lightboxGallery.length - 1;
        }

        this.lightboxImage.classList.add('isLoading');
        this.lightboxLoader.classList.add('isActive');
        setTimeout(() => {
            this.lightboxImage.src = this.lightboxGallery[this.activeIndex];

            this.lightboxImage.onload = () => {
                this.lightboxLoader.classList.remove('isActive');
                this.lightboxImage.classList.remove('isLoading');
            };
        }, 100);
    }

    nextImage() {
        if (this.activeIndex < this.lightboxGallery.length - 1) {
            this.activeIndex++;
        } else {
            this.activeIndex = 0;
        }

        this.lightboxImage.classList.add('isLoading');
        this.lightboxLoader.classList.add('isActive');
        setTimeout(() => {
            this.lightboxImage.src = this.lightboxGallery[this.activeIndex];

            this.lightboxImage.onload = () => {
                this.lightboxLoader.classList.remove('isActive');
                this.lightboxImage.classList.remove('isLoading');
            };
        }, 200);
    }
});

class FadeInElement {
    constructor(element, childSelector = null) {
        this.element = element;
        this.childSelector = childSelector;

        this.delay = 200;

        this.isVisible = false;

        this.childElememts = this.element.querySelectorAll(this.childSelector);

        if (this.childElememts.length === 0) {
            this.childElememts = [this.element];
            this.element.classList.add('Section--noChildren');
        }

        if (this.childElememts.length > 5) {
            this.delay = 50;
        }

        this.offsetTop = this.element.offsetTop;

        this.element.classList.add('isInitialized');
    }

    fadeIn() {
        this.isVisible = true;

        this.childElememts.forEach((child, index) => {
            setTimeout(() => {
                child.classList.add('isVisible');
            }, this.delay * index);
        });
    }

    fadeInOnScroll() {
        const offset = window.scrollY + window.innerHeight - window.innerHeight / 3;

        if (offset > this.offsetTop && !this.isVisible) {
            this.fadeIn();
        }
    }
}

let elementInstances = [];

addEventListener('page:loaded', function() {
    elementInstances = [];

    const elements = document.querySelectorAll('.Builder-container--animate');

    elements.forEach((element) => {
        const instance = new FadeInElement(element, '.Tiles-tile, .BlogList-item, .Accordion-item, .Gallery-column');
        window.addEventListener('scroll', instance.fadeInOnScroll.bind(instance));
        instance.fadeInOnScroll();
        elementInstances.push(instance);
    });
})

addEventListener('page:visit', function(event) {
    elementInstances.forEach((instance) => {
        window.removeEventListener('scroll', instance.fadeInOnScroll.bind(instance));
    });
});
