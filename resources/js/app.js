import './bootstrap';
import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;
window.createIcons = createIcons;
window.lucideIcons = icons;

// Formatters for Brazilian standard formats
window.formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
};

window.formatDate = (dateStr) => {
    if (!dateStr) return '';
    const [year, month, day] = dateStr.split('T')[0].split('-');
    return `${day}/${month}/${year}`;
};

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});
