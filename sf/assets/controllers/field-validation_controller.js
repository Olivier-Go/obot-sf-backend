import { Controller } from 'stimulus';
import { validateEmail, validatePassword } from "../js/utils/functions";

export default class extends Controller {
    static targets = [ "form" ]

    connect() {}

    validate(event) {
        const input = event.currentTarget;
        const isValid = () => {
            input.classList.add('is-valid');
            input.classList.remove('is-invalid');
        }
        const isInvalid = () => {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
        }

        switch(input.type) {
            case 'email':
                validateEmail(input.value) ? isValid() : isInvalid();
                break;
            case 'password':
                validatePassword(input.value) ? isValid() : isInvalid();
                break;
        }
    }
}
