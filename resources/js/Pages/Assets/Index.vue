<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    assets: Object,
});

const conditionLabel = {
    good: 'Baik',
    needs_attention: 'Perlu Perhatian',
    damaged: 'Rusak',
};

const conditionColor = {
    good: 'bg-teal-800 text-teal-100',
    needs_attention: 'bg-amber-800 text-amber-100',
    damaged: 'bg-red-800 text-red-100',
};

function destroy(asset) {
    if (confirm(`Nonaktifkan aset "${asset.name}"? Data histori tetap tersimpan.`)) {
        router.delete(route('admin.assets.destroy', asset.id));
    }
}

// --- Modal update kondisi (Supervisor) ---
const showModal = ref(false);
const editingAsset = ref(null);

const conditionForm = useForm({
    condition: '',
    location: '',
});

function openConditionModal(asset) {
    editingAsset.value = asset;
    conditionForm.condition = asset.condition;
    conditionForm.location = asset.location ?? '';
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingAsset.value = null;
    conditionForm.reset();
    conditionForm.clearErrors();
}

function submitCondition() {
    conditionForm.patch(route('supervisor.assets.updateCondition', editingAsset.value.id), {
        onSuccess: () => closeModal(),
    });
}
</script>

<template>
    <Head title="Manajemen Aset" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Manajemen Aset</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 flex justify-end">
                    <Link
                        v-if="$page.props.auth.user.role_slug === 'admin'"
                        :href="route('admin.assets.create')"
                        class="rounded bg-teal-700 px-4 py-2 text-sm text-white hover:bg-teal-600"
                    >
                        + Tambah Aset
                    </Link>
                </div>

                <div class="overflow-hidden rounded-lg bg-gray-800 shadow">
                    <table class="min-w-full divide-y divide-gray-700 text-sm">
                        <thead class="bg-gray-900 text-gray-300">
                            <tr>
                                <th class="px-4 py-3 text-left">Nama</th>
                                <th class="px-4 py-3 text-left">Kategori</th>
                                <th class="px-4 py-3 text-left">Lokasi</th>
                                <th class="px-4 py-3 text-left">Kondisi</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700 text-gray-200">
                            <tr v-for="asset in assets.data" :key="asset.id">
                                <td class="px-4 py-3">{{ asset.name }}</td>
                                <td class="px-4 py-3">{{ asset.category }}</td>
                                <td class="px-4 py-3">{{ asset.location ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span :class="['rounded px-2 py-1 text-xs', conditionColor[asset.condition]]">
                                        {{ conditionLabel[asset.condition] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span v-if="asset.is_deleted" class="rounded bg-gray-600 px-2 py-1 text-xs">Dihapus</span>
                                    <span v-else-if="asset.status === 'inactive'" class="rounded bg-gray-600 px-2 py-1 text-xs">Nonaktif</span>
                                    <span v-else class="rounded bg-green-800 px-2 py-1 text-xs">Aktif</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <!-- Aksi Admin -->
                                    <template v-if="$page.props.auth.user.role_slug === 'admin' && !asset.is_deleted">
                                        <Link
                                            :href="route('admin.assets.edit', asset.id)"
                                            class="mr-2 text-teal-400 hover:underline"
                                        >
                                            Edit
                                        </Link>
                                        <button @click="destroy(asset)" class="text-red-400 hover:underline">
                                            Nonaktifkan
                                        </button>
                                    </template>

                                    <!-- Aksi Supervisor -->
                                    <button
                                        v-if="$page.props.auth.user.role_slug === 'supervisor' && !asset.is_deleted"
                                        @click="openConditionModal(asset)"
                                        class="text-amber-400 hover:underline"
                                    >
                                        Update Kondisi
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="assets.data.length === 0">
                                <td colspan="6" class="px-4 py-6 text-center text-gray-400">
                                    Belum ada aset terdaftar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Update Kondisi (Supervisor) -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="closeModal"
        >
            <div class="w-full max-w-md rounded-lg bg-gray-800 p-6">
                <h3 class="mb-4 text-lg font-semibold text-white">
                    Update Kondisi: {{ editingAsset?.name }}
                </h3>

                <form @submit.prevent="submitCondition" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-300">Kondisi</label>
                        <select v-model="conditionForm.condition" class="mt-1 w-full rounded bg-gray-700 text-white">
                            <option value="good">Baik</option>
                            <option value="needs_attention">Perlu Perhatian</option>
                            <option value="damaged">Rusak</option>
                        </select>
                        <p v-if="conditionForm.errors.condition" class="mt-1 text-sm text-red-400">
                            {{ conditionForm.errors.condition }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-300">Lokasi</label>
                        <input v-model="conditionForm.location" type="text" class="mt-1 w-full rounded bg-gray-700 text-white" />
                        <p v-if="conditionForm.errors.location" class="mt-1 text-sm text-red-400">
                            {{ conditionForm.errors.location }}
                        </p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="closeModal" class="rounded bg-gray-600 px-4 py-2 text-sm text-white hover:bg-gray-500">
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="conditionForm.processing"
                            class="rounded bg-amber-700 px-4 py-2 text-sm text-white hover:bg-amber-600 disabled:opacity-50"
                        >
                            {{ conditionForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>