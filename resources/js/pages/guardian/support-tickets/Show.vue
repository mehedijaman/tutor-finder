<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import TicketPriorityBadge from '@/components/support/TicketPriorityBadge.vue';
import TicketReplyForm from '@/components/support/TicketReplyForm.vue';
import TicketStatusBadge from '@/components/support/TicketStatusBadge.vue';
import TicketThread from '@/components/support/TicketThread.vue';
import { Badge } from '@/components/ui/badge';
import GuardianLayout from '@/layouts/GuardianLayout.vue';
import type { TicketDetail } from '@/types/support';

const props = defineProps<{
    ticket: TicketDetail;
    currentUserId: number;
}>();

const breadcrumbs = [
    { title: 'Support Tickets', href: '/guardian/support-tickets' },
    { title: props.ticket.ticket_number, href: '#' },
];

function formatCategory(category: string): string {
    return category.charAt(0).toUpperCase() + category.slice(1);
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleString();
}
</script>

<template>
    <Head :title="`Ticket ${ticket.ticket_number}`" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <h1
                                class="text-2xl sm:text-3xl font-semibold tracking-tight text-slate-900"
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
                        <p class="text-xs text-slate-500">
                            Created {{ formatDate(ticket.created_at) }}
                        </p>
                    </div>
                    <Link
                        href="/guardian/support-tickets"
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

            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
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
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Reply</h2>
                <TicketReplyForm
                    :action="`/guardian/support-tickets/${ticket.id}/reply`"
                    :max-attachments="3"
                />
            </div>
        </div>
    </GuardianLayout>
</template>
