<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    name: '',
    category: 'turbine',
    location: '',
    installed_at: '',
    condition: 'good',
    pm_interval_days: 90,
});

function submit() {
    form.post(route('admin.assets.store'));
}
</script>

<template>
    <Head title="Tambah Aset" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Tambah Aset Baru</h2>
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
                        <p v-if="form.errors.category" class="mt-1 text-sm text-red-400">{{ form.errors.category }}</p>
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
                        <label class="block text-sm text-gray-300">Interval PM (hari)</label>
                        <input v-model="form.pm_interval_days" type="number" min="1" class="mt-1 w-full rounded bg-gray-700 text-white" />
                        <p v-if="form.errors.pm_interval_days" class="mt-1 text-sm text-red-400">{{ form.errors.pm_interval_days }}</p>
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