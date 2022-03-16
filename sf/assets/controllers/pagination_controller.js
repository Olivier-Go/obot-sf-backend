import { Controller } from 'stimulus';

export default class extends Controller {
    static values = {
        url: String
    }

    connect() {}

    change(event) {
        const selectedValue = event.currentTarget;
        const item = selectedValue.options[selectedValue.selectedIndex].text;
        window.location.href = this.urlValue.replace('_itemNum', item);
    }
}
