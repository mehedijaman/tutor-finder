<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    message: {
        type: Object,
        required: true,
    },
});

const breadcrumbs = [
    { title: 'Contact Messages', href: '/admin/contact-messages' },
    { title: 'Details', href: '#' },
];

const confirmOpen = ref(false);

function openStatusDialog() {
    confirmOpen.value = true;
}

function updateStatus() {
    const nextStatus = props.message.status === 'closed' ? 'open' : 'closed';

    router.patch(
        `/admin/contact-messages/${props.message.id}/status`,
        {
            status: nextStatus,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                confirmOpen.value = false;
            },
        },
    );
}
</script>

<template>
    <Head :title="`Message #${message.id}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Contact Message #{{ message.id }}</h1>
                <Link href="/admin/contact-messages" class="text-sm underline">Back to messages</Link>
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
            </div>

            <section class="rounded-xl border bg-white p-5 text-sm">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-muted-foreground">Name</p>
                        <p class="font-medium">{{ message.name }}</p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Status</p>
                        <Badge :variant="message.status === 'open' ? 'default' : 'secondary'">
                            {{ message.status }}
                        </Badge>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Email</p>
                        <p class="font-medium">{{ message.email || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Phone</p>
                        <p class="font-medium">{{ message.phone || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Subject</p>
                        <p class="font-medium">{{ message.subject || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Received</p>
                        <p class="font-medium">{{ message.created_at ? new Date(message.created_at).toLocaleString() : '—' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-muted-foreground">Message</p>
                        <div class="mt-1 whitespace-pre-wrap rounded-md bg-slate-50 p-3 text-slate-800">
                            {{ message.message }}
                        </div>
                    </div>
                    <div>
                        <p class="text-muted-foreground">IP</p>
                        <p class="font-medium">{{ message.ip || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">User Agent</p>
                        <p class="font-medium break-all">{{ message.user_agent || '—' }}</p>
                    </div>
                </div>

                <div class="mt-5">
                    <Button type="button" @click="openStatusDialog">
                        {{ message.status === 'closed' ? 'Reopen Message' : 'Mark as Closed' }}
                    </Button>
                </div>
            </section>
        </div>

        <ConfirmDialog
            v-model:open="confirmOpen"
            :title="message.status === 'closed' ? 'Reopen message' : 'Mark as closed'"
            :description="
                message.status === 'closed'
                    ? 'This message will be marked as open.'
                    : 'This message will be marked as closed.'
            "
            :confirm-label="message.status === 'closed' ? 'Reopen' : 'Mark Closed'"
            @confirm="updateStatus"
        />
    </AdminLayout>
</template>
