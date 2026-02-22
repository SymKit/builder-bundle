import { Controller } from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["row"];

    connect() {
        this.updateVisibility();
    }

    disconnect() {
        // Nothing to explicitly block or disconnect
    }

    switch(event) {
        const fieldName = event.currentTarget.dataset.dependencyField;
        const groupName = event.currentTarget.dataset.dependencyGroup;

        this.updateVisibility(groupName, fieldName);
    }

    updateVisibility(groupName = null, activeFieldName = null) {
        this.rowTargets.forEach(row => {
            const rowGroup = row.dataset.dependencyGroup;
            const rowField = row.dataset.dependencyField;

            // If groupName is provided, only touch rows of that group
            if (groupName && rowGroup !== groupName) {
                return;
            }

            // On initial load (activeFieldName is null), use the data-dependency-active attribute
            const isActive = activeFieldName
                ? rowField === activeFieldName
                : row.dataset.dependencyActive === 'true';

            if (isActive) {
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');

                // Only clear if we are in an explicit switch (to avoid clearing data on load)
                if (activeFieldName) {
                    this.clearFieldValue(row);
                }
            }
        });
    }

    clearFieldValue(row) {
        // Find inputs, including hidden ones but only if they carry the model value (like in RichSelect)
        const input = row.querySelector('input:not([type="hidden"]), input[data-model="value"], select, textarea');

        if (input) {
            input.value = '';

            // Trigger events for Live Components and other listeners
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
}
