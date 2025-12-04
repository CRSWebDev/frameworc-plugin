oc.registerControl('gallery', class extends oc.ControlBase {
    init() {
        this.images = this.element.querySelectorAll('.Gallery-imageHolder img');
        this.imagesToLoad = Array.from(this.element.querySelectorAll('.Gallery-imageHolder img'));
        this.columns = this.element.querySelectorAll('.Gallery-column');
        this.wrapper = this.element.querySelector('.Gallery-wrapper');
        this.maxLoadImages = 99999;

        this.unloadedImages = [];
        this.loadedImagesNum = 0;
    }

    connect() {
        if (this.images.length > this.columns.length * 2) {
            this.wrapper.classList.remove('Gallery-wrapper--fewImages');
            this.maxLoadImages = this.columns.length * 2;
        } else {
            this.wrapper.classList.add('Gallery-wrapper--fewImages');
            this.maxLoadImages = 99999;
        }

        this.loadImages();

        this.listen('click', '.Gallery-button .Button', this.loadMoreImages);
    }

    loadImages() {
        if (this.imagesToLoad.length === 0) {
            return;
        }

        const image = this.imagesToLoad.shift();

        image.addEventListener('load', () => {
            if (this.loadedImagesNum >= this.maxLoadImages) {
                this.unloadedImages.push(image);
                this.loadImages();
                return;
            }

            this.loadedImagesNum++;
            this.distributeImage(image);
            setTimeout(() => {
                this.loadImages();
            }, 100);
        });

        if (image.complete) {
            if (this.loadedImagesNum >= this.maxLoadImages) {
                this.unloadedImages.push(image);
                this.loadImages();
                return;
            }

            this.loadedImagesNum++;
            this.distributeImage(image);
            setTimeout(() => {
                this.loadImages();
            }, 100);
        }
    }

    loadMoreImages() {
        this.imagesToLoad = this.unloadedImages;
        this.maxLoadImages = 99999;
        this.loadImages();

        this.wrapper.classList.add('Gallery-wrapper--allImages');
    }

    distributeImage(image) {
        let shortestColumn = this.columns[0];

        this.columns.forEach((column) => {
            if (column.offsetHeight < shortestColumn.offsetHeight) {
                shortestColumn = column;
            }
        });

        const wrapper = document.createElement('div');
        wrapper.classList.add('Gallery-imageWrapper');
        wrapper.appendChild(image);

        shortestColumn.appendChild(wrapper);

        if (this.loadedImagesNum === this.maxLoadImages) {
            // tallest column height
            let tallestHeight = 0;
            this.columns.forEach((column) => {
                if (column.offsetHeight > tallestHeight) {
                    tallestHeight = column.offsetHeight;
                }
            });

            this.wrapper.style.height = `${tallestHeight}px`;
        }
    }
});
