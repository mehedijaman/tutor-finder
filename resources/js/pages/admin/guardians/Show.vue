<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    guardian: {
        type: Object,
        required: true,
    },
});

const breadcrumbs = [
    { title: 'Guardians', href: '/admin/guardians' },
    { title: 'Details', href: '#' },
];

const confirmOpen = ref(false);

function toggleStatus() {
    const nextStatus =
        props.guardian.status === 'active' ? 'suspended' : 'active';

    router.patch(`/admin/guardians/${props.guardian.id}/status`, {
        status: nextStatus,
    });

    confirmOpen.value = false;
}
</script>

<template>
    <Head title="Guardian Details" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900"
                    >
                        Guardian Details
                    </h1>
                    <p class="text-sm text-slate-600">
                        View guardian profile information and account state.
                    </p>
                </div>
                <Link
                    href="/admin/guardians"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >Back</Link
                >
            </div>

            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 text-sm shadow-sm sm:p-6"
            >
                <p>
                    <span class="font-medium">Name:</span> {{ guardian.name }}
                </p>
                <p>
                    <span class="font-medium">Email:</span>
                    {{ guardian.email || '—' }}
                </p>
                <p>
                    <span class="font-medium">Phone:</span>
                    {{ guardian.phone || '—' }}
                </p>
                <p>
                    <span class="font-medium">Status:</span>
                    {{ guardian.status }}
                </p>
                <p>
                    <span class="font-medium">Phone verified:</span>
                    {{ guardian.phone_verified_at || 'No' }}
                </p>
            </div>

            <Button type="button" @click="confirmOpen = true">
                {{
                    guardian.status === 'active'
                        ? 'Suspend Guardian'
                        : 'Unsuspend Guardian'
                }}
            </Button>
        </div>

        <ConfirmDialog
            v-model:open="confirmOpen"
            :title="
                guardian.status === 'active'
                    ? 'Suspend Guardian'
                    : 'Unsuspend Guardian'
            "
            :description="
                guardian.status === 'active'
                    ? 'Suspend user will prevent login and dashboard access.'
                    : 'Unsuspend will re-enable access.'
            "
            :confirm-label="
                guardian.status === 'active' ? 'Suspend' : 'Unsuspend'
            "
            :destructive="guardian.status === 'active'"
            @confirm="toggleStatus"
        />
    </AdminLayout>
</template>
