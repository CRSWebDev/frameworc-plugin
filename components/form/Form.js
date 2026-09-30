oc.registerControl('form', class extends oc.ControlBase {
    init() {
        this.$form = this.element.querySelector('form');
        this.formControls = this.element.querySelectorAll('.Form-control');

        this.options = Object.assign({
            someOptions: 'someValue'
        }, this.config.options ?? {});
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
        const container = e.target.closest('.Form-fileContainer');
        const fileNamesContainer = container.querySelector('.Form-fileName');
        const resetFileButton = container.querySelector('.Form-fileReset');
        const files = e.target.files;
        const fileNames = [];

        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                fileNames.push(files[i].name);
            }

            fileNamesContainer.innerHTML = fileNames.join(', ');

            resetFileButton.classList.add('isActive');
        } else {
            fileNamesContainer.innerHTML = '';
            resetFileButton.classList.remove('isActive');
        }
    }

    handleFileReset(e) {
        e.preventDefault();

        const container = e.target.closest('.Form-fileContainer');

        container.querySelector('.Form-fileName').innerHTML = '';
        container.querySelector('input[type="file"]').value = '';
        container.querySelector('.Form-fileReset').classList.remove('isActive');
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

        const errorFields = e.detail.message?.X_OCTOBER_ERROR_FIELDS ?? {};

        Object.keys(errorFields).forEach((errorKey) => {
            // Checkbox option errors are keyed as "name.0"
            const key = errorKey.split('.')[0];

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
