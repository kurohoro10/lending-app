// resources/js/app.js
import './bootstrap';

// Import globals first and assign to window explicitly so all
// eagerly-loaded module scripts below can safely call window.*
import {
    toggleAccordion,
    toRawNumber,
    formatWithCommas,
    initCurrencyInput,
} from './script';

window.toggleAccordion  = toggleAccordion;
window.toRawNumber      = toRawNumber;
window.formatWithCommas = formatWithCommas;
window.initCurrencyInput = initCurrencyInput;

// Auto-load all application modules
import.meta.glob('./applications/**/*.js', { eager: true });

// Auto-load all admin modules
import.meta.glob('./admin/**/*.js', { eager: true });