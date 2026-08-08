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
                    class="w-full rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                >
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl"
                    >
                        Backup Management
                    </h1>
                    <p class="text-sm text-slate-600">
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
                            @click="cleanBackups"
                        >
                            Run Cleanup
                        </Button>
                    </div>
                </div>
            </div>

            <div
                v-if="resultMessage"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ resultMessage }}
            </div>

            <div
                v-if="errorMessage"
                class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"
            >
                {{ errorMessage }}
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-xl border bg-white p-4">
                    <p class="text-xs text-muted-foreground">Destinations</p>
                    <p class="mt-1 text-2xl font-semibold sm:text-3xl">
                        {{ stats.destinations_count ?? 0 }}
                    </p>
                </div>

                <div class="rounded-xl border bg-white p-4">
                    <p class="text-xs text-muted-foreground">Healthy</p>
                    <p
                        class="mt-1 text-2xl font-semibold text-emerald-700 sm:text-3xl"
                    >
                        {{ stats.healthy_count ?? 0 }}
                    </p>
                </div>

                <div class="rounded-xl border bg-white p-4">
                    <p class="text-xs text-muted-foreground">Unhealthy</p>
                    <p
                        class="mt-1 text-2xl font-semibold text-rose-700 sm:text-3xl"
                    >
                        {{ stats.unhealthy_count ?? 0 }}
                    </p>
                </div>

                <div class="rounded-xl border bg-white p-4">
                    <p class="text-xs text-muted-foreground">Backup Files</p>
                    <p class="mt-1 text-2xl font-semibold sm:text-3xl">
                        {{ stats.backup_files_count ?? 0 }}
                    </p>
                </div>

                <div class="rounded-xl border bg-white p-4">
                    <p class="text-xs text-muted-foreground">Total Storage</p>
                    <p class="mt-1 text-2xl font-semibold sm:text-3xl">
                        {{ stats.total_storage ?? '0 KB' }}
                    </p>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border bg-white">
                <div class="border-b px-4 py-3">
                    <h2 class="font-medium">Backup Destinations</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/40">
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
                        <tbody>
                            <tr
                                v-for="destination in destinations"
                                :key="`${destination.backup_name}-${destination.disk}`"
                                class="border-t align-top"
                            >
                                <td class="px-4 py-3">
                                    {{ destination.backup_name }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ destination.disk }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="
                                            destination.reachable
                                                ? 'text-emerald-700'
                                                : 'text-rose-700'
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
                                                ? 'text-emerald-700'
                                                : 'text-rose-700'
                                        "
                                    >
                                        {{
                                            destination.healthy
                                                ? 'Healthy'
                                                : 'Unhealthy'
                                        }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    {{ destination.total_backups }}
                                </td>
                                <td class="px-4 py-3">
                                    <div>
                                        {{
                                            destination.newest_backup_at || '—'
                                        }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ destination.newest_backup_age }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
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
                                            class="text-xs text-rose-700"
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
                            <tr v-if="!hasDestinations" class="border-t">
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

            <div class="overflow-hidden rounded-xl border bg-white">
                <div class="border-b px-4 py-3">
                    <h2 class="font-medium">Available Backup Files</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/40">
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
                        <tbody>
                            <tr
                                v-for="backup in backupFiles"
                                :key="`${backup.disk}-${backup.path}`"
                                class="border-t"
                            >
                                <td class="px-4 py-3 font-mono text-xs">
                                    {{ backup.name }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ backup.backup_name }}
                                </td>
                                <td class="px-4 py-3">{{ backup.disk }}</td>
                                <td class="px-4 py-3">{{ backup.size }}</td>
                                <td class="px-4 py-3">
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
                                            class="inline-flex h-8 items-center justify-center rounded-md border px-3 text-xs hover:bg-muted"
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
                            <tr v-if="!hasBackupFiles" class="border-t">
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
