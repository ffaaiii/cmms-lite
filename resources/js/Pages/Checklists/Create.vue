<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    assets: Array,
});

const form = useForm({
    asset_id: '',
    condition_found: '',
    notes: '',
});

function selectCondition(value) {
    form.condition_found = value;
}

function submit() {
    form.post(route('technician.checklists.store'), {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Checklist Inspeksi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">Checklist Inspeksi</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-lg sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-5 rounded-lg bg-gray-800 p-6">
                    <div>
                        <label class="block text-sm text-gray-300">Pilih Aset</label>
                        <select v-model="form.asset_id" class="mt-1 w-full rounded bg-gray-700 p-3 text-white">
                            <option value="" disabled>-- Pilih Aset --</option>
                            <option v-for="asset in assets" :key="asset.id" :value="asset.id">
                                {{ asset.name }} — {{ asset.location ?? asset.category }}
                            </option>
                        </select>
                        <p v-if="form.errors.asset_id" class="mt-1 text-sm text-red-400">{{ form.errors.asset_id }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm text-gray-300">Kondisi Aset</label>
                        <div class="grid grid-cols-1 gap-3">
                            <button
                                type="button"
                                @click="selectCondition('good')"
                                :class="[
                                    'rounded-lg p-4 text-lg font-semibold transition',
                                    form.condition_found === 'good'
                                        ? 'bg-teal-700 text-white ring-2 ring-teal-400'
                                        : 'bg-gray-700 text-gray-200 hover:bg-gray-600',
                                ]"
                            >
                                ✓ Baik
                            </button>
                            <button
                                type="button"
                                @click="selectCondition('needs_attention')"
                                :class="[
                                    'rounded-lg p-4 text-lg font-semibold transition',
                                    form.condition_found === 'needs_attention'
                                        ? 'bg-amber-700 text-white ring-2 ring-amber-400'
                                        : 'bg-gray-700 text-gray-200 hover:bg-gray-600',
                                ]"
                            >
                                ⚠ Perlu Perhatian
                            </button>
                            <button
                                type="button"
                                @click="selectCondition('damaged')"
                                :class="[
                                    'rounded-lg p-4 text-lg font-semibold transition',
                                    form.condition_found === 'damaged'
                                        ? 'bg-red-700 text-white ring-2 ring-red-400'
                                        : 'bg-gray-700 text-gray-200 hover:bg-gray-600',
                                ]"
                            >
                                ✕ Rusak
                            </button>
                        </div>
                        <p v-if="form.errors.condition_found" class="mt-1 text-sm text-red-400">
                            {{ form.errors.condition_found }}
                        </p>
                    </div>

                    <div v-if="form.condition_found && form.condition_found !== 'good'">
                        <label class="block text-sm text-gray-300">Catatan (wajib)</label>
                        <textarea
                            v-model="form.notes"
                            rows="4"
                            placeholder="Jelaskan temuan..."
                            class="mt-1 w-full rounded bg-gray-700 p-3 text-white"
                        ></textarea>
                        <p v-if="form.errors.notes" class="mt-1 text-sm text-red-400">{{ form.errors.notes }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing || !form.condition_found"
                        class="w-full rounded-lg bg-teal-700 p-4 text-lg font-semibold text-white hover:bg-teal-600 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Mengirim...' : 'Kirim Checklist' }}
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>