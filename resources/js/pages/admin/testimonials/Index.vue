<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Star, Upload, X } from 'lucide-vue-next';
import { onBeforeUnmount, ref, watch } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import RowActionsDropdown from '@/components/admin/table/RowActionsDropdown.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/AdminLayout.vue';

type TestimonialItem = {
    id: number;
    user_id: number | null;
    name: string;
    role: string | null;
    avatar_url: string | null;
    content: string;
    rating: number;
    status: string;
    sort_order: number;
    updated_at: string | null;
    deleted_at: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedItems = {
    data: TestimonialItem[];
    current_page: number;
    last_page: number;
    links: PaginationLink[];
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
};

type PendingAction =
    'delete' | 'restore' | 'force-delete' | 'empty-recycle-bin';

const props = defineProps<{
    items: PaginatedItems;
    filters: {
        trash: boolean;
        q: string;
    };
    counts: {
        active: number;
        trash: number;
    };
}>();

const breadcrumbs = [{ title: 'Testimonials', href: '/admin/testimonials' }];
const baseUrl = '/admin/testimonials';

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'role', label: 'Role' },
    { key: 'content', label: 'Content', cellClass: 'max-w-xs' },
    { key: 'rating', label: 'Rating' },
    { key: 'status', label: 'Status' },
    { key: 'sort_order', label: 'Sort' },
    { key: 'updated_at', label: 'Updated At' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.q ?? '');
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

watch(
    () => props.filters.q,
    (value) => {
        const normalized = value ?? '';

        if (normalized !== search.value) {
            search.value = normalized;
        }
    },
);

watch(search, (value) => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }

    searchDebounceTimer = setTimeout(() => {
        applyFilters({ q: value, page: 1 });
    }, 350);
});

onBeforeUnmount(() => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }

    clearTemporaryAvatarUrl();
});

