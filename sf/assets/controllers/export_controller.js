import { Controller } from 'stimulus';
import printJS from 'print-js';

export default class extends Controller {
    connect() {}

    print(event) {
        const orientation = event.currentTarget.dataset.orientation;
        let styles = [...document.styleSheets]
            .map(styleSheet =>
                [...styleSheet.cssRules]
                .map(rule => rule.cssText)
                .join(''))
            .filter(Boolean)
            .join('\n')
        ;

        styles = orientation === 'landscape' ? '@page { size: Letter landscape; }' + styles : styles;

        printJS({
            printable: 'print-content',
            documentTitle: '',
            type: 'html',
            scanStyles: false,
            style: styles,
            maxWidth: 1000
        });
    }

    pdf(event) {
        window.open(
            `${window.location.href}?export=pdf`,
            '_blank'
        );
    }
}