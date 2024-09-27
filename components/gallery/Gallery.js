oc.registerControl('gallery', class extends oc.ControlBase {
    init() {
        this.images = this.element.querySelectorAll('.Gallery-imageHolder img');
        this.columns = this.element.querySelectorAll('.Gallery-column');
    }

    connect() {
        this.loadImages();
    }

    loadImages() {
        this.images.forEach((image) => {
            image.addEventListener('load', () => {
                this.distributeImage(image);
            });
        });
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
    }
});
