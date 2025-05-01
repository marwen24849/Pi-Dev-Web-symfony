import './bootstrap.js';
import './styles/app.css';
import TomSelect from 'tom-select';

// Tu peux ignorer ce console.log si tu veux
console.log('Chart.js avec Stimulus prêt 🎉');
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-controller="autocomplete"][name$="[category]"]').forEach((el) => {
        new TomSelect(el, {
            plugins: ['clear_button'],
            create: true,
            maxOptions: 10,
            load: function(query, callback) {
                fetch(el.dataset.autocompleteUrl + '?query=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
            render: {
                option: function(item, escape) {
                    return '<div>' + escape(item.value) + '</div>';
                },
                item: function(item, escape) {
                    return '<div>' + escape(item.value) + '</div>';
                }
            }
        });
    });
});