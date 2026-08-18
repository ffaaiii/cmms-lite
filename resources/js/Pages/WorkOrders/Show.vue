<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    workOrder: Object,
});

const statusLabel = {
    draft: 'Draft',
    assigned: 'Assigned',
    in_progress: 'In Progress',
    completed: 'Completed',
    closed: 'Closed',
};

const statusColor = {
    draft: 'bg-gray-600 text-gray-100',
    assigned: 'bg-blue-800 text-blue-100',
    in_progress: 'bg-amber-800 text-amber-100',
    completed: 'bg-teal-800 text-teal-100',
    closed: 'bg-green-900 text-green-100',
};

// --- Form transisi status (Teknisi) ---
const transitionForm = useForm({
    status: '',
    note: '',
    parts: [],
});

function addPartRow() {
    transitionForm.parts.push({ part_name: '', quantity: 1, unit: '' });
}

function removePartRow(index) {
    transitionForm.parts.splice(index, 1);
}

function startWork() {
    transitionForm.status = 'in_progress';
    transitionForm.patch(route('technician.tasks.transition', props.workOrder.id));
}

function completeWork() {
    transitionForm.status = 'completed';
    transitionForm.patch(route('technician.tasks.transition', props.workOrder.id));
}

// --- Aksi Supervisor ---
function approve() {
    if (confirm('Setujui work order ini? Status akan ditutup (Closed).')) {
        useForm({}).patch(route('supervisor.work-orders.approve', props.workOrder.id));
    }
}

const showRejectForm = ref(false);
const rejectForm = useForm({ rejection_note: '' });

function submitReject() {
    rejectForm.patch(route('supervisor.work-orders.reject', props.workOrder.id), {
        onSuccess: () => { showRejectForm.value = false; },
    });
}
</script>

