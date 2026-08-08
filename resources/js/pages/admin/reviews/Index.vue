<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    CheckCircle,
    Edit3,
    ExternalLink,
    RotateCcw,
    Star,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

import StarRating from '@/components/tutors/StarRating.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/AdminLayout.vue';

type ReviewTutor = {
    id: number;
    name: string;
    photo_url: string | null;
};

type ReviewGuardian = {
    id: number;
    name: string;
};

type ReviewJob = {
    id: number;
    title: string;
};

type ReviewAssignment = {
    id: number;
    job_id: number;
    job: ReviewJob | null;
};

type Review = {
    id: number;
    tutor_user_id: number;
    guardian_user_id: number;
    rating: number;
    comment: string | null;
    created_at: string;
    updated_at: string;
    tutor: ReviewTutor;
    guardian: ReviewGuardian;
    job_assignment: ReviewAssignment | null;
};

type PaginatedReviews = {
    data: Review[];
    current_page: number;
    last_page: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
};

const props = defineProps<{
    reviews: PaginatedReviews;
    filters: {
        q?: string;
        rating?: string | number;
        trash?: boolean;
    };
    trashedCount: number;
}>();

const breadcrumbs = [{ title: 'Tutor Reviews', href: '/admin/reviews' }];

const page = usePage();
const successMessage = computed(
    () => (page.props.flash as { success?: string })?.success,
);

const baseUrl = '/admin/reviews';
const search = ref(props.filters.q || '');
const ratingFilter = ref(
    props.filters.rating ? String(props.filters.rating) : 'all',
);

let debounceTimer: ReturnType<typeof setTimeout>;

watch(
    () => props.filters,
    (value) => {
        search.value = value.q || '';
        ratingFilter.value = value.rating ? String(value.rating) : 'all';
    },
);

watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 350);
});

watch(ratingFilter, () => {
    applyFilters();
});

function applyFilters(): void {
    const params: Record<string, string> = {};
    if (search.value) {
        params.q = search.value;
    }
    if (ratingFilter.value && ratingFilter.value !== 'all') {
        params.rating = ratingFilter.value;
    }
    if (props.filters.trash) {
        params.trash = '1';
    }

    router.get(baseUrl, params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

// --- Edit Review ---
const editingReview = ref<Review | null>(null);
const editHoveredStar = ref(0);

const editForm = useForm({
    rating: 0,
    comment: '',
});

function startEdit(review: Review): void {
    editingReview.value = review;
    editForm.rating = review.rating;
    editForm.comment = review.comment ?? '';
    editHoveredStar.value = 0;
}

function cancelEdit(): void {
    editingReview.value = null;
    editForm.reset();
    editHoveredStar.value = 0;
}

function submitEdit(): void {
    if (!editingReview.value) {
        return;
    }

    editForm.put(`${baseUrl}/${editingReview.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingReview.value = null;
            editForm.reset();
            editHoveredStar.value = 0;
        },
    });
}

// --- Delete Review ---
const deleteDialogOpen = ref(false);
const reviewToDelete = ref<Review | null>(null);
const isDeleting = ref(false);

function confirmDelete(review: Review): void {
    reviewToDelete.value = review;
    deleteDialogOpen.value = true;
}

function handleDelete(): void {
    if (!reviewToDelete.value) {
        return;
    }

    isDeleting.value = true;
    router.delete(`${baseUrl}/${reviewToDelete.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
            deleteDialogOpen.value = false;
            reviewToDelete.value = null;
        },
    });
}

// --- Restore Review ---
const isRestoring = ref(false);
const restoreDialogOpen = ref(false);
const reviewToRestore = ref<Review | null>(null);

function confirmRestore(review: Review): void {
    reviewToRestore.value = review;
    restoreDialogOpen.value = true;
}

function handleRestore(): void {
    if (!reviewToRestore.value) {
        return;
    }

    isRestoring.value = true;
    router.patch(
        `${baseUrl}/${reviewToRestore.value.id}/restore`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isRestoring.value = false;
                restoreDialogOpen.value = false;
                reviewToRestore.value = null;
            },
        },
    );
}

