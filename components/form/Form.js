oc.registerControl('form', class extends oc.ControlBase {
    init() {
        this.$form = this.element.querySelector('form');
        this.resetFileButton = this.element.querySelector('.Form-fileReset');
        this.inputEl = this.element.querySelector('input[type="file"]');
        this.fileNamesContainer = this.element.querySelector('.Form-fileName');
        this.formControls = this.element.querySelectorAll('.Form-control');

        this.options = Object.assign({
            someOptions: 'someValue'
        }, JSON.parse(this.config.options));
    }

    connect() {
        this.listen('change', 'input[type="file"]', this.handleFileSelect);
        this.listen('click', '.Form-fileReset', this.handleFileReset);
        this.listen('click', 'a.Form-moreInfoLink', this.handleMoreInfo);
        addEventListener('ajax:request-error', this.proxy(this.handleAjaxError));
    }

    disconnect() {
        removeEventListener('ajax:request-error', this.proxy(this.handleAjaxError));
    }

    handleFileSelect(e) {
        const files = e.target.files;
        const fileNames = [];

        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                fileNames.push(files[i].name);
            }

            this.fileNamesContainer.innerHTML = fileNames.join(', ');

            this.resetFileButton.classList.add('isActive');
        } else {
            this.fileNamesContainer.innerHTML = '';
            this.resetFileButton.classList.remove('isActive');
        }
    }

    handleFileReset(e) {
        e.preventDefault();

        this.fileNamesContainer.innerHTML = '';
        this.inputEl.value = '';
        this.resetFileButton.classList.remove('isActive');
    }

    handleMoreInfo(e) {
        e.preventDefault();

        const target = e.target;
        const targetId = target.getAttribute('href').replace('#', '');
        const targetEl = document.getElementById(targetId);

        if (targetEl) {
            if (targetEl.classList.contains('isActive')) {
                targetEl.classList.remove('isActive');
            } else {
                targetEl.classList.add('isActive');
            }
        }
    }

    handleAjaxError(e) {
        this.formControls.forEach((el) => {
            el.classList.remove('hasError');
        });

        Object.keys(e.detail.message.X_OCTOBER_ERROR_FIELDS).forEach((key) => {
            if (key === 'altcha') {
                oc.flashMsg({
                    message: e.detail.message.X_OCTOBER_ERROR_MESSAGE,
                    type: 'error'
                });

                return;
            }

            const $el = this.$form.querySelector(`[data-form-field="${key}"]`);

            if ($el) {
                $el.classList.add('hasError');
            }
        });
    }
});