<template>
    <Head title="Detail Work Order" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">
                Work Order: {{ workOrder.asset.name }}
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">

                <!-- Info utama -->
                <div class="rounded-lg bg-gray-800 p-6 text-gray-200">
                    <div class="mb-4 flex items-center justify-between">
                        <span :class="['rounded px-3 py-1 text-sm', statusColor[workOrder.status]]">
                            {{ statusLabel[workOrder.status] }}
                        </span>
                        <span v-if="workOrder.is_escalated" class="rounded bg-red-900 px-3 py-1 text-sm text-red-100">
                            Perlu Eskalasi
                        </span>
                    </div>

                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-400">Aset</dt>
                            <dd>{{ workOrder.asset.name }} ({{ workOrder.asset.category }})</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400">Lokasi</dt>
                            <dd>{{ workOrder.asset.location ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400">Tipe</dt>
                            <dd class="capitalize">{{ workOrder.type }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400">Prioritas</dt>
                            <dd class="capitalize">{{ workOrder.priority }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400">Teknisi</dt>
                            <dd>{{ workOrder.technician?.name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400">Dibuat oleh</dt>
                            <dd>{{ workOrder.creator?.name ?? '-' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4">
                        <dt class="text-gray-400 text-sm">Deskripsi</dt>
                        <dd class="mt-1">{{ workOrder.description ?? '-' }}</dd>
                    </div>

                    <div v-if="workOrder.rejection_note" class="mt-4 rounded bg-red-950 p-3 text-sm text-red-200">
                        <strong>Catatan penolakan terakhir:</strong> {{ workOrder.rejection_note }}
                        <span class="block text-red-400">({{ workOrder.rejection_count }}x ditolak)</span>
                    </div>
                </div>

                <!-- Part yang sudah dicatat -->
                <div v-if="workOrder.parts?.length" class="rounded-lg bg-gray-800 p-6">
                    <h3 class="mb-3 text-sm font-semibold text-gray-300">Part Digunakan</h3>
                    <ul class="space-y-1 text-sm text-gray-200">
                        <li v-for="part in workOrder.parts" :key="part.id">
                            {{ part.part_name }} — {{ part.quantity }} {{ part.unit ?? '' }}
                        </li>
                    </ul>
                </div>

                <!-- Aksi Teknisi: mulai kerja -->
                <div
                    v-if="$page.props.auth.user.role_slug === 'technician' && workOrder.status === 'assigned'"
                    class="rounded-lg bg-gray-800 p-6"
                >
                    <button
                        @click="startWork"
                        :disabled="transitionForm.processing"
                        class="rounded bg-amber-700 px-4 py-2 text-white hover:bg-amber-600 disabled:opacity-50"
                    >
                        Mulai Kerjakan
                    </button>
                </div>

                <!-- Aksi Teknisi: tandai selesai (catatan + part) -->
                <div
                    v-if="$page.props.auth.user.role_slug === 'technician' && workOrder.status === 'in_progress'"
                    class="rounded-lg bg-gray-800 p-6"
                >
                    <h3 class="mb-3 text-sm font-semibold text-gray-300">Tandai Selesai</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-300">Catatan Pekerjaan</label>
                            <textarea
                                v-model="transitionForm.note"
                                rows="3"
                                class="mt-1 w-full rounded bg-gray-700 text-white"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-300">Part Digunakan</label>
                            <div
                                v-for="(part, index) in transitionForm.parts"
                                :key="index"
                                class="mt-2 flex gap-2"
                            >
                                <input
                                    v-model="part.part_name"
                                    type="text"
                                    placeholder="Nama part"
                                    class="w-1/2 rounded bg-gray-700 text-white text-sm"
                                />
                                <input
                                    v-model="part.quantity"
                                    type="number"
                                    min="1"
                                    placeholder="Qty"
                                    class="w-1/4 rounded bg-gray-700 text-white text-sm"
                                />
                                <input
                                    v-model="part.unit"
                                    type="text"
                                    placeholder="Satuan"
                                    class="w-1/4 rounded bg-gray-700 text-white text-sm"
                                />
                                <button type="button" @click="removePartRow(index)" class="text-red-400">
                                    ✕
                                </button>
                            </div>
                            <button type="button" @click="addPartRow" class="mt-2 text-sm text-teal-400 hover:underline">
                                + Tambah Part
                            </button>
                        </div>

                        <p v-if="transitionForm.errors.parts" class="text-sm text-red-400">
                            {{ transitionForm.errors.parts }}
                        </p>

                        <button
                            @click="completeWork"
                            :disabled="transitionForm.processing"
                            class="rounded bg-teal-700 px-4 py-2 text-white hover:bg-teal-600 disabled:opacity-50"
                        >
                            {{ transitionForm.processing ? 'Menyimpan...' : 'Tandai Selesai' }}
                        </button>
                    </div>
                </div>

                <!-- Aksi Supervisor: approve/reject -->
                <div
                    v-if="$page.props.auth.user.role_slug === 'supervisor' && workOrder.status === 'completed'"
                    class="rounded-lg bg-gray-800 p-6"
                >
                    <h3 class="mb-3 text-sm font-semibold text-gray-300">Review Hasil Kerja</h3>

                    <div class="flex gap-2">
                        <button @click="approve" class="rounded bg-green-700 px-4 py-2 text-white hover:bg-green-600">
                            Approve
                        </button>
                        <button
                            @click="showRejectForm = !showRejectForm"
                            class="rounded bg-red-700 px-4 py-2 text-white hover:bg-red-600"
                        >
                            Reject
                        </button>
                    </div>

                    <div v-if="showRejectForm" class="mt-4 space-y-2">
                        <textarea
                            v-model="rejectForm.rejection_note"
                            rows="3"
                            placeholder="Alasan penolakan (wajib)"
                            class="w-full rounded bg-gray-700 text-white"
                        ></textarea>
                        <p v-if="rejectForm.errors.rejection_note" class="text-sm text-red-400">
                            {{ rejectForm.errors.rejection_note }}
                        </p>
                        <button
                            @click="submitReject"
                            :disabled="rejectForm.processing"
                            class="rounded bg-red-700 px-4 py-2 text-sm text-white hover:bg-red-600 disabled:opacity-50"
                        >
                            Kirim Penolakan
                        </button>
                    </div>
                </div>

                <!-- Riwayat log -->
                <div class="rounded-lg bg-gray-800 p-6">
                    <h3 class="mb-3 text-sm font-semibold text-gray-300">Riwayat Perubahan</h3>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li v-for="log in workOrder.logs" :key="log.id" class="border-b border-gray-700 pb-2">
                            <span class="text-gray-400">{{ log.user?.name }}</span> —
                            {{ log.from_status ?? '(baru)' }} → {{ log.to_status }}
                            <span v-if="log.note" class="block text-gray-400">"{{ log.note }}"</span>
                        </li>
                        <li v-if="!workOrder.logs?.length" class="text-gray-500">Belum ada riwayat.</li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>