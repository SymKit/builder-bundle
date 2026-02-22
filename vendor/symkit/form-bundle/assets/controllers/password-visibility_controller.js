import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['input', 'iconShow', 'iconHide'];

    disconnect() {
        // Nothing to disconnect explicitly here
    }

    toggle() {
        if (this.inputTarget.type === 'password') {
            this.inputTarget.type = 'text';
            this.iconShowTarget.classList.add('hidden');
            this.iconHideTarget.classList.remove('hidden');
        } else {
            this.inputTarget.type = 'password';
            this.iconShowTarget.classList.remove('hidden');
            this.iconHideTarget.classList.add('hidden');
        }
    }
}
