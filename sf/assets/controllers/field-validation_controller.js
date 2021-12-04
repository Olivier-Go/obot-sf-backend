import { Controller } from 'stimulus';

export default class extends Controller {
    static targets = [ "form" ]

    connect() {}

    validate(event) {
        this.formTarget.classList.add('was-validated');
    }
}
