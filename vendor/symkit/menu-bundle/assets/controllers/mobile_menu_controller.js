import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["menu", "backdrop", "panel"];

    connect() {
        // Ensure initial state
        if (this.hasMenuTarget) {
            this.menuTarget.style.display = 'none';
        }
    }

    toggle() {
        if (this.menuTarget.style.display === 'none') {
            this.open();
        } else {
            this.close();
        }
    }

    open() {
        this.menuTarget.style.display = 'block';

        // Use requestAnimationFrame to ensure the display change is processed before adding classes
        requestAnimationFrame(() => {
            this.backdropTarget.classList.add('opacity-100');
            this.backdropTarget.classList.remove('opacity-0');

            this.panelTarget.classList.add('translate-x-0');
            this.panelTarget.classList.remove('-translate-x-full');
        });
    }

    close() {
        this.backdropTarget.classList.remove('opacity-100');
        this.backdropTarget.classList.add('opacity-0');

        this.panelTarget.classList.remove('translate-x-0');
        this.panelTarget.classList.add('-translate-x-full');

        // Wait for transitions to finish before hiding
        setTimeout(() => {
            this.menuTarget.style.display = 'none';
        }, 300);
    }
}
