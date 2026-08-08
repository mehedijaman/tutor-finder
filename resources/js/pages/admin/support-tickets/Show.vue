<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import TicketPriorityBadge from '@/components/support/TicketPriorityBadge.vue';
import TicketReplyForm from '@/components/support/TicketReplyForm.vue';
import TicketStatusBadge from '@/components/support/TicketStatusBadge.vue';
import TicketThread from '@/components/support/TicketThread.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { SelectOption } from '@/types';
import type { TicketDetail } from '@/types/support';

interface AdminUser {
    id: number;
    name: string;
}

const props = defineProps<{
    ticket: TicketDetail;
    currentUserId: number;
    statusOptions: SelectOption[];
    adminUsers: AdminUser[];
}>();

const breadcrumbs = [
    { title: 'Support Tickets', href: '/admin/support-tickets' },
    { title: props.ticket.ticket_number, href: '#' },
];

const selectedStatus = ref(props.ticket.status);
const selectedAdmin = ref(
    props.ticket.assigned_to ? String(props.ticket.assigned_to) : '',
);

const confirmOpen = ref(false);
const confirmTitle = ref('');
const confirmDescription = ref('');
const confirmLabel = ref('');
const pendingAction = ref<(() => void) | null>(null);

function formatCategory(category: string): string {
    return category.charAt(0).toUpperCase() + category.slice(1);
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleString();
}

function updateStatus(): void {
    if (selectedStatus.value === props.ticket.status) {
        return;
    }

    if (selectedStatus.value === 'closed') {
        confirmTitle.value = 'Close Ticket';
        confirmDescription.value =
            'Are you sure you want to close this ticket? The user will be notified.';
        confirmLabel.value = 'Close Ticket';
        pendingAction.value = () => {
            router.patch(
                `/admin/support-tickets/${props.ticket.id}/status`,
                { status: selectedStatus.value },
                { preserveScroll: true },
            );
        };
        confirmOpen.value = true;
    } else {
        router.patch(
            `/admin/support-tickets/${props.ticket.id}/status`,
            { status: selectedStatus.value },
            { preserveScroll: true },
        );
    }
}

function assignTicket(): void {
    if (!selectedAdmin.value) {
        return;
    }

    router.patch(
        `/admin/support-tickets/${props.ticket.id}/assign`,
        { assigned_to: Number(selectedAdmin.value) },
        { preserveScroll: true },
    );
}

function runConfirmedAction(): void {
    if (pendingAction.value) {
        pendingAction.value();
        pendingAction.value = null;
    }
    confirmOpen.value = false;
}

function resetConfirmState(): void {
    confirmOpen.value = false;
    pendingAction.value = null;
    selectedStatus.value = props.ticket.status;
}
</script>

<template>
    <Head :title="`Ticket ${ticket.ticket_number}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <h1
                                class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl"
                            >
                                {{ ticket.subject }}
                            </h1>
                        </div>
                        <div
                            class="flex flex-wrap items-center gap-2 text-sm text-slate-500"
                        >
                            <span class="font-mono text-xs">
                                {{ ticket.ticket_number }}
                            </span>
                            <span>&middot;</span>
                            <TicketStatusBadge :status="ticket.status" />
                            <TicketPriorityBadge :priority="ticket.priority" />
                            <Badge variant="outline">
                                {{ formatCategory(ticket.category) }}
                            </Badge>
                        </div>
                    </div>
                    <Link
                        href="/admin/support-tickets"
                        class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Back
                    </Link>
                </div>
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
            </div>

            <div class="grid min-w-0 gap-6 lg:grid-cols-3">
                <div class="min-w-0 space-y-6 lg:col-span-2">
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                    >
                        <h2 class="mb-4 text-lg font-semibold text-slate-900">
                            Conversation
                        </h2>
                        <TicketThread
                            :messages="ticket.messages"
                            :current-user-id="currentUserId"
                        />
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                    >
                        <h2 class="mb-4 text-lg font-semibold text-slate-900">
                            Reply
                        </h2>
                        <TicketReplyForm
                            :action="`/admin/support-tickets/${ticket.id}/reply`"
                            :max-attachments="3"
                        />
                    </div>
                </div>

                <div class="space-y-6">
                    <div
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                    >
                        <h3 class="mb-4 text-sm font-semibold text-slate-900">
                            Ticket Details
                        </h3>
                        <div class="space-y-3 text-sm">
                            <div>
                                <p class="text-muted-foreground">Created By</p>
                                <p class="font-medium">
                                    {{ ticket.user?.name ?? 'Unknown' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-muted-foreground">Role</p>
                                <p class="font-medium capitalize">
                                    {{ ticket.user?.role ?? '—' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-muted-foreground">Created At</p>
                                <p class="font-medium">
                                    {{ formatDate(ticket.created_at) }}
                                </p>
                            </div>
                            <div v-if="ticket.closed_at">
                                <p class="text-muted-foreground">Closed At</p>
                                <p class="font-medium">
                                    {{ formatDate(ticket.closed_at) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-muted-foreground">Messages</p>
                                <p class="font-medium">
                                    {{ ticket.messages?.length ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                    >
                        <h3 class="mb-4 text-sm font-semibold text-slate-900">
                            Manage Ticket
                        </h3>

                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label>Status</Label>
                                <Select v-model="selectedStatus">
                                    <SelectTrigger>
                                        <SelectValue
                                            placeholder="Select status"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="opt in statusOptions"
                                            :key="opt.value"
                                            :value="opt.value"
                                        >
                                            {{ opt.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="w-full"
                                    :disabled="selectedStatus === ticket.status"
                                    @click="updateStatus"
                                >
                                    Update Status
                                </Button>
                            </div>

                            <Separator />

                            <div class="space-y-2">
                                <Label>Assign To</Label>
                                <Select v-model="selectedAdmin">
                                    <SelectTrigger>
                                        <SelectValue
                                            placeholder="Select admin"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="admin in adminUsers"
                                            :key="admin.id"
                                            :value="String(admin.id)"
                                        >
                                            {{ admin.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="w-full"
                                    :disabled="
                                        !selectedAdmin ||
                                        Number(selectedAdmin) ===
                                            ticket.assigned_to
                                    "
                                    @click="assignTicket"
                                >
                                    Assign
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDialog
            v-model:open="confirmOpen"
            :title="confirmTitle"
            :description="confirmDescription"
            :confirm-label="confirmLabel"
            @confirm="runConfirmedAction"
            @cancel="resetConfirmState"
        />
    </AdminLayout>
</template>
