<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    destinations: {
        type: Array,
        default: () => [],
    },
    backupFiles: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    permissions: {
        type: Object,
        default: () => ({}),
    },
    resultMessage: {
        type: String,
        default: null,
    },
    errorMessage: {
        type: String,
        default: null,
    },
});

const breadcrumbs = [{ title: 'Backups', href: '/admin/backups' }];

const hasDestinations = computed(() => props.destinations.length > 0);
const hasBackupFiles = computed(() => props.backupFiles.length > 0);

function runBackup() {
    router.post('/admin/backups/run', {}, { preserveScroll: true });
}

function cleanBackups() {
    router.post('/admin/backups/clean', {}, { preserveScroll: true });
}

function buildDownloadUrl(backup) {
    const query = new URLSearchParams({
        disk: backup.disk,
        backup_name: backup.backup_name,
        path: backup.path,
    });

    return `/admin/backups/download?${query.toString()}`;
}

function deleteBackup(backup) {
    if (
        !window.confirm(
            `Delete backup file "${backup.name}"? This action cannot be undone.`,
        )
    ) {
        return;
    }

    router.delete('/admin/backups/file', {
        data: {
            disk: backup.disk,
            backup_name: backup.backup_name,
            path: backup.path,
        },
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Backup Management" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div
                    class="w-full rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm sm:p-6"
                >
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100 sm:text-3xl"
                    >
                        Backup Management
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Monitor destination health and run backup operations.
                    </p>
                    <div class="mt-4 flex items-center gap-2">
                        <Button
                            v-if="permissions.can_run"
                            type="button"
                            @click="runBackup"
                        >
                            Run Backup
                        </Button>

                        <Button
                            v-if="permissions.can_clean"
                            type="button"
                            variant="outline"
                            class="dark:border-slate-700 dark:text-slate-300"
                            @click="cleanBackups"
                        >
                            Run Cleanup
                        </Button>
                    </div>
                </div>
            </div>

            <div
                v-if="resultMessage"
                class="rounded-lg border border-emerald-200 dark:border-emerald-900/60 bg-emerald-50 dark:bg-emerald-950/40 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-300"
            >
                {{ resultMessage }}
            </div>

            <div
                v-if="errorMessage"
                class="rounded-lg border border-rose-200 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/40 px-4 py-3 text-sm text-rose-800 dark:text-rose-300"
            >
                {{ errorMessage }}
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm">
                    <p class="text-xs text-muted-foreground">Destinations</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-slate-100 sm:text-3xl">
                        {{ stats.destinations_count ?? 0 }}
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm">
                    <p class="text-xs text-muted-foreground">Healthy</p>
                    <p
                        class="mt-1 text-2xl font-semibold text-emerald-700 dark:text-emerald-400 sm:text-3xl"
                    >
                        {{ stats.healthy_count ?? 0 }}
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm">
                    <p class="text-xs text-muted-foreground">Unhealthy</p>
                    <p
                        class="mt-1 text-2xl font-semibold text-rose-700 dark:text-rose-400 sm:text-3xl"
                    >
                        {{ stats.unhealthy_count ?? 0 }}
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm">
                    <p class="text-xs text-muted-foreground">Backup Files</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-slate-100 sm:text-3xl">
                        {{ stats.backup_files_count ?? 0 }}
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm">
                    <p class="text-xs text-muted-foreground">Total Storage</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-slate-100 sm:text-3xl">
                        {{ stats.total_storage ?? '0 KB' }}
                    </p>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
                <div class="border-b border-slate-100 dark:border-slate-800 px-4 py-3">
                    <h2 class="font-medium text-slate-900 dark:text-slate-100">Backup Destinations</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Disk</th>
                                <th class="px-4 py-3">Reachable</th>
                                <th class="px-4 py-3">Healthy</th>
                                <th class="px-4 py-3">Backups</th>
                                <th class="px-4 py-3">Newest Backup</th>
                                <th class="px-4 py-3">Used Storage</th>
                                <th class="px-4 py-3">Failures</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr
                                v-for="destination in destinations"
                                :key="`${destination.backup_name}-${destination.disk}`"
                                class="align-top transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/50"
                            >
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                    {{ destination.backup_name }}
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    {{ destination.disk }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="
                                            destination.reachable
                                                ? 'text-emerald-700 dark:text-emerald-400'
                                                : 'text-rose-700 dark:text-rose-400'
                                        "
                                    >
                                        {{
                                            destination.reachable ? 'Yes' : 'No'
                                        }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="
                                            destination.healthy
                                                ? 'text-emerald-700 dark:text-emerald-400'
                                                : 'text-rose-700 dark:text-rose-400'
                                        "
                                    >
                                        {{
                                            destination.healthy
                                                ? 'Healthy'
                                                : 'Unhealthy'
                                        }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    {{ destination.total_backups }}
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    <div>
                                        {{
                                            destination.newest_backup_at || '—'
                                        }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ destination.newest_backup_age }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    {{ destination.used_storage }}
                                </td>
                                <td class="px-4 py-3">
                                    <div
                                        v-if="destination.failures?.length"
                                        class="space-y-1"
                                    >
                                        <p
                                            v-for="(
                                                failure, index
                                            ) in destination.failures"
                                            :key="`${destination.disk}-${index}`"
                                            class="text-xs text-rose-700 dark:text-rose-400"
                                        >
                                            {{ failure.check }}:
                                            {{ failure.message }}
                                        </p>
                                    </div>
                                    <span v-else class="text-muted-foreground"
                                        >—</span
                                    >
                                </td>
                            </tr>
                            <tr v-if="!hasDestinations">
                                <td
                                    class="px-4 py-6 text-center text-muted-foreground"
                                    colspan="8"
                                >
                                    No monitored backup destinations found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
                <div class="border-b border-slate-100 dark:border-slate-800 px-4 py-3">
                    <h2 class="font-medium text-slate-900 dark:text-slate-100">Available Backup Files</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-3">File</th>
                                <th class="px-4 py-3">Backup Name</th>
                                <th class="px-4 py-3">Disk</th>
                                <th class="px-4 py-3">Size</th>
                                <th class="px-4 py-3">Created At</th>
                                <th class="px-4 py-3">Age</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr
                                v-for="backup in backupFiles"
                                :key="`${backup.disk}-${backup.path}`"
                                class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/50"
                            >
                                <td class="px-4 py-3 font-mono text-xs text-slate-900 dark:text-slate-100">
                                    {{ backup.name }}
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    {{ backup.backup_name }}
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ backup.disk }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ backup.size }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    {{ backup.created_at }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ backup.age }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a
                                            v-if="permissions.can_download"
                                            :href="buildDownloadUrl(backup)"
                                            class="inline-flex h-8 items-center justify-center rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 text-xs text-slate-700 dark:text-slate-200 transition hover:bg-slate-50 dark:hover:bg-slate-700"
                                        >
                                            Download
                                        </a>

                                        <Button
                                            v-if="permissions.can_delete"
                                            type="button"
                                            variant="destructive"
                                            size="sm"
                                            @click="deleteBackup(backup)"
                                        >
                                            Delete
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!hasBackupFiles">
                                <td
                                    class="px-4 py-6 text-center text-muted-foreground"
                                    colspan="7"
                                >
                                    No backup files are currently available.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
