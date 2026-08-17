<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    asset: Object,
});

const form = useForm({
    name: props.asset.name,
    category: props.asset.category,
    location: props.asset.location,
    installed_at: props.asset.installed_at,
    condition: props.asset.condition,
    pm_interval_days: props.asset.pm_interval_days,
    status: props.asset.status,
});

function submit() {
    form.put(route('admin.assets.update', props.asset.id));
}
</script>

<template>
    <Head title="Edit Aset" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Edit Aset: {{ asset.name }}</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-4 rounded-lg bg-gray-800 p-6">
                    <div>
                        <label class="block text-sm text-gray-300">Nama Aset</label>
                        <input v-model="form.name" type="text" class="mt-1 w-full rounded bg-gray-700 text-white" />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-400">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-300">Kategori</label>
                        <select v-model="form.category" class="mt-1 w-full rounded bg-gray-700 text-white">
                            <option value="turbine">Turbin</option>
                            <option value="well">Sumur</option>
                            <option value="pipe">Pipa</option>
                            <option value="cooling_tower">Cooling Tower</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-300">Lokasi</label>
                        <input v-model="form.location" type="text" class="mt-1 w-full rounded bg-gray-700 text-white" />
                    </div>

                    <div>
                        <label class="block text-sm text-gray-300">Tanggal Instalasi</label>
                        <input v-model="form.installed_at" type="date" class="mt-1 w-full rounded bg-gray-700 text-white" />
                    </div>

                    <div>
                        <label class="block text-sm text-gray-300">Kondisi</label>
                        <select v-model="form.condition" class="mt-1 w-full rounded bg-gray-700 text-white">
                            <option value="good">Baik</option>
                            <option value="needs_attention">Perlu Perhatian</option>
                            <option value="damaged">Rusak</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-300">Interval PM (hari)</label>
                        <input v-model="form.pm_interval_days" type="number" min="1" class="mt-1 w-full rounded bg-gray-700 text-white" />
                        <p v-if="form.errors.pm_interval_days" class="mt-1 text-sm text-red-400">{{ form.errors.pm_interval_days }}</p>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-300">Status</label>
                        <select v-model="form.status" class="mt-1 w-full rounded bg-gray-700 text-white">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded bg-teal-700 px-4 py-2 text-white hover:bg-teal-600 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>