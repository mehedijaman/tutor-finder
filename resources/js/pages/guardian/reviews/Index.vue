<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    CheckCircle,
    Edit3,
    ExternalLink,
    MessageSquare,
    RotateCcw,
    Send,
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
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import GuardianLayout from '@/layouts/GuardianLayout.vue';

type ReviewTutor = {
    id: number;
    name: string;
    photo_url: string | null;
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
    rating: number;
    comment: string | null;
    created_at: string;
    updated_at: string;
    tutor: ReviewTutor;
    job_assignment: ReviewAssignment | null;
};

type PaginatedReviews = {
    data: Review[];
    current_page: number;
    last_page: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
};

type ReviewableAssignment = {
    assignment_id: number;
    tutor_user_id: number;
    job_title: string;
    tutor_name: string;
};

const props = defineProps<{
    reviews: PaginatedReviews;
    reviewableAssignments: ReviewableAssignment[];
    trashedCount: number;
    showTrashed: boolean;
}>();

const breadcrumbs = [{ title: 'My Reviews', href: '/guardian/reviews' }];

const page = usePage();
const successMessage = computed(
    () => (page.props.flash as { success?: string })?.success,
);

// --- New Review Form ---
const showNewForm = ref(false);
const selectedAssignment = ref<number | null>(
    props.reviewableAssignments.length === 1
        ? props.reviewableAssignments[0].assignment_id
        : null,
);
const hoveredStar = ref(0);

const newForm = useForm({
    job_assignment_id: selectedAssignment.value,
    rating: 0,
    comment: '',
});

function selectAssignment(assignmentId: number): void {
    selectedAssignment.value = assignmentId;
    newForm.job_assignment_id = assignmentId;
}

