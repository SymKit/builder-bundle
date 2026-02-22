import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['content', 'container'];
    static values = {
        title: { type: String, default: 'On this page' },
    }

    connect() {
        if (!this.hasContentTarget || !this.hasContainerTarget) return;

        this.headings = Array.from(this.contentTarget.querySelectorAll('h2, h3, h4'));
        this.render();
        this.setupIntersectionObserver();
    }

    render() {
        if (this.headings.length === 0) {
            this.containerTarget.classList.add('hidden');
            return;
        }

        const nav = document.createElement('nav');
        nav.className = 'sticky top-24';

        const title = document.createElement('h2');
        title.className = 'text-base font-bold text-slate-900 dark:text-white mb-3';
        title.textContent = this.titleValue;

        const ul = document.createElement('ul');
        ul.className = 'space-y-2';

        this.headings.forEach((heading, index) => {
            if (!heading.id) {
                heading.id = `toc-${index}-${heading.textContent.toLowerCase().replace(/\s+/g, '-')}`;
            }

            const li = document.createElement('li');
            const level = parseInt(heading.tagName.substring(1));

            // Matching the indentation from the image
            const marginClass = level === 3 ? 'ml-6' : (level === 4 ? 'ml-12' : '');
            if (marginClass) li.className = marginClass;

            const a = document.createElement('a');
            a.href = `#${heading.id}`;
            a.textContent = heading.textContent;
            a.dataset.tocId = heading.id;
            a.className = 'block text-[15px] text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors duration-200';

            li.appendChild(a);
            ul.appendChild(li);
        });

        nav.appendChild(title);
        nav.appendChild(ul);

        this.containerTarget.innerHTML = '';
        this.containerTarget.appendChild(nav);
    }

    setupIntersectionObserver() {
        const options = {
            rootMargin: '-100px 0px -70% 0px',
            threshold: 0
        };

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.activate(entry.target.id);
                }
            });
        }, options);

        this.headings.forEach(heading => this.observer.observe(heading));
    }

    activate(id) {
        const links = this.containerTarget.querySelectorAll('a');
        links.forEach(link => {
            if (link.dataset.tocId === id) {
                link.classList.add('text-sky-500', 'dark:text-sky-400', 'font-medium');
                link.classList.remove('text-slate-500', 'dark:text-slate-400');
            } else {
                link.classList.remove('text-sky-500', 'dark:text-sky-400', 'font-medium');
                link.classList.add('text-slate-500', 'dark:text-slate-400');
            }
        });
    }

    disconnect() {
        if (this.observer) {
            this.observer.disconnect();
        }
    }
}
