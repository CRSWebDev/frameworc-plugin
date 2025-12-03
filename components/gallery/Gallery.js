oc.registerControl('gallery', class extends oc.ControlBase {
    init() {
        this.images = this.element.querySelectorAll('.Gallery-imageHolder img');
        this.columns = this.element.querySelectorAll('.Gallery-column');
        this.wrapper = this.element.querySelector('.Gallery-wrapper');
        this.maxLoadImages = 99999;

        this.unloadedImages = [];
        this.loadedImages = 0;
    }

    connect() {
        if (this.images.length < this.columns.length * 2) {
            this.wrapper.classList.remove('Gallery-wrapper--fewImages');
            this.maxLoadImages = this.columns.length;
        } else {
            this.wrapper.classList.add('Gallery-wrapper--fewImages');
            this.maxLoadImages = 99999;
        }

        this.loadImages();

        this.listen('click', '.Gallery-button .Button', this.loadMoreImages);
    }

    loadImages() {
        this.loadedImages = 0;
        this.images.forEach((image) => {
            image.addEventListener('load', () => {
                if (this.loadedImages >= this.maxLoadImages) {
                    this.unloadedImages.push(image);
                    return;
                }
                this.loadedImages++;
                this.distributeImage(image);
            });

            if (image.complete) {
                if (this.loadedImages >= this.maxLoadImages) {
                    this.unloadedImages.push(image);
                    return;
                }
                this.loadedImages++;
                this.distributeImage(image);
            }
        });
    }

    loadMoreImages() {
        const imagesToLoad = this.unloadedImages;
        imagesToLoad.forEach((image) => {
            this.loadedImages++;
            this.distributeImage(image);
        });

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

        if (this.loadedImages === this.maxLoadImages) {
            console.log('asdf')
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
