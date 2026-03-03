<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    tutor: {
        type: Object,
        required: true,
    },
});

const breadcrumbs = [
    { title: 'Tutors', href: '/admin/tutors' },
    { title: 'Details', href: '#' },
];

const confirmOpen = ref(false);

function toggleStatus() {
    const nextStatus = props.tutor.status === 'active' ? 'suspended' : 'active';

    router.patch(`/admin/tutors/${props.tutor.id}/status`, {
        status: nextStatus,
    });

    confirmOpen.value = false;
}
</script>

<template>
    <Head title="Tutor Details" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Tutor Details</h1>
                <Link href="/admin/tutors" class="text-sm underline">Back</Link>
            </div>

            <div class="rounded-xl border bg-white p-4 text-sm">
                <p><span class="font-medium">Name:</span> {{ tutor.name }}</p>
                <p>
                    <span class="font-medium">Email:</span>
                    {{ tutor.email || '—' }}
                </p>
                <p>
                    <span class="font-medium">Phone:</span>
                    {{ tutor.phone || '—' }}
                </p>
                <p>
                    <span class="font-medium">Status:</span> {{ tutor.status }}
                </p>
                <p>
                    <span class="font-medium">Phone verified:</span>
                    {{ tutor.phone_verified_at || 'No' }}
                </p>
            </div>

            <Button type="button" @click="confirmOpen = true">
                {{
                    tutor.status === 'active'
                        ? 'Suspend Tutor'
                        : 'Unsuspend Tutor'
                }}
            </Button>
        </div>

        <ConfirmDialog
            v-model:open="confirmOpen"
            :title="
                tutor.status === 'active' ? 'Suspend Tutor' : 'Unsuspend Tutor'
            "
            :description="
                tutor.status === 'active'
                    ? 'Suspend user will prevent login and dashboard access.'
                    : 'Unsuspend will re-enable access.'
            "
            :confirm-label="tutor.status === 'active' ? 'Suspend' : 'Unsuspend'"
            :destructive="tutor.status === 'active'"
            @confirm="toggleStatus"
        />
    </AdminLayout>
</template>
