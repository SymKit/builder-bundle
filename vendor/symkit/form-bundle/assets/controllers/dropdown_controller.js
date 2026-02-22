import { Controller } from '@hotwired/stimulus';
import { useTransition } from 'stimulus-use';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["menu", "button"];

    connect() {
        useTransition(this, {
            element: this.menuTarget,
            enterActive: 'transition ease-out duration-100',
            enterFrom: 'transform opacity-0 scale-95',
            enterTo: 'transform opacity-100 scale-100',
            leaveActive: 'transition ease-in duration-75',
            leaveFrom: 'transform opacity-100 scale-100',
            leaveTo: 'transform opacity-0 scale-95',
            hiddenClass: 'hidden',
        });
    }

    disconnect() {
        if (this.leave) {
            this.leave();
        }
    }

    toggle() {
        this.toggleTransition();
    }

    hide(event) {
        if (this.element.contains(event.target) === false && !this.menuTarget.classList.contains('hidden')) {
            this.leave();
        }
    }
}
