import './bootstrap';
import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';
import Chart from 'chart.js/auto';
import { initCepAutoFill, formatCep, fetchCepData } from './cep';

window.Alpine = Alpine;
window.Chart = Chart;
window.createIcons = createIcons;
window.lucideIcons = icons;
window.initCepAutoFill = initCepAutoFill;
window.formatCep = formatCep;
window.fetchCepData = fetchCepData;

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

function bootApp() {
    createIcons({ icons });
    initCepAutoFill();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootApp);
} else {
    bootApp();
}
