import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["input", "button"];

    disconnect() {
        // Nothing to explicitly disconnect here
    }

    connect() {
        this.check();
    }

    check() {
        const url = this.inputTarget.value;
        if (url) {
            this.buttonTarget.classList.remove('opacity-0');
            this.buttonTarget.classList.add('opacity-100');
        } else {
            this.buttonTarget.classList.remove('opacity-100');
            this.buttonTarget.classList.add('opacity-0');
        }
    }

    open() {
        const url = this.inputTarget.value;
        if (url) {
            window.open(url, '_blank', 'noopener,noreferrer');
        }
    }
}
