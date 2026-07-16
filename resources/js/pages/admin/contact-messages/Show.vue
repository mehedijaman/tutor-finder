<script setup lang="ts">
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
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl sm:text-3xl font-semibold tracking-tight text-slate-900 sm:text-3xl"
                    >
                        Contact Message #{{ message.id }}
                    </h1>
                    <p class="text-sm text-slate-600">
                        Review message metadata and update resolution status.
                    </p>
                </div>
                <Link
                    href="/admin/contact-messages"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >Back</Link
                >
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
            </div>

            <section
                class="rounded-2xl border border-slate-200/80 bg-white p-5 text-sm shadow-sm sm:p-6"
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-muted-foreground">Name</p>
                        <p class="font-medium">{{ message.name }}</p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Status</p>
                        <Badge
                            :variant="
                                message.status === 'open'
                                    ? 'default'
                                    : 'secondary'
                            "
                        >
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
                        <p class="font-medium">
                            {{
                                message.created_at
                                    ? new Date(
                                          message.created_at,
                                      ).toLocaleString()
                                    : '—'
                            }}
                        </p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-muted-foreground">Message</p>
                        <div
                            class="mt-1 rounded-md bg-slate-50 p-3 whitespace-pre-wrap text-slate-800"
                        >
                            {{ message.message }}
                        </div>
                    </div>
                    <div>
                        <p class="text-muted-foreground">IP</p>
                        <p class="font-medium">{{ message.ip || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">User Agent</p>
                        <p class="font-medium break-all">
                            {{ message.user_agent || '—' }}
                        </p>
                    </div>
                </div>

                <div class="mt-5">
                    <Button type="button" @click="openStatusDialog">
                        {{
                            message.status === 'closed'
                                ? 'Reopen Message'
                                : 'Mark as Closed'
                        }}
                    </Button>
                </div>
            </section>
        </div>

        <ConfirmDialog
            v-model:open="confirmOpen"
            :title="
                message.status === 'closed'
                    ? 'Reopen message'
                    : 'Mark as closed'
            "
            :description="
                message.status === 'closed'
                    ? 'This message will be marked as open.'
                    : 'This message will be marked as closed.'
            "
            :confirm-label="
                message.status === 'closed' ? 'Reopen' : 'Mark Closed'
            "
            @confirm="updateStatus"
        />
    </AdminLayout>
</template>
