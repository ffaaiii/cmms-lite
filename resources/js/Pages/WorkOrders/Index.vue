<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    workOrders: Object,
    technicians: Array,
});

const statusLabel = {
    draft: 'Draft',
    assigned: 'Assigned',
    in_progress: 'In Progress',
    completed: 'Completed',
    closed: 'Closed',
};

// Warna sesuai 11-ui-ux.md: Draft abu, Assigned biru, In Progress amber,
// Completed teal, Closed hijau gelap
const statusColor = {
    draft: 'bg-gray-600 text-gray-100',
    assigned: 'bg-blue-800 text-blue-100',
    in_progress: 'bg-amber-800 text-amber-100',
    completed: 'bg-teal-800 text-teal-100',
    closed: 'bg-green-900 text-green-100',
};

const priorityColor = {
    normal: 'bg-gray-700 text-gray-200',
    urgent: 'bg-red-800 text-red-100',
};

// --- Modal Assign / Reassign (Supervisor) ---
const showAssignModal = ref(false);
const assigningWo = ref(null);

const assignForm = useForm({ assigned_to: '' });

function openAssignModal(wo) {
    assigningWo.value = wo;
    assignForm.assigned_to = wo.assigned_to ?? '';
    showAssignModal.value = true;
}

function closeAssignModal() {
    showAssignModal.value = false;
    assigningWo.value = null;
    assignForm.reset();
    assignForm.clearErrors();
}

function submitAssign() {
    assignForm.patch(route('supervisor.work-orders.assign', assigningWo.value.id), {
        onSuccess: () => closeAssignModal(),
    });
}

// --- Modal Reject (Supervisor) ---
const showRejectModal = ref(false);
const rejectingWo = ref(null);

const rejectForm = useForm({ rejection_note: '' });

function openRejectModal(wo) {
    rejectingWo.value = wo;
    showRejectModal.value = true;
}

function closeRejectModal() {
    showRejectModal.value = false;
    rejectingWo.value = null;
    rejectForm.reset();
    rejectForm.clearErrors();
}

function submitReject() {
    rejectForm.patch(route('supervisor.work-orders.reject', rejectingWo.value.id), {
        onSuccess: () => closeRejectModal(),
    });
}

function approve(wo) {
    if (confirm(`Setujui work order untuk "${wo.asset.name}"? Status akan ditutup (Closed).`)) {
        router.patch(route('supervisor.work-orders.approve', wo.id));
    }
}

const isSupervisor = () => route().current().startsWith('supervisor.');
</script>

