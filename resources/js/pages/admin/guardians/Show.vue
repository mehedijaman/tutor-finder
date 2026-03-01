<script setup>
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
    const nextStatus = props.guardian.status === 'active' ? 'suspended' : 'active';

    router.patch(`/admin/guardians/${props.guardian.id}/status`, {
        status: nextStatus,
    });

    confirmOpen.value = false;
}
</script>

<template>
    <Head title="Guardian Details" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Guardian Details</h1>
                <Link href="/admin/guardians" class="text-sm underline">Back</Link>
            </div>

            <div class="rounded-xl border bg-white p-4 text-sm">
                <p><span class="font-medium">Name:</span> {{ guardian.name }}</p>
                <p><span class="font-medium">Email:</span> {{ guardian.email || '—' }}</p>
                <p><span class="font-medium">Phone:</span> {{ guardian.phone || '—' }}</p>
                <p><span class="font-medium">Status:</span> {{ guardian.status }}</p>
                <p><span class="font-medium">Phone verified:</span> {{ guardian.phone_verified_at || 'No' }}</p>
            </div>

            <Button type="button" @click="confirmOpen = true">
                {{ guardian.status === 'active' ? 'Suspend Guardian' : 'Unsuspend Guardian' }}
            </Button>
        </div>

        <ConfirmDialog
            v-model:open="confirmOpen"
            :title="guardian.status === 'active' ? 'Suspend Guardian' : 'Unsuspend Guardian'"
            :description="
                guardian.status === 'active'
                    ? 'Suspend user will prevent login and dashboard access.'
                    : 'Unsuspend will re-enable access.'
            "
            :confirm-label="guardian.status === 'active' ? 'Suspend' : 'Unsuspend'"
            :destructive="guardian.status === 'active'"
            @confirm="toggleStatus"
        />
    </AdminLayout>
</template>
