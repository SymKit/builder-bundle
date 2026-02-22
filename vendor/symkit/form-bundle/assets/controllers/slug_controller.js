import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['source'];
    static values = {
        targetId: String, // ID of the target input
    }

    connect() {
        // Find the target input
        this.targetInput = null;

        if (this.hasTargetIdValue) {
            this.targetInput = document.getElementById(this.targetIdValue);
        }

        if (this.targetInput) {
            this.handleInput = this.handleInput.bind(this);
            this.targetInput.addEventListener('input', this.handleInput);

            this.initializeSourceValue();
        }
    }

    initializeSourceValue() {
        if (this.targetInput.value) {
            this.updateSource(this.targetInput.value);
        }
    }

    disconnect() {
        if (this.targetInput) {
            this.targetInput.removeEventListener('input', this.handleInput);
        }
    }

    handleInput(event) {
        this.updateSource(event.target.value);
    }

    updateSource(value) {
        if (!this.hasSourceTarget) {
            return;
        }

        this.sourceTarget.value = value;
        this.sourceTarget.dispatchEvent(new Event('input', { bubbles: true }));
        this.sourceTarget.dispatchEvent(new Event('change', { bubbles: true }));
    }
}