<template>
    <Head title="Work Order" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Work Order</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 flex justify-end">
                    <Link
                        v-if="$page.props.auth.user.role_slug === 'supervisor'"
                        :href="route('supervisor.work-orders.create')"
                        class="rounded bg-teal-700 px-4 py-2 text-sm text-white hover:bg-teal-600"
                    >
                        + Buat Work Order
                    </Link>
                </div>

                <div class="overflow-x-auto rounded-lg bg-gray-800 shadow">
                    <table class="min-w-full divide-y divide-gray-700 text-sm">
                        <thead class="bg-gray-900 text-gray-300">
                            <tr>
                                <th class="px-4 py-3 text-left">Aset</th>
                                <th class="px-4 py-3 text-left">Tipe</th>
                                <th class="px-4 py-3 text-left">Prioritas</th>
                                <th class="px-4 py-3 text-left">Teknisi</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Eskalasi</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700 text-gray-200">
                            <tr v-for="wo in workOrders.data" :key="wo.id">
                                <td class="px-4 py-3">{{ wo.asset.name }}</td>
                                <td class="px-4 py-3 capitalize">{{ wo.type }}</td>
                                <td class="px-4 py-3">
                                    <span :class="['rounded px-2 py-1 text-xs capitalize', priorityColor[wo.priority]]">
                                        {{ wo.priority }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ wo.technician?.name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span :class="['rounded px-2 py-1 text-xs', statusColor[wo.status]]">
                                        {{ statusLabel[wo.status] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span v-if="wo.is_escalated" class="rounded bg-red-900 px-2 py-1 text-xs text-red-100">
                                        Perlu Eskalasi
                                    </span>
                                    <span v-else class="text-gray-500">-</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        :href="$page.props.auth.user.role_slug === 'supervisor'
                                            ? route('supervisor.work-orders.show', wo.id)
                                            : route('technician.tasks.show', wo.id)"
                                        class="mr-2 text-teal-400 hover:underline"
                                    >
                                        Detail
                                    </Link>

                                    <template v-if="$page.props.auth.user.role_slug === 'supervisor'">
                                        <button
                                            v-if="wo.status === 'draft' || wo.status === 'assigned' || wo.status === 'in_progress'"
                                            @click="openAssignModal(wo)"
                                            class="mr-2 text-blue-400 hover:underline"
                                        >
                                            {{ wo.assigned_to ? 'Alihkan' : 'Assign' }}
                                        </button>
                                        <button
                                            v-if="wo.status === 'completed'"
                                            @click="approve(wo)"
                                            class="mr-2 text-green-400 hover:underline"
                                        >
                                            Approve
                                        </button>
                                        <button
                                            v-if="wo.status === 'completed'"
                                            @click="openRejectModal(wo)"
                                            class="text-red-400 hover:underline"
                                        >
                                            Reject
                                        </button>
                                    </template>
                                </td>
                            </tr>
                            <tr v-if="workOrders.data.length === 0">
                                <td colspan="7" class="px-4 py-6 text-center text-gray-400">
                                    Belum ada work order.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Assign / Reassign -->
        <div
            v-if="showAssignModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="closeAssignModal"
        >
            <div class="w-full max-w-md rounded-lg bg-gray-800 p-6">
                <h3 class="mb-4 text-lg font-semibold text-white">
                    {{ assigningWo?.assigned_to ? 'Alihkan' : 'Assign' }} Work Order: {{ assigningWo?.asset.name }}
                </h3>

                <form @submit.prevent="submitAssign" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-300">Pilih Teknisi</label>
                        <select v-model="assignForm.assigned_to" class="mt-1 w-full rounded bg-gray-700 text-white">
                            <option value="" disabled>-- Pilih Teknisi --</option>
                            <option v-for="tech in technicians" :key="tech.id" :value="tech.id">
                                {{ tech.name }}
                            </option>
                        </select>
                        <p v-if="assignForm.errors.assigned_to" class="mt-1 text-sm text-red-400">
                            {{ assignForm.errors.assigned_to }}
                        </p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="closeAssignModal" class="rounded bg-gray-600 px-4 py-2 text-sm text-white hover:bg-gray-500">
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="assignForm.processing"
                            class="rounded bg-blue-700 px-4 py-2 text-sm text-white hover:bg-blue-600 disabled:opacity-50"
                        >
                            {{ assignForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Reject -->
        <div
            v-if="showRejectModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="closeRejectModal"
        >
            <div class="w-full max-w-md rounded-lg bg-gray-800 p-6">
                <h3 class="mb-4 text-lg font-semibold text-white">
                    Reject Work Order: {{ rejectingWo?.asset.name }}
                </h3>

                <form @submit.prevent="submitReject" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-300">Catatan Penolakan (wajib)</label>
                        <textarea
                            v-model="rejectForm.rejection_note"
                            rows="3"
                            class="mt-1 w-full rounded bg-gray-700 text-white"
                        ></textarea>
                        <p v-if="rejectForm.errors.rejection_note" class="mt-1 text-sm text-red-400">
                            {{ rejectForm.errors.rejection_note }}
                        </p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="closeRejectModal" class="rounded bg-gray-600 px-4 py-2 text-sm text-white hover:bg-gray-500">
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="rejectForm.processing"
                            class="rounded bg-red-700 px-4 py-2 text-sm text-white hover:bg-red-600 disabled:opacity-50"
                        >
                            {{ rejectForm.processing ? 'Menyimpan...' : 'Tolak' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>