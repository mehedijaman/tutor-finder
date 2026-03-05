<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Edit3, MessageSquare, ThumbsUp, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import StarRating from '@/components/tutors/StarRating.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type Guardian = {
    id: number;
    name: string;
    photo_url: string | null;
};

type Review = {
    id: number;
    rating: number;
    comment: string | null;
    created_at: string;
    guardian: Guardian;
};

type PaginatedReviews = {
    data: Review[];
    current_page: number;
    last_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
    total: number;
};

const props = defineProps<{
    reviews: PaginatedReviews;
    averageRating: number;
    totalReviews: number;
    ratingDistribution: Record<number, number>;
}>();

const emit = defineEmits<{
    edit: [review: Review];
}>();

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id ?? null);

const maxDistribution = computed(() => {
    return Math.max(...Object.values(props.ratingDistribution), 1);
});

const deleteDialogOpen = ref(false);
const reviewToDelete = ref<Review | null>(null);
const isDeleting = ref(false);

function formatDate(dateStr: string): string {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
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

function isOwnReview(review: Review): boolean {
    return currentUserId.value === review.guardian.id;
}

function handleEdit(review: Review): void {
    emit('edit', review);
}

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
</script>

<template>
    <section
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800"
    >
        <div class="mb-6 flex items-center gap-2">
            <MessageSquare class="h-5 w-5 text-slate-600 dark:text-slate-400" />
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                Reviews & Ratings
            </h2>
        </div>

        <!-- Rating Summary -->
        <div v-if="totalReviews > 0" class="mb-8 grid gap-6 sm:grid-cols-2">
            <!-- Average Rating -->
            <div
                class="flex flex-col items-center justify-center rounded-xl bg-slate-50 p-6 dark:bg-slate-900/50"
            >
                <div
                    class="text-5xl font-bold text-slate-900 tabular-nums dark:text-white"
                >
                    {{ averageRating.toFixed(1) }}
                </div>
                <StarRating :rating="averageRating" size="lg" class="mt-2" />
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Based on {{ totalReviews }}
                    {{ totalReviews === 1 ? 'review' : 'reviews' }}
                </p>
            </div>

            <!-- Distribution -->
            <div class="flex flex-col justify-center space-y-2">
                <div
                    v-for="star in [5, 4, 3, 2, 1]"
                    :key="star"
                    class="flex items-center gap-3"
                >
                    <span
                        class="w-8 text-right text-sm font-medium text-slate-600 dark:text-slate-400"
                    >
                        {{ star }} ★
                    </span>
                    <div
                        class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700"
                    >
                        <div
                            class="h-full rounded-full bg-amber-400 transition-all duration-500"
                            :style="{
                                width:
                                    totalReviews > 0
                                        ? `${(ratingDistribution[star] / maxDistribution) * 100}%`
                                        : '0%',
                            }"
                        />
                    </div>
                    <span
                        class="w-8 text-sm text-slate-500 tabular-nums dark:text-slate-400"
                    >
                        {{ ratingDistribution[star] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Reviews List -->
        <div v-if="reviews.data.length > 0" class="space-y-5">
            <div
                v-for="review in reviews.data"
                :key="review.id"
                :class="[
                    'rounded-xl border p-4 transition-colors',
                    isOwnReview(review)
                        ? 'border-blue-100 bg-blue-50/30 dark:border-blue-900/30 dark:bg-blue-950/20'
                        : 'border-slate-100 hover:bg-slate-50/50 dark:border-slate-700 dark:hover:bg-slate-900/30',
                ]"
            >
                <div class="flex items-start gap-3">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <div
                            v-if="review.guardian.photo_url"
                            class="h-10 w-10 overflow-hidden rounded-full"
                        >
                            <img
                                :src="review.guardian.photo_url"
                                :alt="review.guardian.name"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div
                            v-else
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600 dark:bg-blue-900 dark:text-blue-300"
                        >
                            {{ getInitial(review.guardian.name) }}
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="text-sm font-semibold text-slate-900 dark:text-white"
                            >
                                {{ review.guardian.name }}
                            </span>
                            <span
                                v-if="isOwnReview(review)"
                                class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900 dark:text-blue-300"
                            >
                                You
                            </span>
                            <span
                                class="text-xs text-slate-400"
                                :title="formatDate(review.created_at)"
                            >
                                {{ timeAgo(review.created_at) }}
                            </span>
                        </div>
                        <StarRating
                            :rating="review.rating"
                            size="sm"
                            class="mt-1"
                        />
                        <p
                            v-if="review.comment"
                            class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300"
                        >
                            {{ review.comment }}
                        </p>
                    </div>

                    <!-- Actions for own reviews -->
                    <div
                        v-if="isOwnReview(review)"
                        class="flex flex-shrink-0 items-center gap-1"
                    >
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-700 dark:hover:text-blue-400"
                            title="Edit review"
                            @click="handleEdit(review)"
                        >
                            <Edit3 class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950 dark:hover:text-red-400"
                            title="Delete review"
                            @click="confirmDelete(review)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex flex-col items-center py-10 text-center">
            <div
                class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-900/50"
            >
                <ThumbsUp class="h-7 w-7 text-slate-400" />
            </div>
            <p
                class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400"
            >
                No reviews yet
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Be the first to leave a review for this tutor.
            </p>
        </div>

        <!-- Pagination -->
        <div
            v-if="reviews.last_page > 1"
            class="mt-6 flex items-center justify-center gap-2"
        >
            <Link
                v-if="reviews.prev_page_url"
                :href="reviews.prev_page_url"
                preserve-scroll
                class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 dark:border-slate-600 dark:text-slate-400"
            >
                Previous
            </Link>
            <span class="text-sm text-slate-500 dark:text-slate-400">
                Page {{ reviews.current_page }} of {{ reviews.last_page }}
            </span>
            <Link
                v-if="reviews.next_page_url"
                :href="reviews.next_page_url"
                preserve-scroll
                class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 dark:border-slate-600 dark:text-slate-400"
            >
                Next
            </Link>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="deleteDialogOpen">
            <DialogContent>
                <DialogHeader class="space-y-2">
                    <DialogTitle>Delete Review</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this review? This action
                        cannot be undone.
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
    </section>
</template>