function submitNewReview(): void {
    newForm.post('/guardian/reviews', {
        preserveScroll: true,
        onSuccess: () => {
            newForm.reset();
            hoveredStar.value = 0;
            showNewForm.value = false;
            selectedAssignment.value =
                props.reviewableAssignments.length === 1
                    ? props.reviewableAssignments[0].assignment_id
                    : null;
        },
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

watch(
    () => editingReview.value,
    (review) => {
        if (review) {
            editForm.rating = review.rating;
            editForm.comment = review.comment ?? '';
        }
    },
);

function submitEdit(): void {
    if (!editingReview.value) {
        return;
    }
    editForm.patch(`/guardian/reviews/${editingReview.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingReview.value = null;
            editForm.reset();
            editHoveredStar.value = 0;
        },
    });
}

// --- Single Soft Delete ---
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
    router.delete(`/guardian/reviews/${reviewToDelete.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
            deleteDialogOpen.value = false;
            reviewToDelete.value = null;
        },
    });
}

// --- Restore Single ---
const restoreDialogOpen = ref(false);
const reviewToRestore = ref<Review | null>(null);
const isRestoring = ref(false);

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
        `/guardian/reviews/${reviewToRestore.value.id}/restore`,
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

// --- Force Delete Single ---
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
    router.delete(
        `/guardian/reviews/${reviewToForceDelete.value.id}/force-delete`,
        {
            preserveScroll: true,
            onFinish: () => {
                isForceDeleting.value = false;
                forceDeleteDialogOpen.value = false;
                reviewToForceDelete.value = null;
            },
        },
    );
}

// --- Restore All ---
const restoreAllDialogOpen = ref(false);
const isRestoringAll = ref(false);

function handleRestoreAll(): void {
    isRestoringAll.value = true;
    router.patch(
        '/guardian/reviews/restore-all',
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
    router.delete('/guardian/reviews/empty-trash', {
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

function timeAgo(dateStr: string): string {
    const seconds = Math.floor(
        (Date.now() - new Date(dateStr).getTime()) / 1000,
    );
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    const months = Math.floor(days / 30);

    if (months > 0) {
        return `${months} month${months > 1 ? 's' : ''} ago`;
    }
    if (days > 0) {
        return `${days} day${days > 1 ? 's' : ''} ago`;
    }
    if (hours > 0) {
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    }
    if (minutes > 0) {
        return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    }
    return 'Just now';
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
    <Head title="My Reviews" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Page Header -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1
                            class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
                        >
                            My Reviews
                        </h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            View your submitted reviews and rate tutors you've
                            worked with.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <Button
                            v-if="!props.showTrashed"
                            variant="outline"
                            size="sm"
                            :disabled="trashedCount === 0"
                            class="dark:border-slate-700 dark:text-slate-300"
                            as-child
                        >
                            <Link
                                href="/guardian/reviews?trash=1"
                                preserve-scroll
                            >
                                <Trash2 class="mr-2 h-4 w-4" />
                                Recycle Bin ({{ trashedCount }})
                            </Link>
                        </Button>
                        <Button
                            v-if="props.showTrashed"
                            variant="outline"
                            size="sm"
                            class="dark:border-slate-700 dark:text-slate-300"
                            as-child
                        >
                            <Link href="/guardian/reviews" preserve-scroll>
                                Back to Reviews
                            </Link>
                        </Button>
                        <Button
                            v-if="props.showTrashed && reviews.total > 0"
                            variant="outline"
                            size="sm"
                            class="dark:border-slate-700 dark:text-slate-300"
                            @click="restoreAllDialogOpen = true"
                        >
                            <RotateCcw class="mr-2 h-4 w-4" />
                            Restore All
                        </Button>
                        <Button
                            v-if="props.showTrashed && reviews.total > 0"
                            variant="destructive"
                            size="sm"
                            @click="emptyTrashDialogOpen = true"
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Empty Recycle Bin
                        </Button>
                        <Button
                            v-if="
                                reviewableAssignments.length > 0 &&
                                !showNewForm &&
                                !props.showTrashed
                            "
                            @click="showNewForm = true"
                        >
                            <MessageSquare class="mr-2 h-4 w-4" />
                            Write a Review
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Flash Message -->
            <div
                v-if="successMessage"
                class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300"
            >
                <CheckCircle class="h-4.5 w-4.5 flex-shrink-0" />
                {{ successMessage }}
            </div>

            <!-- Pending Reviews Alert -->
            <div
                v-if="
                    reviewableAssignments.length > 0 &&
                    !showNewForm &&
                    !props.showTrashed
                "
                class="rounded-2xl border border-amber-200/80 bg-amber-50 p-5 shadow-sm dark:border-amber-900/60 dark:bg-amber-950/40"
            >
                <div class="flex items-start gap-3">
                    <Star class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-500" />
                    <div>
                        <p
                            class="text-sm font-medium text-amber-800 dark:text-amber-300"
                        >
                            You have
                            {{ reviewableAssignments.length }}
                            confirmed
                            {{
                                reviewableAssignments.length === 1
                                    ? 'assignment'
                                    : 'assignments'
                            }}
                            awaiting review
                        </p>
                        <p
                            class="mt-1 text-xs text-amber-600 dark:text-amber-400"
                        >
                            Share your experience to help other guardians find
                            great tutors.
                        </p>
                    </div>
                </div>
            </div>

            <!-- New Review Form -->
            <div
                v-if="showNewForm && !props.showTrashed"
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mb-5 flex items-center justify-between">
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-slate-100"
                    >
                        Write a Review
                    </h2>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300"
                        @click="showNewForm = false"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="space-y-5">
                    <!-- Assignment Selector -->
                    <div>
                        <Label
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            Select Assignment to Review
                        </Label>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <button
                                v-for="assignment in reviewableAssignments"
                                :key="assignment.assignment_id"
                                type="button"
                                :class="[
                                    'rounded-lg border px-4 py-3 text-left text-sm transition-all',
                                    selectedAssignment ===
                                    assignment.assignment_id
                                        ? 'border-blue-500 bg-blue-50 text-blue-700 ring-1 ring-blue-500 dark:bg-blue-950/40 dark:text-blue-300'
                                        : 'border-slate-200 text-slate-700 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-800',
                                ]"
                                @click="
                                    selectAssignment(assignment.assignment_id)
                                "
                            >
                                <p class="font-medium">
                                    {{ assignment.tutor_name }}
                                </p>
                                <p
                                    class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    {{ assignment.job_title }}
                                </p>
                            </button>
                        </div>
                        <p
                            v-if="newForm.errors.job_assignment_id"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ newForm.errors.job_assignment_id }}
                        </p>
                    </div>

                    <!-- Star Rating -->
                    <div>
                        <Label
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            Your Rating
                        </Label>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1">
                                <button
                                    v-for="star in 5"
                                    :key="star"
                                    type="button"
                                    class="cursor-pointer rounded-sm p-0.5 transition-transform hover:scale-125 focus:ring-2 focus:ring-amber-400 focus:ring-offset-1 focus:outline-none"
                                    @click="newForm.rating = star"
                                    @mouseenter="hoveredStar = star"
                                    @mouseleave="hoveredStar = 0"
                                >
                                    <Star
                                        :class="[
                                            'h-7 w-7 transition-colors',
                                            star <=
                                            (hoveredStar || newForm.rating)
                                                ? 'fill-amber-400 text-amber-400'
                                                : 'fill-slate-200 text-slate-300 dark:fill-slate-700 dark:text-slate-600',
                                        ]"
                                    />
                                </button>
                            </div>
                            <span
                                v-if="newForm.rating > 0"
                                class="text-sm font-medium text-amber-600 dark:text-amber-400"
                            >
                                {{ ratingLabel(newForm.rating) }}
                            </span>
                        </div>
                        <p
                            v-if="newForm.errors.rating"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ newForm.errors.rating }}
                        </p>
                    </div>

                    <!-- Comment -->
                    <div>
                        <Label
                            for="new-review-comment"
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            Your Review
                            <span class="text-slate-400 dark:text-slate-500"
                                >(optional)</span
                            >
                        </Label>
                        <Textarea
                            id="new-review-comment"
                            v-model="newForm.comment"
                            placeholder="Share your experience with this tutor..."
                            :rows="4"
                            class="resize-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        />
                        <div class="mt-1 flex items-center justify-between">
                            <p
                                v-if="newForm.errors.comment"
                                class="text-sm text-red-600 dark:text-red-400"
                            >
                                {{ newForm.errors.comment }}
                            </p>
                            <span
                                class="ml-auto text-xs text-slate-400 dark:text-slate-500"
                            >
                                {{ (newForm.comment ?? '').length }} / 2000
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3">
                        <Button
                            variant="outline"
                            class="dark:border-slate-700 dark:text-slate-300"
                            :disabled="newForm.processing"
                            @click="showNewForm = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            :disabled="
                                newForm.processing ||
                                newForm.rating === 0 ||
                                !selectedAssignment
                            "
                            @click="submitNewReview"
                        >
                            <Send class="mr-2 h-4 w-4" />
                            {{
                                newForm.processing
                                    ? 'Submitting...'
                                    : 'Submit Review'
                            }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Reviews List -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="border-b border-slate-200/80 px-5 py-4 sm:px-6 dark:border-slate-800"
                >
                    <h2
                        class="text-base font-semibold text-slate-900 dark:text-slate-100"
                    >
                        {{ props.showTrashed ? 'Recycle Bin' : 'Your Reviews' }}
                        <span
                            v-if="reviews.total > 0"
                            class="ml-1 text-sm font-normal text-muted-foreground"
                        >
                            ({{ reviews.total }})
                        </span>
                    </h2>
                </div>

                <div
                    v-if="reviews.data.length > 0"
                    class="divide-y divide-slate-100 dark:divide-slate-800"
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
                                    class="text-sm font-semibold text-slate-900 dark:text-slate-100"
                                >
                                    Edit Review for
                                    {{ review.tutor.name }}
                                </h3>
                                <button
                                    type="button"
                                    class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300"
                                    @click="cancelEdit"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>

                            <!-- Edit Star Rating -->
                            <div>
                                <Label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
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
                                                        : 'fill-slate-200 text-slate-300 dark:fill-slate-700 dark:text-slate-600',
                                                ]"
                                            />
                                        </button>
                                    </div>
                                    <span
                                        v-if="editForm.rating > 0"
                                        class="text-sm font-medium text-amber-600 dark:text-amber-400"
                                    >
                                        {{ ratingLabel(editForm.rating) }}
                                    </span>
                                </div>
                                <p
                                    v-if="editForm.errors.rating"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ editForm.errors.rating }}
                                </p>
                            </div>

                            <!-- Edit Comment -->
                            <div>
                                <Label
                                    :for="`edit-comment-${review.id}`"
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Comment
                                    <span
                                        class="text-slate-400 dark:text-slate-500"
                                        >(optional)</span
                                    >
                                </Label>
                                <Textarea
                                    :id="`edit-comment-${review.id}`"
                                    v-model="editForm.comment"
                                    placeholder="Update your review..."
                                    :rows="3"
                                    class="resize-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                />
                                <div
                                    class="mt-1 flex items-center justify-between"
                                >
                                    <p
                                        v-if="editForm.errors.comment"
                                        class="text-sm text-red-600 dark:text-red-400"
                                    >
                                        {{ editForm.errors.comment }}
                                    </p>
                                    <span
                                        class="ml-auto text-xs text-slate-400 dark:text-slate-500"
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
                                    class="dark:border-slate-700 dark:text-slate-300"
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
                                    class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600 dark:bg-blue-950/40 dark:text-blue-300"
                                >
                                    {{ getInitial(review.tutor.name) }}
                                </div>
                            </div>

                            <!-- Review Content -->
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <Link
                                        :href="`/tutors/${review.tutor_user_id}`"
                                        class="text-sm font-semibold text-slate-900 hover:text-blue-600 hover:underline dark:text-slate-100 dark:hover:text-blue-400"
                                    >
                                        {{ review.tutor.name }}
                                    </Link>
                                    <span
                                        class="text-xs text-slate-400 dark:text-slate-500"
                                    >
                                        {{ timeAgo(review.created_at) }}
                                    </span>
                                </div>

                                <div
                                    v-if="review.job_assignment?.job"
                                    class="mt-0.5"
                                >
                                    <Badge
                                        variant="outline"
                                        class="text-xs font-normal dark:border-slate-700 dark:text-slate-300"
                                    >
                                        {{ review.job_assignment.job.title }}
                                    </Badge>
                                </div>

                                <StarRating
                                    :rating="review.rating"
                                    size="sm"
                                    class="mt-1.5"
                                />

                                <p
                                    v-if="review.comment"
                                    class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300"
                                >
                                    {{ review.comment }}
                                </p>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-shrink-0 items-center gap-1">
                                <template v-if="props.showTrashed">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="dark:border-slate-700 dark:text-slate-300"
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
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300"
                                        title="View tutor profile"
                                    >
                                        <ExternalLink class="h-4 w-4" />
                                    </Link>
                                    <button
                                        type="button"
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-800 dark:hover:text-blue-400"
                                        title="Edit review"
                                        @click="startEdit(review)"
                                    >
                                        <Edit3 class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"
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
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800"
                    >
                        <component
                            :is="props.showTrashed ? Trash2 : MessageSquare"
                            class="h-7 w-7 text-slate-400 dark:text-slate-500"
                        />
                    </div>
                    <p
                        class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-300"
                    >
                        {{
                            props.showTrashed
                                ? 'Recycle bin is empty'
                                : 'No reviews yet'
                        }}
                    </p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                        {{
                            props.showTrashed
                                ? 'Deleted reviews will appear here.'
                                : 'Once you review a tutor, it will appear here.'
                        }}
                    </p>
                    <Button
                        v-if="props.showTrashed"
                        class="mt-4 dark:border-slate-700 dark:text-slate-300"
                        variant="outline"
                        as-child
                    >
                        <Link href="/guardian/reviews" preserve-scroll>
                            Back to Reviews
                        </Link>
                    </Button>
                    <Button
                        v-else-if="
                            reviewableAssignments.length > 0 && !showNewForm
                        "
                        class="mt-4"
                        @click="showNewForm = true"
                    >
                        <MessageSquare class="mr-2 h-4 w-4" />
                        Write Your First Review
                    </Button>
                </div>

                <!-- Pagination -->
                <div
                    v-if="reviews.last_page > 1"
                    class="flex items-center justify-center gap-1 border-t border-slate-200/80 px-5 py-4 dark:border-slate-800"
                >
                    <template v-for="link in reviews.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            :class="[
                                'rounded-lg px-3 py-1.5 text-sm font-medium transition-colors',
                                link.active
                                    ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900'
                                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800',
                            ]"
                        >
                            {{ formatPaginationLabel(link.label) }}
                        </Link>
                        <span
                            v-else
                            class="rounded-lg px-3 py-1.5 text-sm text-slate-300 dark:text-slate-600"
                        >
                            {{ formatPaginationLabel(link.label) }}
                        </span>
                    </template>
                </div>
            </div>

            <!-- Delete Confirmation Dialog -->
            <Dialog v-model:open="deleteDialogOpen">
                <DialogContent class="dark:border-slate-800 dark:bg-slate-900">
                    <DialogHeader class="space-y-2">
                        <DialogTitle class="text-slate-900 dark:text-slate-100"
                            >Delete Review</DialogTitle
                        >
                        <DialogDescription
                            class="text-slate-500 dark:text-slate-400"
                        >
                            Are you sure you want to delete this review? This
                            action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="gap-2">
                        <Button
                            variant="outline"
                            class="dark:border-slate-700 dark:text-slate-300"
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
                <DialogContent class="dark:border-slate-800 dark:bg-slate-900">
                    <DialogHeader class="space-y-2">
                        <DialogTitle class="text-slate-900 dark:text-slate-100"
                            >Restore Review</DialogTitle
                        >
                        <DialogDescription
                            class="text-slate-500 dark:text-slate-400"
                        >
                            Are you sure you want to restore this review? It
                            will be visible again on tutor profiles.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="gap-2">
                        <Button
                            variant="outline"
                            class="dark:border-slate-700 dark:text-slate-300"
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

            <!-- Force Delete Confirmation Dialog -->
            <Dialog v-model:open="forceDeleteDialogOpen">
                <DialogContent class="dark:border-slate-800 dark:bg-slate-900">
                    <DialogHeader class="space-y-2">
                        <DialogTitle class="text-slate-900 dark:text-slate-100"
                            >Permanently Delete Review</DialogTitle
                        >
                        <DialogDescription
                            class="text-slate-500 dark:text-slate-400"
                        >
                            Are you sure you want to permanently delete this
                            review? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="gap-2">
                        <Button
                            variant="outline"
                            class="dark:border-slate-700 dark:text-slate-300"
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

            <!-- Restore All Dialog -->
            <Dialog v-model:open="restoreAllDialogOpen">
                <DialogContent class="dark:border-slate-800 dark:bg-slate-900">
                    <DialogHeader class="space-y-2">
                        <DialogTitle class="text-slate-900 dark:text-slate-100"
                            >Restore All Reviews</DialogTitle
                        >
                        <DialogDescription
                            class="text-slate-500 dark:text-slate-400"
                        >
                            Are you sure you want to restore all reviews from
                            the recycle bin?
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="gap-2">
                        <Button
                            variant="outline"
                            class="dark:border-slate-700 dark:text-slate-300"
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

            <!-- Empty Trash Dialog -->
            <Dialog v-model:open="emptyTrashDialogOpen">
                <DialogContent class="dark:border-slate-800 dark:bg-slate-900">
                    <DialogHeader class="space-y-2">
                        <DialogTitle class="text-slate-900 dark:text-slate-100"
                            >Empty Recycle Bin</DialogTitle
                        >
                        <DialogDescription
                            class="text-slate-500 dark:text-slate-400"
                        >
                            Are you sure you want to permanently delete all
                            reviews in the recycle bin? This action cannot be
                            undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="gap-2">
                        <Button
                            variant="outline"
                            class="dark:border-slate-700 dark:text-slate-300"
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
                                    ? 'Emptying...'
                                    : 'Empty Recycle Bin'
                            }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </GuardianLayout>
</template>
