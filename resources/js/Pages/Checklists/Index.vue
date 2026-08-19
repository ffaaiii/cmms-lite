<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    checklists: Object,
});

const conditionLabel = {
    needs_attention: 'Perlu Perhatian',
    damaged: 'Rusak',
};

const conditionColor = {
    needs_attention: 'bg-amber-800 text-amber-100',
    damaged: 'bg-red-800 text-red-100',
};

function confirmChecklist(checklist) {
    if (confirm(`Konfirmasi usulan ini? Work order corrective akan dibuat untuk "${checklist.asset.name}".`)) {
        router.patch(route('supervisor.checklists.confirm', checklist.id));
    }
}

function dismissChecklist(checklist) {
    if (confirm(`Tolak usulan ini? Tidak ada work order yang akan dibuat.`)) {
        router.patch(route('supervisor.checklists.dismiss', checklist.id));
    }
}
</script>

<template>
    <Head title="Review Checklist Inspeksi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Review Usulan Checklist</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="space-y-4">
                    <div
                        v-for="checklist in checklists.data"
                        :key="checklist.id"
                        class="rounded-lg bg-gray-800 p-5"
                    >
                        <div class="mb-2 flex items-center justify-between">
                            <h3 class="font-semibold text-white">{{ checklist.asset.name }}</h3>
                            <span :class="['rounded px-2 py-1 text-xs', conditionColor[checklist.condition_found]]">
                                {{ conditionLabel[checklist.condition_found] }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-400">
                            Dilaporkan oleh {{ checklist.inspector.name }}
                        </p>
                        <p class="mt-2 text-sm text-gray-200">{{ checklist.notes }}</p>

                        <div class="mt-4 flex gap-2">
                            <button
                                @click="confirmChecklist(checklist)"
                                class="rounded bg-green-700 px-4 py-2 text-sm text-white hover:bg-green-600"
                            >
                                Konfirmasi → Buat WO
                            </button>
                            <button
                                @click="dismissChecklist(checklist)"
                                class="rounded bg-gray-600 px-4 py-2 text-sm text-white hover:bg-gray-500"
                            >
                                Tolak
                            </button>
                        </div>
                    </div>

                    <div v-if="checklists.data.length === 0" class="rounded-lg bg-gray-800 p-6 text-center text-gray-400">
                        Tidak ada usulan yang perlu direview.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>