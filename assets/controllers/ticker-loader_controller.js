import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    loaderContainer = document.getElementById('ticker-loading');

    connect() {
        this.loaderContainer.classList.remove('d-flex');
        this.loaderContainer.classList.add('d-none');
        this.resetTabbable();
    }

    click(event) {
        document.activeElement.blur();
        this.loaderContainer.classList.remove('d-none');
        this.loaderContainer.classList.add('d-flex');
    }

    resetTabbable() {
        let tabClass = null;

        document.querySelectorAll('[role="tablist"] button').forEach((btn, index) => {
            if (index === 0) {
                btn.classList.add('active');
                tabClass = btn.dataset.class;
            } else {
                btn.classList.remove('active');
            }
        });

        const allTabList = [].slice.call(document.querySelectorAll('.tabbable-pane'));
        allTabList.forEach(triggerEl => {
            triggerEl.classList.remove('fade-in', 'active', 'show');
        });

        const triggerTabList = [].slice.call(document.querySelectorAll(tabClass));
        triggerTabList.forEach(triggerEl => {
            triggerEl.classList.add('fade-in', 'active', 'show');
        });
    }
}
