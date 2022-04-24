import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {}

    show(event) {
        const button = event.currentTarget;
        const tabClass = event.currentTarget.dataset.class;

        document.querySelectorAll('.tabbable').forEach(btn => {
            btn.classList.remove('active');
        });

        const allTabList = [].slice.call(document.querySelectorAll('.tabbable-pane'));
        allTabList.forEach(triggerEl => {
            triggerEl.classList.remove('fade-in', 'active', 'show');
        });

        const triggerTabList = [].slice.call(document.querySelectorAll(tabClass));
        triggerTabList.forEach(triggerEl => {
            triggerEl.classList.add('fade-in', 'active', 'show');
        });
        button.classList.add('active');
    }
}
