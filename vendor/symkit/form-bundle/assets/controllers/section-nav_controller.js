import { Controller } from '@hotwired/stimulus';

/**
 * Controller for section navigation in sectioned forms.
 * Handles:
 * - Highlighting active section in nav based on scroll position
 * - Smooth scrolling when clicking nav links
 */
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['navList', 'navLink', 'section'];

    connect() {
        this.setupIntersectionObserver();
    }

    disconnect() {
        if (this.observer) {
            this.observer.disconnect();
        }
    }

    setupIntersectionObserver() {
        const options = {
            root: null,
            rootMargin: '-100px 0px -50% 0px',
            threshold: 0
        };

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.setActiveSection(entry.target.id);
                }
            });
        }, options);

        this.sectionTargets.forEach(section => {
            this.observer.observe(section);
        });
    }

    setActiveSection(sectionId) {
        this.navLinkTargets.forEach(link => {
            const isActive = link.dataset.sectionId === sectionId;

            if (isActive) {
                link.classList.remove('border-transparent', 'text-slate-600', 'dark:text-gray-400');
                link.classList.add('border-indigo-600', 'bg-indigo-50', 'dark:bg-indigo-900/30', 'text-indigo-700', 'dark:text-indigo-300', 'font-medium');
            } else {
                link.classList.add('border-transparent', 'text-slate-600', 'dark:text-gray-400');
                link.classList.remove('border-indigo-600', 'bg-indigo-50', 'dark:bg-indigo-900/30', 'text-indigo-700', 'dark:text-indigo-300', 'font-medium');
            }
        });
    }

    scrollTo(event) {
        event.preventDefault();
        const link = event.currentTarget;
        const sectionId = link.dataset.sectionId;
        const section = document.getElementById(sectionId);

        if (section) {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}
