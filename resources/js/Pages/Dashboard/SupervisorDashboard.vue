<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    period: Object,
    metrics: Object,
    statCards: Object,
});

const selectedDays = ref(30);

function applyDaysFilter(days) {
    selectedDays.value = days;
    router.get(route('supervisor.dashboard'), { days }, { preserveState: true });
}

function fmt(value, suffix = ' jam') {
    return value === null ? '-' : `${value}${suffix}`;
}
</script>

<template>
    <Head title="Dashboard Supervisor" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Dashboard Supervisor</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <!-- Filter periode -->
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

                <!-- Stat cards -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-lg bg-gray-800 p-5">
                        <p class="text-sm text-gray-400">Total WO Terbuka</p>
                        <p class="mt-1 text-3xl font-bold text-white">{{ statCards.total_wo_open }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-800 p-5">
                        <p class="text-sm text-gray-400">WO Urgent</p>
                        <p class="mt-1 text-3xl font-bold text-red-400">{{ statCards.wo_urgent }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-800 p-5">
                        <p class="text-sm text-gray-400">PM Jatuh Tempo Minggu Ini</p>
                        <p class="mt-1 text-3xl font-bold text-amber-400">{{ statCards.pm_due_this_week }}</p>
                    </div>
                </div>

                <!-- Metrik reliability -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-lg bg-gray-800 p-5">
                        <p class="text-sm text-gray-400">MTTR (rata-rata waktu perbaikan)</p>
                        <p class="mt-1 text-2xl font-bold text-teal-400">{{ fmt(metrics.mttr_hours) }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-800 p-5">
                        <p class="text-sm text-gray-400">MTBF Agregat</p>
                        <p class="mt-1 text-2xl font-bold text-teal-400">{{ fmt(metrics.mtbf_aggregate_hours) }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-800 p-5">
                        <p class="text-sm text-gray-400">Rata-rata Downtime</p>
                        <p class="mt-1 text-2xl font-bold text-teal-400">{{ fmt(metrics.downtime_avg_hours) }}</p>
                    </div>
                </div>

                <!-- MTBF per aset -->
                <div class="overflow-hidden rounded-lg bg-gray-800 shadow">
                    <div class="border-b border-gray-700 p-4">
                        <h3 class="font-semibold text-white">MTBF per Aset</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-700 text-sm">
                        <thead class="bg-gray-900 text-gray-300">
                            <tr>
                                <th class="px-4 py-3 text-left">Aset</th>
                                <th class="px-4 py-3 text-left">Jumlah Kerusakan</th>
                                <th class="px-4 py-3 text-left">MTBF (jam)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700 text-gray-200">
                            <tr v-for="row in metrics.mtbf_per_asset" :key="row.asset_id">
                                <td class="px-4 py-3">{{ row.asset_name }}</td>
                                <td class="px-4 py-3">{{ row.failure_count }}</td>
                                <td class="px-4 py-3">{{ fmt(row.mtbf_hours, '') }}</td>
                            </tr>
                            <tr v-if="metrics.mtbf_per_asset.length === 0">
                                <td colspan="3" class="px-4 py-6 text-center text-gray-400">
                                    Belum ada data kerusakan pada periode ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>