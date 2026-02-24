// Global scripts
window.toggleAccordion = function(id) {
    const content = document.getElementById(id + '-content');
    const button = content.previousElementSibling;
    const chevron = document.getElementById(id + '-chevron');

    const isExpanded = button.getAttribute('aria-expanded') === 'true';

    if (isExpanded) {
        // Collapse
        content.classList.add('hidden');
        button.setAttribute('aria-expanded', 'false');
        chevron.classList.remove('rotate-180');
    } else {
        // Expand
        content.classList.remove('hidden');
        button.setAttribute('aria-expanded', 'true');
        chevron.classList.add('rotate-180');
    }
}
