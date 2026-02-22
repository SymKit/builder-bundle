import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

export default class extends Controller {
    static values = {
        id: String
    }
    static targets = ['editor', 'htmlEditor', 'linkUrl', 'linkBlank', 'linkNofollow', 'linkConfig', 'toolbar']

    connect() {
        this.onSelectionChange = this.onSelectionChange.bind(this);
        document.addEventListener('selectionchange', this.onSelectionChange);
    }

    disconnect() {
        document.removeEventListener('selectionchange', this.onSelectionChange);
    }

    onFocus() {
        this.element.classList.add('is-editing');
    }

    onBlur() {
        // Delay hide to allow clicks on toolbar buttons
        setTimeout(() => {
            if (!this.element.contains(document.activeElement)) {
                this.element.classList.remove('is-editing');
            }
        }, 200);
    }

    async sync() {
        if (!this.hasEditorTarget) return;
        const component = await getComponent(this.element.closest('[data-controller="live"]'));

        // Default property for visual editor is 'content', unless overridden (e.g. for snippets which use 'html')
        const property = this.editorTarget.dataset.property || 'content';
        const data = { [property]: this.editorTarget.innerHTML };

        // Auto-detect snippet blocks to ensure 'html' is also updated for legacy compatibility
        if (this.editorTarget.classList.contains('snippet-preview')) {
            data.html = this.editorTarget.innerHTML;
        }

        component.action('updateBlockData', {
            id: this.idValue,
            data: data
        }, { debounce: 500 });
    }

    async syncHtml(event) {
        const component = await getComponent(this.element.closest('[data-controller="live"]'));
        const property = event.target.dataset.property || 'html';
        const value = event.target.value;

        component.action('updateBlockData', {
            id: this.idValue,
            data: { [property]: value }
        }, { debounce: 500 });
    }

    async syncProperty(event) {
        const component = await getComponent(this.element.closest('[data-controller="live"]'));
        const property = event.target.dataset.property;
        if (!property) return;

        const value = (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA' || event.target.tagName === 'SELECT')
            ? event.target.value
            : event.target.innerHTML;

        component.action('updateBlockData', {
            id: this.idValue,
            data: { [property]: value }
        }, { debounce: 500 });
    }

    format(event) {
        const cmd = event.params.cmd;
        const val = event.params.val || null;

        document.execCommand(cmd, false, val);
        this.editorTarget.focus();
        this.sync();
    }

    link() {
        const selection = window.getSelection();
        if (selection.isCollapsed) return;

        const url = window.prompt('Enter URL:');
        if (url) {
            document.execCommand('createLink', false, url);
            this.sync();
        }
    }

    unlink() {
        document.execCommand('unlink');
        this.sync();
        this.onSelectionChange();
    }

    color(event) {
        document.execCommand('foreColor', false, event.target.value);
        this.sync();
    }

    code() {
        const selection = window.getSelection();
        if (selection.isCollapsed) return;

        const range = selection.getRangeAt(0);
        const parent = range.commonAncestorContainer.parentElement;

        // rudimentary toggle check
        if (parent.tagName === 'CODE') {
            // unwrapping is hard without losing cursor or complex logic, 
            // but let's just support adding for now or simple replacement
            const text = document.createTextNode(parent.textContent);
            parent.parentNode.replaceChild(text, parent);
        } else {
            const code = document.createElement('code');
            code.className = "px-1.5 py-0.5 bg-gray-100 dark:bg-white/10 rounded font-mono text-sm text-pink-500 dark:text-pink-400";
            try {
                // This might be tricky if selection spans nodes. 
                // A safe way is using extractContents causing fragmentation but valid.
                code.appendChild(range.extractContents());
                range.insertNode(code);
            } catch (e) {
                // Fallback for simple text
                code.textContent = selection.toString();
                range.deleteContents();
                range.insertNode(code);
            }
        }
        this.sync();
    }

    async transformNode(event) {
        const component = await getComponent(this.element.closest('[data-controller="live"]'));
        const type = event.params.type;

        // If it's a block type (like 'quote' or 'infobox'), we could use transformBlock.
        // For now, let's just support paragraph/heading formatting via formatBlock in the toolbar.
        if (['h2', 'h3', 'h4', 'h5', 'p'].includes(type)) {
            document.execCommand('formatBlock', false, type === 'p' ? 'p' : type);
            this.sync();
        }
    }

    onSelectionChange() {
        if (!this.hasEditorTarget) return;

        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) return;

        const range = selection.getRangeAt(0);
        const container = range.commonAncestorContainer;
        const parent = container.nodeType === 3 ? container.parentElement : container;

        // Link detection
        const link = parent.closest('a');
        if (link && this.element.contains(link)) {
            this.showLinkConfig(link);
        } else {
            this.hideLinkConfig();
        }
    }

