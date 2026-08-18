<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    assets: Array,
});

const form = useForm({
    asset_id: '',
    type: 'corrective',
    priority: 'normal',
    description: '',
    scheduled_at: '',
});

function submit() {
    form.post(route('supervisor.work-orders.store'));
}
</script>

<template>
    <Head title="Buat Work Order" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Buat Work Order</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-4 rounded-lg bg-gray-800 p-6">
                    <div>
                        <label class="block text-sm text-gray-300">Aset</label>
                        <select v-model="form.asset_id" class="mt-1 w-full rounded bg-gray-700 text-white">
                            <option value="" disabled>-- Pilih Aset --</option>
                            <option v-for="asset in assets" :key="asset.id" :value="asset.id">
                                {{ asset.name }} ({{ asset.category }})
                            </option>
                        </select>
                        <p v-if="form.errors.asset_id" class="mt-1 text-sm text-red-400">{{ form.errors.asset_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-300">Tipe</label>
                        <select v-model="form.type" class="mt-1 w-full rounded bg-gray-700 text-white">
                            <option value="preventive">Preventive</option>
                            <option value="corrective">Corrective</option>
                        </select>
                        <p v-if="form.errors.type" class="mt-1 text-sm text-red-400">{{ form.errors.type }}</p>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-300">Prioritas</label>
                        <select v-model="form.priority" class="mt-1 w-full rounded bg-gray-700 text-white">
                            <option value="normal">Normal</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        <p v-if="form.errors.priority" class="mt-1 text-sm text-red-400">{{ form.errors.priority }}</p>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-300">Deskripsi</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="mt-1 w-full rounded bg-gray-700 text-white"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-400">{{ form.errors.description }}</p>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-300">Jadwal (opsional)</label>
                        <input v-model="form.scheduled_at" type="date" class="mt-1 w-full rounded bg-gray-700 text-white" />
                        <p v-if="form.errors.scheduled_at" class="mt-1 text-sm text-red-400">{{ form.errors.scheduled_at }}</p>
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