// --- Permanent Delete ---
const forceDeleteDialogOpen = ref(false);
const reviewToForceDelete = ref<Review | null>(null);
const isForceDeleting = ref(false);

function confirmForceDelete(review: Review): void {
    reviewToForceDelete.value = review;
    forceDeleteDialogOpen.value = true;
}

function handleForceDelete(): void {
    if (!reviewToForceDelete.value) {
        return;
    }

    isForceDeleting.value = true;
    router.delete(`${baseUrl}/${reviewToForceDelete.value.id}/force-delete`, {
        preserveScroll: true,
        onFinish: () => {
            isForceDeleting.value = false;
            forceDeleteDialogOpen.value = false;
            reviewToForceDelete.value = null;
        },
    });
}

// --- Restore All ---
const restoreAllDialogOpen = ref(false);
const isRestoringAll = ref(false);

function handleRestoreAll(): void {
    isRestoringAll.value = true;
    router.patch(
        `${baseUrl}/restore-all`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isRestoringAll.value = false;
                restoreAllDialogOpen.value = false;
            },
        },
    );
}

// --- Empty Recycle Bin ---
const emptyTrashDialogOpen = ref(false);
const isEmptyingTrash = ref(false);

function handleEmptyTrash(): void {
    isEmptyingTrash.value = true;
    router.delete(`${baseUrl}/empty-trash`, {
        preserveScroll: true,
        onFinish: () => {
            isEmptyingTrash.value = false;
            emptyTrashDialogOpen.value = false;
        },
    });
}

// --- Helpers ---
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

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function getInitial(name: string): string {
    return name.charAt(0).toUpperCase();
}

function formatPaginationLabel(label: string): string {
    return String(label ?? '')
        .replaceAll('&laquo;', '\u00AB')
        .replaceAll('&raquo;', '\u00BB')
        .replace(/<[^>]*>/g, '')
        .trim();
}
</script>