    showLinkConfig(link) {
        if (!this.hasLinkConfigTarget) return;
        this.linkConfigTarget.classList.remove('hidden');
        this.linkUrlTarget.value = link.getAttribute('href') || '';
        this.linkBlankTarget.checked = link.getAttribute('target') === '_blank';
        this.linkNofollowTarget.checked = link.getAttribute('rel')?.includes('nofollow');
        this.currentLink = link;
    }

    hideLinkConfig() {
        if (this.hasLinkConfigTarget) {
            this.linkConfigTarget.classList.add('hidden');
            this.currentLink = null;
        }
    }

    updateLink() {
        if (!this.currentLink) return;

        this.currentLink.setAttribute('href', this.linkUrlTarget.value);

        if (this.linkBlankTarget.checked) {
            this.currentLink.setAttribute('target', '_blank');
        } else {
            this.currentLink.removeAttribute('target');
        }

        if (this.linkNofollowTarget.checked) {
            this.currentLink.setAttribute('rel', 'nofollow');
        } else {
            this.currentLink.removeAttribute('rel');
        }

        this.sync();
    }

    async openMediaPicker() {
        const component = await getComponent(this.element.closest('[data-controller="live"]'));
        await component.action('openMediaPicker', { context: this.idValue });
    }

    onMediaSelected(event) {
        const { id, url, context } = event.detail;
        if (context !== this.idValue) return;

        if (this.hasEditorTarget) {
            this.editorTarget.focus();
            document.execCommand('insertImage', false, url);
            this.sync();
        } else {
            // It's a non-rich-text block (like an Image block or a featured image field)
            const component = this.element.closest('[data-controller="live"]');
            getComponent(component).then(comp => {
                comp.action('updateBlockDataProperty', {
                    id: this.idValue,
                    property: 'mediaId',
                    value: id
                });
            });
        }
    }

    async keydown(event) {
        // Shift+Enter -> New paragraph block (Optional, keeping it simple for now as requested)
        if (event.key === 'Enter' && event.shiftKey) {
            const component = await getComponent(this.element.closest('[data-controller="live"]'));
            if (this.editorTarget.closest('ul, ol')) return;

            event.preventDefault();
            const blockEl = this.element.closest('[data-block-id]');
            const allBlocks = Array.from(blockEl.parentElement.querySelectorAll('[data-block-id]'));
            const index = allBlocks.indexOf(blockEl);

            await component.action('addBlock', { type: 'paragraph', index: index + 1 });
        }

        // Backspace on empty -> Delete block
        if (event.key === 'Backspace' && this.editorTarget.innerHTML === '' && this.editorTarget.innerHTML !== '<br>') {
            event.preventDefault();
            const component = await getComponent(this.element.closest('[data-controller="live"]'));
            await component.action('removeBlock', { id: this.idValue });
        }
    }

    // Collection & Table sync remains for legacy/structured blocks compatibility
    async syncCollection(event) {
        const component = await getComponent(this.element.closest('[data-controller="live"]'));
        const property = event.target.dataset.property;
        const collection = [];
        const items = Array.from(this.element.querySelectorAll(`[data-property="${property}"]`));

        items.forEach(item => {
            const data = {};
            if (property === 'items') {
                data.content = item.innerHTML;
            }
            collection.push(data);
        });

        component.action('updateBlockData', {
            id: this.idValue,
            data: { [property]: collection }
        }, { debounce: 500 });
    }
}
