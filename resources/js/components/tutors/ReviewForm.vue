<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Send, Star, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

type ReviewableAssignment = {
    assignment_id: number;
    job_title: string;
};

type EditReview = {
    id: number;
    rating: number;
    comment: string | null;
};

const props = defineProps<{
    tutorId: number;
    assignments: ReviewableAssignment[];
    editReview?: EditReview | null;
}>();

const emit = defineEmits<{
    cancelEdit: [];
}>();

const isEditing = computed(() => !!props.editReview);

const selectedAssignment = ref<number | null>(
    props.assignments.length === 1 ? props.assignments[0].assignment_id : null,
);
const hoveredStar = ref(0);

const form = useForm({
    job_assignment_id: selectedAssignment.value,
    rating: 0,
    comment: '',
});

watch(
    () => props.editReview,
    (review) => {
        if (review) {
            form.rating = review.rating;
            form.comment = review.comment ?? '';
            hoveredStar.value = 0;
        } else {
            form.reset();
            hoveredStar.value = 0;
        }
    },
    { immediate: true },
);

const selectedLabel = computed(() => {
    if (!selectedAssignment.value) {
        return 'Select an assignment';
    }
    return props.assignments.find(
        (a) => a.assignment_id === selectedAssignment.value,
    )?.job_title;
});

function selectAssignment(assignmentId: number): void {
    selectedAssignment.value = assignmentId;
    form.job_assignment_id = assignmentId;
}

function setRating(rating: number): void {
    form.rating = rating;
}

function submitReview(): void {
    if (isEditing.value && props.editReview) {
        form.transform((data) => ({
            rating: data.rating,
            comment: data.comment,
        })).put(`/guardian/reviews/${props.editReview.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                emit('cancelEdit');
            },
        });
    } else {
        form.post('/guardian/reviews', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                hoveredStar.value = 0;
            },
        });
    }
}

function cancelEdit(): void {
    emit('cancelEdit');
}

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
</script>

<template>
    <div
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800"
    >
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                {{ isEditing ? 'Edit Your Review' : 'Write a Review' }}
            </h3>
            <button
                v-if="isEditing"
                type="button"
                class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300"
                @click="cancelEdit"
            >
                <X class="h-5 w-5" />
            </button>
        </div>

        <div class="space-y-5">
            <!-- Assignment Selector (only for new reviews) -->
            <div v-if="!isEditing && assignments.length > 1">
                <Label
                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                >
                    Which assignment are you reviewing?
                </Label>
                <div class="space-y-2">
                    <button
                        v-for="assignment in assignments"
                        :key="assignment.assignment_id"
                        type="button"
                        :class="[
                            'w-full rounded-lg border px-4 py-3 text-left text-sm transition-all',
                            selectedAssignment === assignment.assignment_id
                                ? 'border-blue-500 bg-blue-50 text-blue-700 ring-1 ring-blue-500 dark:bg-blue-950 dark:text-blue-300'
                                : 'border-slate-200 text-slate-700 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300',
                        ]"
                        @click="selectAssignment(assignment.assignment_id)"
                    >
                        {{ assignment.job_title }}
                    </button>
                </div>
                <p
                    v-if="form.errors.job_assignment_id"
                    class="mt-1 text-sm text-red-600"
                >
                    {{ form.errors.job_assignment_id }}
                </p>
            </div>

            <div
                v-else-if="!isEditing"
                class="text-sm text-slate-600 dark:text-slate-400"
            >
                Reviewing for:
                <span class="font-medium text-slate-800 dark:text-slate-200">{{
                    selectedLabel
                }}</span>
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
                            @click="setRating(star)"
                            @mouseenter="hoveredStar = star"
                            @mouseleave="hoveredStar = 0"
                        >
                            <Star
                                :class="[
                                    'h-7 w-7 transition-colors',
                                    star <= (hoveredStar || form.rating)
                                        ? 'fill-amber-400 text-amber-400'
                                        : 'fill-slate-200 text-slate-300 dark:fill-slate-600 dark:text-slate-500',
                                ]"
                            />
                        </button>
                    </div>
                    <span
                        v-if="form.rating > 0"
                        class="text-sm font-medium text-amber-600"
                    >
                        {{ ratingLabel(form.rating) }}
                    </span>
                </div>
                <p v-if="form.errors.rating" class="mt-1 text-sm text-red-600">
                    {{ form.errors.rating }}
                </p>
            </div>

            <!-- Comment -->
            <div>
                <Label
                    for="review-comment"
                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                >
                    Your Review
                    <span class="text-slate-400">(optional)</span>
                </Label>
                <Textarea
                    id="review-comment"
                    v-model="form.comment"
                    placeholder="Share your experience with this tutor..."
                    :rows="4"
                    class="resize-none"
                />
                <div class="mt-1 flex items-center justify-between">
                    <p v-if="form.errors.comment" class="text-sm text-red-600">
                        {{ form.errors.comment }}
                    </p>
                    <span class="ml-auto text-xs text-slate-400">
                        {{ (form.comment ?? '').length }} / 2000
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3">
                <Button
                    v-if="isEditing"
                    variant="outline"
                    :disabled="form.processing"
                    @click="cancelEdit"
                >
                    Cancel
                </Button>
                <Button
                    :disabled="
                        form.processing ||
                        form.rating === 0 ||
                        (!isEditing && !selectedAssignment)
                    "
                    :class="isEditing ? '' : 'w-full'"
                    @click="submitReview"
                >
                    <Send class="mr-2 h-4 w-4" />
                    {{
                        form.processing
                            ? 'Submitting...'
                            : isEditing
                              ? 'Update Review'
                              : 'Submit Review'
                    }}
                </Button>
            </div>
        </div>
    </div>
</template>