<template>
    <Head title="Tutor Reviews" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Page Header -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <h1
                            class="text-2xl font-semibold tracking-tight sm:text-3xl"
                        >
                            {{
                                props.filters.trash
                                    ? 'Recycle Bin'
                                    : 'Tutor Reviews'
                            }}
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            {{
                                props.filters.trash
                                    ? 'Manage deleted tutor reviews.'
                                    : 'Manage all tutor reviews submitted by guardians.'
                            }}
                            Total: {{ reviews.total }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="!props.filters.trash"
                            variant="outline"
                            size="sm"
                            :disabled="trashedCount === 0"
                            as-child
                        >
                            <Link :href="`${baseUrl}?trash=1`" preserve-scroll>
                                <Trash2 class="mr-2 h-4 w-4" />
                                Recycle Bin ({{ trashedCount }})
                            </Link>
                        </Button>
                        <Button
                            v-if="props.filters.trash"
                            variant="outline"
                            size="sm"
                            as-child
                        >
                            <Link :href="baseUrl" preserve-scroll>
                                Back to Reviews
                            </Link>
                        </Button>
                        <Button
                            v-if="props.filters.trash && reviews.total > 0"
                            variant="outline"
                            size="sm"
                            @click="restoreAllDialogOpen = true"
                        >
                            <RotateCcw class="mr-2 h-4 w-4" />
                            Restore All
                        </Button>
                        <Button
                            v-if="props.filters.trash && reviews.total > 0"
                            variant="destructive"
                            size="sm"
                            @click="emptyTrashDialogOpen = true"
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Empty Recycle Bin
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Flash Message -->
            <div
                v-if="successMessage"
                class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
            >
                <CheckCircle class="h-4.5 w-4.5 flex-shrink-0" />
                {{ successMessage }}
            </div>

            <!-- Filters -->
            <div
                v-if="!props.filters.trash"
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:grid-cols-2"
            >
                <div class="grid gap-2">
                    <Label for="review-search">Search</Label>
                    <Input
                        id="review-search"
                        v-model="search"
                        type="text"
                        placeholder="Search by tutor, guardian, or comment"
                    />
                </div>

                <div class="grid gap-2">
                    <Label>Rating</Label>
                    <Select v-model="ratingFilter">
                        <SelectTrigger>
                            <SelectValue placeholder="All ratings" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Ratings</SelectItem>
                            <SelectItem
                                v-for="n in 5"
                                :key="n"
                                :value="String(n)"
                            >
                                {{ n }}
                                {{ n === 1 ? 'Star' : 'Stars' }}
                                — {{ ratingLabel(n) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Reviews List -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white shadow-sm"
            >
                <div
                    v-if="reviews.data.length > 0"
                    class="divide-y divide-slate-100"
                >
                    <div
                        v-for="review in reviews.data"
                        :key="review.id"
                        class="p-5 sm:p-6"
                    >
                        <!-- Editing Mode -->
                        <div
                            v-if="
                                editingReview && editingReview.id === review.id
                            "
                            class="space-y-5"
                        >
                            <div class="flex items-center justify-between">
                                <h3
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    Edit Review #{{ review.id }}
                                </h3>
                                <button
                                    type="button"
                                    class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                                    @click="cancelEdit"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>

                            <!-- Edit Star Rating -->
                            <div>
                                <Label
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Rating
                                </Label>
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-1">
                                        <button
                                            v-for="star in 5"
                                            :key="star"
                                            type="button"
                                            class="cursor-pointer rounded-sm p-0.5 transition-transform hover:scale-125 focus:ring-2 focus:ring-amber-400 focus:ring-offset-1 focus:outline-none"
                                            @click="editForm.rating = star"
                                            @mouseenter="editHoveredStar = star"
                                            @mouseleave="editHoveredStar = 0"
                                        >
                                            <Star
                                                :class="[
                                                    'h-6 w-6 transition-colors',
                                                    star <=
                                                    (editHoveredStar ||
                                                        editForm.rating)
                                                        ? 'fill-amber-400 text-amber-400'
                                                        : 'fill-slate-200 text-slate-300',
                                                ]"
                                            />
                                        </button>
                                    </div>
                                    <span
                                        v-if="editForm.rating > 0"
                                        class="text-sm font-medium text-amber-600"
                                    >
                                        {{ ratingLabel(editForm.rating) }}
                                    </span>
                                </div>
                                <p
                                    v-if="editForm.errors.rating"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ editForm.errors.rating }}
                                </p>
                            </div>

                            <!-- Edit Comment -->
                            <div>
                                <Label
                                    :for="`edit-comment-${review.id}`"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Comment
                                    <span class="text-slate-400"
                                        >(optional)</span
                                    >
                                </Label>
                                <Textarea
                                    :id="`edit-comment-${review.id}`"
                                    v-model="editForm.comment"
                                    placeholder="Update the review..."
                                    :rows="3"
                                    class="resize-none"
                                />
                                <div
                                    class="mt-1 flex items-center justify-between"
                                >
                                    <p
                                        v-if="editForm.errors.comment"
                                        class="text-sm text-red-600"
                                    >
                                        {{ editForm.errors.comment }}
                                    </p>
                                    <span
                                        class="ml-auto text-xs text-slate-400"
                                    >
                                        {{ (editForm.comment ?? '').length }}
                                        / 2000
                                    </span>
                                </div>
                            </div>

                            <!-- Edit Actions -->
                            <div class="flex items-center gap-3">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="editForm.processing"
                                    @click="cancelEdit"
                                >
                                    Cancel
                                </Button>
                                <Button
                                    size="sm"
                                    :disabled="
                                        editForm.processing ||
                                        editForm.rating === 0
                                    "
                                    @click="submitEdit"
                                >
                                    {{
                                        editForm.processing
                                            ? 'Saving...'
                                            : 'Save Changes'
                                    }}
                                </Button>
                            </div>
                        </div>

                        <!-- Display Mode -->
                        <div v-else class="flex items-start gap-4">
                            <!-- Tutor Avatar -->
                            <div class="flex-shrink-0">
                                <div
                                    v-if="review.tutor.photo_url"
                                    class="h-11 w-11 overflow-hidden rounded-full"
                                >
                                    <img
                                        :src="review.tutor.photo_url"
                                        :alt="review.tutor.name"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                                <div
                                    v-else
                                    class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600"
                                >
                                    {{ getInitial(review.tutor.name) }}
                                </div>
                            </div>

                            <!-- Review Content -->
                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex flex-wrap items-center gap-x-3 gap-y-1"
                                >
                                    <span
                                        class="text-sm font-semibold text-slate-900"
                                    >
                                        {{ review.tutor.name }}
                                    </span>
                                    <span class="text-xs text-slate-400">
                                        reviewed by
                                    </span>
                                    <span
                                        class="text-sm font-medium text-slate-700"
                                    >
                                        {{ review.guardian.name }}
                                    </span>
                                    <span class="text-xs text-slate-400">
                                        {{ formatDate(review.created_at) }}
                                    </span>
                                </div>

                                <div
                                    v-if="review.job_assignment?.job"
                                    class="mt-1"
                                >
                                    <Badge
                                        variant="outline"
                                        class="text-xs font-normal"
                                    >
                                        {{ review.job_assignment.job.title }}
                                    </Badge>
                                </div>

                                <div class="mt-1.5 flex items-center gap-2">
                                    <StarRating
                                        :rating="review.rating"
                                        size="sm"
                                    />
                                    <span
                                        class="text-xs font-medium text-amber-600"
                                    >
                                        {{ ratingLabel(review.rating) }}
                                    </span>
                                </div>

                                <p
                                    v-if="review.comment"
                                    class="mt-2 text-sm leading-relaxed text-slate-600"
                                >
                                    {{ review.comment }}
                                </p>
                                <p
                                    v-else
                                    class="mt-2 text-sm text-slate-400 italic"
                                >
                                    No comment provided.
                                </p>

                                <div class="mt-2 text-xs text-slate-400">
                                    Review #{{ review.id }}
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-shrink-0 items-center gap-1">
                                <template v-if="props.filters.trash">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="confirmRestore(review)"
                                    >
                                        <RotateCcw class="mr-1.5 h-3.5 w-3.5" />
                                        Restore
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="confirmForceDelete(review)"
                                    >
                                        <Trash2 class="mr-1.5 h-3.5 w-3.5" />
                                        Permanent Delete
                                    </Button>
                                </template>
                                <template v-else>
                                    <Link
                                        :href="`/tutors/${review.tutor_user_id}`"
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                                        title="View tutor profile"
                                    >
                                        <ExternalLink class="h-4 w-4" />
                                    </Link>
                                    <button
                                        type="button"
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-blue-600"
                                        title="Edit review"
                                        @click="startEdit(review)"
                                    >
                                        <Edit3 class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                                        title="Delete review"
                                        @click="confirmDelete(review)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="flex flex-col items-center py-16 text-center"
                >
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100"
                    >
                        <component
                            :is="props.filters.trash ? Trash2 : Star"
                            class="h-7 w-7 text-slate-400"
                        />
                    </div>
                    <p class="mt-4 text-sm font-medium text-slate-600">
                        {{
                            props.filters.trash
                                ? 'Recycle bin is empty'
                                : 'No reviews found'
                        }}
                    </p>
                    <p class="mt-1 text-xs text-slate-400">
                        {{
                            props.filters.trash
                                ? 'Deleted reviews will appear here.'
                                : 'Try adjusting your search or filter criteria.'
                        }}
                    </p>
                    <Button
                        v-if="props.filters.trash"
                        class="mt-4"
                        variant="outline"
                        as-child
                    >
                        <Link :href="baseUrl" preserve-scroll>
                            Back to Reviews
                        </Link>
                    </Button>
                </div>

                <!-- Pagination -->
                <div
                    v-if="reviews.last_page > 1"
                    class="flex items-center justify-center gap-1 border-t border-slate-200/80 px-5 py-4"
                >
                    <template v-for="link in reviews.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            :class="[
                                'rounded-lg px-3 py-1.5 text-sm font-medium transition-colors',
                                link.active
                                    ? 'bg-slate-900 text-white'
                                    : 'text-slate-600 hover:bg-slate-100',
                            ]"
                        >
                            {{ formatPaginationLabel(link.label) }}
                        </Link>
                        <span
                            v-else
                            class="rounded-lg px-3 py-1.5 text-sm text-slate-300"
                        >
                            {{ formatPaginationLabel(link.label) }}
                        </span>
                    </template>
                </div>
            </div>

            <!-- Delete Confirmation Dialog -->
            <Dialog v-model:open="deleteDialogOpen">
                <DialogContent>
                    <DialogHeader class="space-y-2">
                        <DialogTitle>Delete Review</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete this review? This
                            action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="gap-2">
                        <Button
                            variant="outline"
                            :disabled="isDeleting"
                            @click="deleteDialogOpen = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            variant="destructive"
                            :disabled="isDeleting"
                            @click="handleDelete"
                        >
                            {{ isDeleting ? 'Deleting...' : 'Delete Review' }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Restore Confirmation Dialog -->
            <Dialog v-model:open="restoreDialogOpen">
                <DialogContent>
                    <DialogHeader class="space-y-2">
                        <DialogTitle>Restore Review</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to restore this review? It
                            will be visible again on tutor profiles.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="gap-2">
                        <Button
                            variant="outline"
                            :disabled="isRestoring"
                            @click="restoreDialogOpen = false"
                        >
                            Cancel
                        </Button>
                        <Button :disabled="isRestoring" @click="handleRestore">
                            {{
                                isRestoring ? 'Restoring...' : 'Restore Review'
                            }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Permanent Delete Confirmation Dialog -->
            <Dialog v-model:open="forceDeleteDialogOpen">
                <DialogContent>
                    <DialogHeader class="space-y-2">
                        <DialogTitle>Permanently Delete Review</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to permanently delete this
                            review? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="gap-2">
                        <Button
                            variant="outline"
                            :disabled="isForceDeleting"
                            @click="forceDeleteDialogOpen = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            variant="destructive"
                            :disabled="isForceDeleting"
                            @click="handleForceDelete"
                        >
                            {{
                                isForceDeleting
                                    ? 'Deleting...'
                                    : 'Permanently Delete'
                            }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Restore All Confirmation Dialog -->
            <Dialog v-model:open="restoreAllDialogOpen">
                <DialogContent>
                    <DialogHeader class="space-y-2">
                        <DialogTitle>Restore All Reviews</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to restore all
                            {{ reviews.total }} deleted review(s)? They will be
                            visible again on tutor profiles.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="gap-2">
                        <Button
                            variant="outline"
                            :disabled="isRestoringAll"
                            @click="restoreAllDialogOpen = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            :disabled="isRestoringAll"
                            @click="handleRestoreAll"
                        >
                            {{
                                isRestoringAll ? 'Restoring...' : 'Restore All'
                            }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Empty Recycle Bin Confirmation Dialog -->
            <Dialog v-model:open="emptyTrashDialogOpen">
                <DialogContent>
                    <DialogHeader class="space-y-2">
                        <DialogTitle>Empty Recycle Bin</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to permanently delete all
                            {{ reviews.total }} review(s) in the recycle bin?
                            This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="gap-2">
                        <Button
                            variant="outline"
                            :disabled="isEmptyingTrash"
                            @click="emptyTrashDialogOpen = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            variant="destructive"
                            :disabled="isEmptyingTrash"
                            @click="handleEmptyTrash"
                        >
                            {{
                                isEmptyingTrash
                                    ? 'Deleting...'
                                    : 'Empty Recycle Bin'
                            }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AdminLayout>
</template>
