<script setup>
import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LineElement,
    LinearScale,
    PointElement,
    Title,
    Tooltip,
} from 'chart.js';
import { Bar, Line } from 'vue-chartjs';
import { computed } from 'vue';

ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, Title, Tooltip, Legend);

const props = defineProps({
    type: { type: String, default: 'bar' },
    labels: { type: Array, default: () => [] },
    datasets: { type: Array, default: () => [] },
    title: { type: String, default: '' },
    yMax: { type: Number, default: null },
});

const chartData = computed(() => ({
    labels: props.labels,
    datasets: props.datasets,
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: props.datasets.length > 1 },
        title: { display: !!props.title, text: props.title },
    },
    scales: {
        y: {
            beginAtZero: true,
            ...(props.yMax !== null ? { max: props.yMax } : {}),
        },
    },
}));

const ChartComponent = computed(() => (props.type === 'line' ? Line : Bar));
</script>

<template>
    <div class="h-64">
        <component :is="ChartComponent" :data="chartData" :options="chartOptions" />
    </div>
</template>
