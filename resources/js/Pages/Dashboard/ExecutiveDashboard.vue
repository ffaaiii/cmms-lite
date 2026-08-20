<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    period: Object,
    metrics: Object,
});

const selectedDays = ref(30);

function applyDaysFilter(days) {
    selectedDays.value = days;
    router.get(route('executive.dashboard'), { days }, { preserveState: true });
}

function fmt(value) {
    return value === null ? '-' : `${value} jam`;
}
</script>

<template>
    <Head title="Dashboard Eksekutif" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Ringkasan Reliability</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-400">Periode:</span>
                    <button
                        v-for="d in [7, 30, 90]"
                        :key="d"
                        @click="applyDaysFilter(d)"
                        :class="[
                            'rounded px-3 py-1 text-sm',
                            selectedDays === d ? 'bg-teal-700 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600',
                        ]"
                    >
                        {{ d }} hari
                    </button>
                    <span class="ml-2 text-xs text-gray-500">{{ period.from }} s/d {{ period.to }}</span>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-lg bg-gray-800 p-6 text-center">
                        <p class="text-sm text-gray-400">MTTR</p>
                        <p class="mt-2 text-3xl font-bold text-teal-400">{{ fmt(metrics.mttr_hours) }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-800 p-6 text-center">
                        <p class="text-sm text-gray-400">MTBF</p>
                        <p class="mt-2 text-3xl font-bold text-teal-400">{{ fmt(metrics.mtbf_aggregate_hours) }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-800 p-6 text-center">
                        <p class="text-sm text-gray-400">Rata-rata Downtime</p>
                        <p class="mt-2 text-3xl font-bold text-teal-400">{{ fmt(metrics.downtime_avg_hours) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>