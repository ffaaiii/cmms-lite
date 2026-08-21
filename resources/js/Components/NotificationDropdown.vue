<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();
const showDropdown = ref(false);

const typeLabel = {
    pm_due: 'PM Jatuh Tempo',
    wo_assigned: 'WO Ditugaskan',
    wo_rejected: 'WO Ditolak',
    wo_escalated: 'Perlu Eskalasi',
};

function notificationTarget(notif) {
    const role = page.props.auth.user.role_slug;
    if (!notif.related_work_order_id) return null;

    return role === 'technician'
        ? route('technician.tasks.show', notif.related_work_order_id)
        : route('supervisor.work-orders.show', notif.related_work_order_id);
}

function openNotification(notif) {
    router.patch(route('notifications.read', notif.id), {}, {
        onSuccess: () => {
            const target = notificationTarget(notif);
            if (target) router.visit(target);
        },
    });
    showDropdown.value = false;
}
</script>

<template>
    <div class="relative">
        <button @click="showDropdown = !showDropdown" class="relative rounded p-2 text-gray-400 hover:text-white">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span
                v-if="page.props.unreadNotifications.length > 0"
                class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white"
            >
                {{ page.props.unreadNotifications.length }}
            </span>
        </button>

        <div
            v-if="showDropdown"
            class="absolute right-0 z-50 mt-2 w-80 rounded-lg bg-gray-800 border border-gray-700 shadow-xl"
        >
            <div class="border-b border-gray-700 p-3 text-sm font-semibold text-white">Notifikasi</div>
            <div class="max-h-96 overflow-y-auto">
                <button
                    v-for="notif in page.props.unreadNotifications"
                    :key="notif.id"
                    @click="openNotification(notif)"
                    class="block w-full border-b border-gray-700/50 p-3 text-left text-sm hover:bg-gray-700/50 transition"
                >
                    <span class="text-xs font-medium text-teal-400">{{ typeLabel[notif.type] ?? notif.type }}</span>
                    <p class="mt-1 text-gray-200 text-xs">{{ notif.message }}</p>
                </button>
                <div v-if="page.props.unreadNotifications.length === 0" class="p-4 text-center text-sm text-gray-400">
                    Tidak ada notifikasi baru.
                </div>
            </div>
        </div>
    </div>
</template>