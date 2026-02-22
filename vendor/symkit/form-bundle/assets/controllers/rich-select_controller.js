import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['trigger', 'menu', 'searchInput', 'option', 'hiddenInput'];

    async connect() {
        this.isOpen = false;
        this.selectedIndex = -1;
        this.component = await getComponent(this.element);

        // Listen for clicks outside to close
        this.boundHandleOutsideClick = this.handleOutsideClick.bind(this);
        document.addEventListener('click', this.boundHandleOutsideClick);
    }

    disconnect() {
        document.removeEventListener('click', this.boundHandleOutsideClick);
    }

    toggle() {
        this.isOpen ? this.close() : this.open();
    }

    open() {
        this.isOpen = true;
        this.element.style.zIndex = '999';
        this.menuTarget.classList.remove('hidden');
        this.triggerTarget.setAttribute('aria-expanded', 'true');

        if (this.hasSearchInputTarget) {
            setTimeout(() => this.searchInputTarget.focus(), 50);
        }
    }

    close() {
        this.isOpen = false;
        this.element.style.zIndex = '';
        this.menuTarget.classList.add('hidden');
        this.triggerTarget.setAttribute('aria-expanded', 'false');
        this.selectedIndex = -1;
        this.resetHighlights();
    }

    handleOutsideClick(event) {
        if (!this.element.contains(event.target) && this.isOpen) {
            this.close();
        }
    }

    unselect() {
        if (this.component) {
            this.component.set('value', null);
            this.component.render();
        }
    }

    select(event) {
        const { value, label } = event.params;

        // Update selection in Live Component
        if (this.component) {
            this.component.set('value', value);
            this.component.set('searchQuery', ''); // Reset search on select
            this.component.render();
        }

        this.close();
        this.triggerTarget.focus();
    }

    handleKeydown(event) {
        if (!this.isOpen) return;

        switch (event.key) {
            case 'ArrowDown':
                event.preventDefault();
                this.navigate(1);
                break;
            case 'ArrowUp':
                event.preventDefault();
                this.navigate(-1);
                break;
            case 'Enter':
                event.preventDefault();
                if (this.selectedIndex >= 0) {
                    this.optionTargets[this.selectedIndex].click();
                }
                break;
            case 'Escape':
                this.close();
                this.triggerTarget.focus();
                break;
        }
    }

    navigate(direction) {
        const options = this.optionTargets;
        if (options.length === 0) return;

        this.selectedIndex += direction;

        if (this.selectedIndex < 0) this.selectedIndex = options.length - 1;
        if (this.selectedIndex >= options.length) this.selectedIndex = 0;

        this.highlight(options[this.selectedIndex]);
    }

    highlight(element) {
        this.resetHighlights();
        element.classList.add('bg-indigo-600', 'text-white');
        element.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }

    resetHighlights() {
        this.optionTargets.forEach(opt => {
            opt.classList.remove('bg-indigo-600', 'text-white');
        });
    }
}
