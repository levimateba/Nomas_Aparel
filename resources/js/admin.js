import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import ApexCharts from 'apexcharts';

Alpine.plugin(collapse);
window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    const salesEl = document.querySelector('#dash-sales-chart');
    if (salesEl && window.dashSalesChart) {
        const chart = new ApexCharts(salesEl, {
            chart: {
                type: 'area',
                height: 280,
                fontFamily: 'Outfit, sans-serif',
                toolbar: { show: false },
                zoom: { enabled: false },
            },
            colors: ['#A58112'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.02,
                    stops: [0, 90, 100],
                },
            },
            series: [{ name: 'Sales', data: window.dashSalesChart.revenue || [] }],
            xaxis: {
                categories: window.dashSalesChart.labels || [],
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: '#64748B', fontSize: '12px' } },
            },
            yaxis: {
                labels: {
                    style: { colors: '#64748B', fontSize: '12px' },
                    formatter: (v) => 'KES ' + Number(v).toLocaleString(),
                },
            },
            grid: {
                borderColor: '#EEF2F7',
                strokeDashArray: 4,
            },
            tooltip: {
                y: { formatter: (v) => 'KES ' + Number(v).toLocaleString(undefined, { minimumFractionDigits: 2 }) },
            },
        });
        chart.render();
    }

    const catEl = document.querySelector('#dash-category-chart');
    if (catEl && window.dashCategoryChart) {
        const labels = (window.dashCategoryChart || []).map((i) => i.name);
        const series = (window.dashCategoryChart || []).map((i) => i.count);
        const colors = ['#A58112', '#465FFF', '#12B76A', '#F79009', '#EE46BC', '#7A5AF8'];
        const chart = new ApexCharts(catEl, {
            chart: {
                type: 'donut',
                height: 260,
                fontFamily: 'Outfit, sans-serif',
            },
            colors,
            labels,
            series: series.length ? series : [1],
            legend: { show: false },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '12px', color: '#64748B', offsetY: 18 },
                            value: {
                                show: true,
                                fontSize: '22px',
                                fontWeight: 700,
                                color: '#111827',
                                offsetY: -8,
                                formatter: (v) => v,
                            },
                            total: {
                                show: true,
                                label: 'Products',
                                fontSize: '12px',
                                color: '#64748B',
                                formatter: () => series.reduce((a, b) => a + b, 0),
                            },
                        },
                    },
                },
            },
            stroke: { width: 0 },
        });
        chart.render();
    }

    document.querySelectorAll('[data-sparkline]').forEach((el) => {
        let data = [];
        try {
            data = JSON.parse(el.getAttribute('data-sparkline') || '[]');
        } catch (e) {
            data = [];
        }
        const color = el.getAttribute('data-color') || '#A58112';
        const chart = new ApexCharts(el, {
            chart: { type: 'line', height: 40, width: 90, sparkline: { enabled: true }, animations: { enabled: false } },
            stroke: { curve: 'smooth', width: 2 },
            colors: [color],
            series: [{ data: data.length ? data : [0, 0, 0, 0] }],
            tooltip: { enabled: false },
        });
        chart.render();
    });
});
