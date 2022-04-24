import { Controller } from '@hotwired/stimulus';
import { jsonSubmitFormData } from '../js/utils/functions';

export default class extends Controller {
    static targets = [ "form", "chart" ]

    connect() {}

    change(event) {
        jsonSubmitFormData(this.formTarget)
            .then(response => {
                this.chartTarget.innerHTML = response.content;
            });
    }
}