function applyFilters(overrides: Record<string, string | number | null> = {}) {
    router.get(
        baseUrl,
        {
            trash: props.filters.trash ? 1 : 0,
            q: search.value,
            ...overrides,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

const dialogOpen = ref(false);
const editingItem = ref<TestimonialItem | null>(null);
const form = useForm<{
    user_id: number | null;
    name: string;
    role: string;
    avatar_url: string;
    avatar: File | null;
    remove_avatar: boolean;
    content: string;
    rating: number;
    status: string;
    sort_order: number;
}>({
    user_id: null,
    name: '',
    role: '',
    avatar_url: '',
    avatar: null,
    remove_avatar: false,
    content: '',
    rating: 5,
    status: 'active',
    sort_order: 0,
});

const hoveredRating = ref(0);

function ratingLabel(rating: number): string {
    const labels: Record<number, string> = {
        1: 'Poor',
        2: 'Fair',
        3: 'Good',
        4: 'Very Good',
        5: 'Excellent',
    };
    return labels[rating] ?? '';
}

function setRating(rating: number): void {
    form.rating = rating;
}

const fileInputRef = ref<HTMLInputElement | null>(null);
const avatarPreviewUrl = ref<string | null>(null);
const temporaryAvatarObjectUrl = ref<string | null>(null);
const isAvatarDragging = ref(false);

function clearTemporaryAvatarUrl(): void {
    if (temporaryAvatarObjectUrl.value) {
        URL.revokeObjectURL(temporaryAvatarObjectUrl.value);
        temporaryAvatarObjectUrl.value = null;
    }
}

function updateAvatarPreview(
    file: File | null,
    existingUrl: string | null,
): void {
    clearTemporaryAvatarUrl();

    if (file) {
        temporaryAvatarObjectUrl.value = URL.createObjectURL(file);
        avatarPreviewUrl.value = temporaryAvatarObjectUrl.value;

        return;
    }

    avatarPreviewUrl.value = existingUrl || null;
}

function triggerAvatarInput(): void {
    fileInputRef.value?.click();
}

function handleSelectedAvatarFile(file: File | undefined): void {
    if (!file) {
        return;
    }

    if (!file.type.startsWith('image/')) {
        return;
    }

    form.avatar = file;
    form.remove_avatar = false;
    updateAvatarPreview(file, form.avatar_url || null);
}

function onAvatarChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    handleSelectedAvatarFile(file);
}

function onAvatarDrop(event: DragEvent): void {
    isAvatarDragging.value = false;
    const file = event.dataTransfer?.files?.[0];
    handleSelectedAvatarFile(file);
}

function onAvatarDragOver(): void {
    isAvatarDragging.value = true;
}

function onAvatarDragLeave(): void {
    isAvatarDragging.value = false;
}

function removeAvatar(): void {
    form.avatar = null;
    form.remove_avatar = true;
    updateAvatarPreview(null, null);

    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
}

function openCreateDialog(): void {
    editingItem.value = null;
    form.reset();
    form.clearErrors();
    form.avatar_url = '';
    form.avatar = null;
    form.remove_avatar = false;
    form.rating = 5;
    form.status = 'active';
    form.sort_order = 0;
    updateAvatarPreview(null, null);
    dialogOpen.value = true;
}

function openEditDialog(item: TestimonialItem): void {
    editingItem.value = item;
    form.clearErrors();
    form.user_id = item.user_id;
    form.name = item.name;
    form.role = item.role ?? '';
    form.avatar_url = item.avatar_url ?? '';
    form.avatar = null;
    form.remove_avatar = false;
    form.content = item.content;
    form.rating = item.rating;
    form.status = item.status;
    form.sort_order = item.sort_order;
    updateAvatarPreview(null, item.avatar_url);
    dialogOpen.value = true;
}

function closeFormDialog(): void {
    dialogOpen.value = false;
    editingItem.value = null;
    form.reset();
    form.clearErrors();
    updateAvatarPreview(null, null);
}

function submitForm(): void {
    const isEdit = Boolean(editingItem.value);

    form.transform((data) => ({
        ...data,
        _method: isEdit ? 'PUT' : 'POST',
    })).post(
        isEdit
            ? `/admin/testimonials/${editingItem.value?.id}`
            : '/admin/testimonials',
        {
            preserveScroll: true,
            onSuccess: () => {
                closeFormDialog();
            },
        },
    );
}

const confirmOpen = ref(false);
const confirmTitle = ref('');
const confirmDescription = ref('');
const confirmLabel = ref('Confirm');
const confirmDestructive = ref(false);
const pendingActionState = ref<{
    action: PendingAction;
    row: TestimonialItem | null;
} | null>(null);

function openConfirm(
    action: PendingAction,
    row: TestimonialItem | null = null,
): void {
    pendingActionState.value = { action, row };

    if (action === 'delete') {
        confirmTitle.value = 'Move to Recycle Bin';
        confirmDescription.value = `Are you sure you want to move "${row?.name}" to the recycle bin?`;
        confirmLabel.value = 'Move to Recycle Bin';
        confirmDestructive.value = true;
    } else if (action === 'restore') {
        confirmTitle.value = 'Restore Testimonial';
        confirmDescription.value = `Are you sure you want to restore "${row?.name}"?`;
        confirmLabel.value = 'Restore';
        confirmDestructive.value = false;
    } else if (action === 'force-delete') {
        confirmTitle.value = 'Permanently Delete Testimonial';
        confirmDescription.value = `Are you sure you want to permanently delete "${row?.name}"? This action cannot be undone.`;
        confirmLabel.value = 'Permanently Delete';
        confirmDestructive.value = true;
    } else if (action === 'empty-recycle-bin') {
        confirmTitle.value = 'Empty Recycle Bin';
        confirmDescription.value =
            'Are you sure you want to permanently delete all testimonials in the recycle bin? This action cannot be undone.';
        confirmLabel.value = 'Empty Recycle Bin';
        confirmDestructive.value = true;
    }

    confirmOpen.value = true;
}

function resetConfirmState(): void {
    pendingActionState.value = null;
    confirmTitle.value = '';
    confirmDescription.value = '';
    confirmLabel.value = 'Confirm';
    confirmDestructive.value = false;
}

function runConfirmedAction(): void {
    if (!pendingActionState.value) {
        return;
    }

    const { action, row } = pendingActionState.value;

    if (action === 'delete' && row) {
        router.delete(`/admin/testimonials/${row.id}`, {
            preserveScroll: true,
            onFinish: () => resetConfirmState(),
        });
    } else if (action === 'restore' && row) {
        router.patch(
            `/admin/testimonials/${row.id}/restore`,
            {},
            {
                preserveScroll: true,
                onFinish: () => resetConfirmState(),
            },
        );
    } else if (action === 'force-delete' && row) {
        router.delete(`/admin/testimonials/${row.id}/force-delete`, {
            preserveScroll: true,
            onFinish: () => resetConfirmState(),
        });
    } else if (action === 'empty-recycle-bin') {
        router.delete('/admin/testimonials/empty-trash', {
            preserveScroll: true,
            onFinish: () => resetConfirmState(),
        });
    }

    confirmOpen.value = false;
    resetConfirmState();
}

function actionItemsForRow(): Array<{
    key: string;
    label: string;
    destructive?: boolean;
}> {
    if (props.filters.trash) {
        return [
            { key: 'restore', label: 'Restore' },
            {
                key: 'force-delete',
                label: 'Permanently Delete',
                destructive: true,
            },
        ];
    }

    return [
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete', destructive: true },
    ];
}

function handleRowAction(action: string, row: TestimonialItem): void {
    if (action === 'edit') {
        openEditDialog(row);

        return;
    }

    if (
        action === 'delete' ||
        action === 'restore' ||
        action === 'force-delete'
    ) {
        openConfirm(action as PendingAction, row);
    }
}
</script>

<template>
    <Head title="Testimonials" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold text-slate-900 sm:text-3xl dark:text-slate-100"
                    >
                        {{
                            filters.trash
                                ? 'Testimonial Recycle Bin'
                                : 'Testimonials'
                        }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Active: {{ counts.active ?? 0 }} | Trash:
                        {{ counts.trash ?? 0 }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="filters.trash ? baseUrl : `${baseUrl}?trash=1`"
                        class="rounded-md border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        {{ filters.trash ? 'Back to Active' : 'Recycle Bin' }}
                    </Link>

                    <Button
                        v-if="filters.trash"
                        type="button"
                        variant="destructive"
                        :disabled="counts.trash === 0"
                        @click="openConfirm('empty-recycle-bin')"
                    >
                        Empty Recycle Bin
                    </Button>

                    <Button
                        v-if="!filters.trash"
                        type="button"
                        @click="openCreateDialog"
                    >
                        Add Testimonial
                    </Button>
                </div>
            </div>

            <div
                class="rounded-xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900"
            >
                <Input
                    v-model="search"
                    type="text"
                    placeholder="Search by name, role, or content"
                    class="max-w-lg dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                />
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No testimonials found."
            >
                <template #cell-name="{ value }">
                    <span
                        class="font-medium text-slate-900 dark:text-slate-100"
                        >{{ value }}</span
                    >
                </template>

                <template #cell-role="{ value }">
                    <span class="text-slate-700 dark:text-slate-300">{{
                        value || '—'
                    }}</span>
                </template>

                <template #cell-content="{ value }">
                    <p
                        class="line-clamp-2 text-sm text-slate-600 dark:text-slate-300"
                    >
                        {{ value }}
                    </p>
                </template>

                <template #cell-rating="{ value }">
                    <div class="flex items-center gap-1">
                        <span
                            class="font-medium text-slate-900 dark:text-slate-100"
                            >{{ value }}/5</span
                        >
                    </div>
                </template>

                <template #cell-status="{ value }">
                    <Badge
                        :variant="value === 'active' ? 'default' : 'secondary'"
                    >
                        {{ value }}
                    </Badge>
                </template>

                <template #cell-updated_at="{ value }">
                    <span class="text-slate-700 dark:text-slate-300">
                        {{ value ? new Date(value).toLocaleString() : '—' }}
                    </span>
                </template>

                <template #cell-actions="{ row }">
                    <RowActionsDropdown
                        :actions="actionItemsForRow()"
                        @select="(action) => handleRowAction(action, row)"
                    />
                </template>
            </DataTable>
        </div>

        <Dialog
            :open="dialogOpen"
            @update:open="
                (value) => (value ? (dialogOpen = true) : closeFormDialog())
            "
        >
            <DialogContent
                class="sm:max-w-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <DialogHeader>
                    <DialogTitle class="text-slate-900 dark:text-slate-100">
                        {{
                            editingItem ? 'Edit Testimonial' : 'Add Testimonial'
                        }}
                    </DialogTitle>
                </DialogHeader>

                <form class="space-y-4" @submit.prevent="submitForm">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label
                                for="testimonial-name"
                                class="text-slate-800 dark:text-slate-200"
                                >Name</Label
                            >
                            <Input
                                id="testimonial-name"
                                v-model="form.name"
                                type="text"
                                required
                                class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label
                                for="testimonial-role"
                                class="text-slate-800 dark:text-slate-200"
                                >Role</Label
                            >
                            <Input
                                id="testimonial-role"
                                v-model="form.role"
                                type="text"
                                class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            />
                            <InputError :message="form.errors.role" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label class="text-slate-800 dark:text-slate-200"
                            >Avatar (Upload)</Label
                        >
                        <div
                            v-if="avatarPreviewUrl"
                            class="relative w-fit overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700"
                        >
                            <img
                                :src="avatarPreviewUrl"
                                alt="Avatar preview"
                                class="h-20 w-20 object-cover"
                            />
                            <button
                                type="button"
                                class="absolute top-1 right-1 rounded-full bg-red-500 p-1 text-white shadow-sm hover:bg-red-600"
                                @click="removeAvatar"
                            >
                                <X class="h-3 w-3" />
                            </button>
                        </div>
                        <div
                            v-if="form.remove_avatar"
                            class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300"
                        >
                            Avatar will be removed on save.
                        </div>
                        <div
                            class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed px-4 py-6 text-center transition-colors"
                            :class="
                                isAvatarDragging
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/40'
                                    : 'border-slate-300 bg-slate-50 hover:border-blue-400 hover:bg-blue-50/50 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700/50'
                            "
                            @click="triggerAvatarInput"
                            @drop.prevent="onAvatarDrop"
                            @dragover.prevent="onAvatarDragOver"
                            @dragleave.prevent="onAvatarDragLeave"
                        >
                            <Upload
                                class="mb-2 h-8 w-8 text-slate-400 dark:text-slate-500"
                            />
                            <p
                                class="text-sm font-medium text-slate-600 dark:text-slate-300"
                            >
                                {{
                                    isAvatarDragging
                                        ? 'Drop avatar image here'
                                        : 'Click or drag & drop avatar'
                                }}
                            </p>
                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                PNG, JPG up to 2MB.
                            </p>
                            <input
                                ref="fileInputRef"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="onAvatarChange"
                            />
                        </div>
                        <InputError :message="form.errors.avatar" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label class="text-slate-800 dark:text-slate-200"
                                >Rating</Label
                            >
                            <div class="flex items-center gap-1">
                                <button
                                    v-for="star in 5"
                                    :key="star"
                                    type="button"
                                    class="rounded-sm p-0.5 transition-transform hover:scale-110 focus:ring-2 focus:ring-amber-400 focus:ring-offset-1 focus:outline-none"
                                    @click="setRating(star)"
                                    @mouseenter="hoveredRating = star"
                                    @mouseleave="hoveredRating = 0"
                                >
                                    <Star
                                        :class="[
                                            'h-6 w-6 transition-colors',
                                            star <=
                                            (hoveredRating || form.rating)
                                                ? 'fill-amber-400 text-amber-400'
                                                : 'fill-slate-200 text-slate-300 dark:fill-slate-700 dark:text-slate-600',
                                        ]"
                                    />
                                </button>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{
                                    form.rating > 0
                                        ? `${form.rating}/5 - ${ratingLabel(form.rating)}`
                                        : 'Select a rating'
                                }}
                            </p>
                            <InputError :message="form.errors.rating" />
                        </div>

                        <div class="grid gap-2">
                            <Label
                                for="testimonial-status"
                                class="text-slate-800 dark:text-slate-200"
                                >Status</Label
                            >
                            <div
                                class="flex items-center gap-3 rounded-md border border-slate-200 bg-white px-3 py-2 text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            >
                                <Switch
                                    id="testimonial-status"
                                    :model-value="form.status === 'active'"
                                    @update:model-value="
                                        (value) =>
                                            (form.status = value
                                                ? 'active'
                                                : 'inactive')
                                    "
                                />
                                <span class="text-sm font-medium">
                                    {{
                                        form.status === 'active'
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </span>
                            </div>
                            <InputError :message="form.errors.status" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label
                            for="testimonial-content"
                            class="text-slate-800 dark:text-slate-200"
                            >Content</Label
                        >
                        <Textarea
                            id="testimonial-content"
                            v-model="form.content"
                            rows="5"
                            class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        />
                        <InputError :message="form.errors.content" />
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            class="dark:border-slate-700 dark:text-slate-300"
                            :disabled="form.processing"
                            @click="closeFormDialog"
                        >
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ editingItem ? 'Update' : 'Create' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <ConfirmDialog
            v-model:open="confirmOpen"
            :title="confirmTitle"
            :description="confirmDescription"
            :confirm-label="confirmLabel"
            :destructive="confirmDestructive"
            @confirm="runConfirmedAction"
            @cancel="resetConfirmState"
        />
    </AdminLayout>
</template>
