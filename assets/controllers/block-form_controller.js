import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['templateRadio', 'htmlRadio', 'templateField', 'htmlField', 'preview'];

    connect() {
        // Determine initial state based on which field has content
        const templateInput = this.templateFieldTarget.querySelector('input, textarea');
        const htmlInput = this.htmlFieldTarget.querySelector('textarea');

        const hasTemplate = templateInput && templateInput.value.trim() !== '';
        const hasHtml = htmlInput && htmlInput.value.trim() !== '';

        if (hasTemplate) {
            this.templateRadioTarget.checked = true;
            this.showTemplate();
        } else if (hasHtml) {
            this.htmlRadioTarget.checked = true;
            this.showHtml();
        } else {
            // Default to template if both empty
            this.templateRadioTarget.checked = true;
            this.showTemplate();
        }

        this.updatePreview();
    }

    toggleSource(event) {
        const selectedType = event.target.value;

        if (selectedType === 'template') {
            this.showTemplate();
        } else {
            this.showHtml();
        }
    }

    showTemplate() {
        this.templateFieldTarget.style.opacity = '1';
        this.templateFieldTarget.style.pointerEvents = 'auto';
        this.htmlFieldTarget.style.opacity = '0.4';
        this.htmlFieldTarget.style.pointerEvents = 'none';

        const templateInput = this.templateFieldTarget.querySelector('input, textarea');
        if (templateInput) {
            templateInput.removeAttribute('disabled');
        }

        const htmlInput = this.htmlFieldTarget.querySelector('textarea');
        if (htmlInput) {
            htmlInput.setAttribute('disabled', 'disabled');
        }
    }

    showHtml() {
        this.htmlFieldTarget.style.opacity = '1';
        this.htmlFieldTarget.style.pointerEvents = 'auto';
        this.templateFieldTarget.style.opacity = '0.4';
        this.templateFieldTarget.style.pointerEvents = 'none';

        const htmlInput = this.htmlFieldTarget.querySelector('textarea');
        if (htmlInput) {
            htmlInput.removeAttribute('disabled');
        }

        const templateInput = this.templateFieldTarget.querySelector('input, textarea');
        if (templateInput) {
            templateInput.setAttribute('disabled', 'disabled');
        }
    }

    updatePreview() {
        if (!this.hasPreviewTarget) return;

        const htmlInput = this.htmlFieldTarget.querySelector('textarea');
        if (htmlInput) {
            this.previewTarget.innerHTML = htmlInput.value;
        }
    }
}
