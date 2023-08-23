oc.registerControl('accordion', class extends oc.ControlBase {
    init() {
        this.$accordion = this.element.querySelector('.Accordion');
        this.$items = this.element.querySelectorAll('.Accordion-item');

        this.options = Object.assign({
            singleMode: false,
            activeItem: 0
        }, JSON.parse(this.config.options));

        this.itemsObj = {};

        this.$items.forEach((item, index) => {
            const obj = {
                item: item,
                headline: item.querySelector('.Accordion-headline'),
                container: item.querySelector('.Accordion-container'),
            }

            this.itemsObj[item.id] = obj;

            if (index === this.options.activeItem) {
                this.open(obj);
            }
        });
    }

    connect() {
        this.listen('click', '.Accordion-headline', this.toggleItem);
    }

    toggleItem(e) {
        const item = this.itemsObj[e.target.closest('.Accordion-item').getAttribute('id')];
        this.toggle(item);
    }

    toggle(item = false) {
        if (this.options.singleMode) {
            Object.keys(this.itemsObj).forEach((key) => {
                if (key !== item.item.id) {
                    const i = this.itemsObj[key];
                    i.item.classList.remove('isActive');
                    i.container.style.height = '0px';
                }
            });
        }

        if (item.item.classList.contains('isActive')) {
            this.close(item);
        } else {
            this.open(item);
        }
    }

    open(item) {
        const height = item.container.scrollHeight;
        item.container.style.height = `${height}px`;
        item.item.classList.add('isActive');
    }

    close(item) {
        item.item.classList.remove('isActive');
        item.container.style.height = '0px';
    }
});
