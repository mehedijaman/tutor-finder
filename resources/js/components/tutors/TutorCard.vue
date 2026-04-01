<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Clock,
    GraduationCap,
    MapPin,
    ShieldAlert,
    User,
    Wallet,
} from 'lucide-vue-next';
import { computed } from 'vue';
import StarRating from '@/components/tutors/StarRating.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';

type TutorEducation = {
    id: number;
    degree: string;
    institute: string;
    department: string;
    graduation_year: number | null;
    result: string | null;
    is_current: boolean;
};

type TutorProfile = {
    id: number;
    gender: string | null;
    bio: string | null;
    present_address: string | null;
    expected_salary_min: number | null;
    expected_salary_max: number | null;
    preferred_tuition_types: string[] | null;
    preferred_categories: string[] | null;
    preferred_classes: string[] | null;
    preferred_subjects: string[] | null;
    preferred_locations: string[] | null;
    available_days: string[] | null;
    available_time: string | null;
};

type Tutor = {
    id: number;
    name: string;
    slug: string;
    verified_at: string | null;
    created_at: string;
    photo_url: string | null;
    tutor_profile: TutorProfile | null;
    tutor_educations: TutorEducation[];
    tutor_reviews_count?: number;
    tutor_reviews_avg_rating?: number | null;
};

const props = defineProps<{
    tutor: Tutor;
}>();

const primaryEducation = computed(() => {
    const educations = props.tutor.tutor_educations ?? [];

    return educations.length > 0 ? educations[0] : null;
});

const preferredSubjectPreview = computed(() => {
    const subjects = props.tutor.tutor_profile?.preferred_subjects ?? [];

    return subjects.slice(0, 3);
});

const hasRating = computed(() => {
    return (
        props.tutor.tutor_reviews_count !== undefined &&
        props.tutor.tutor_reviews_count > 0
    );
});

function getGenderLabel(): string {
    const gender = props.tutor.tutor_profile?.gender;

    if (gender === 'female') {
        return 'Female';
    }

    if (gender === 'male') {
        return 'Male';
    }

    if (gender === 'other') {
        return 'Other';
    }

    return 'Any';
}

function formatSalary(): string {
    const min = props.tutor.tutor_profile?.expected_salary_min ?? null;
    const max = props.tutor.tutor_profile?.expected_salary_max ?? null;

    if (!min && !max) {
        return 'Negotiable';
    }

    const format = (n: number) => `৳ ${n.toLocaleString()}`;

    if (min && max) {
        return `${format(min)} - ${format(max)}`;
    }

    if (min) {
        return format(min);
    }

    return max ? format(max) : 'Negotiable';
}

function getEducationLine(): string {
    if (!primaryEducation.value) {
        return 'Professional Tutor';
    }

    const degree = primaryEducation.value.degree?.trim();
    const institute = primaryEducation.value.institute?.trim();

    if (degree && institute) {
        return `${degree} • ${institute}`;
    }

    return degree || institute || 'Professional Tutor';
}

function getAvailableSummary(): string {
    const availableTime = props.tutor.tutor_profile?.available_time;
    const availableDays = props.tutor.tutor_profile?.available_days ?? [];

    if (availableTime && availableDays.length > 0) {
        return `${availableTime} • ${availableDays.length} day(s)/week`;
    }

    if (availableTime) {
        return availableTime;
    }

    if (availableDays.length > 0) {
        return `${availableDays.length} day(s)/week`;
    }

    return 'Flexible';
}

function memberSinceYear(): string {
    const year = new Date(props.tutor.created_at).getFullYear();

    return Number.isFinite(year) ? String(year) : 'N/A';
}
</script>

<template>
    <Card
        class="group relative flex h-full flex-col overflow-hidden border-slate-200/80 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl"
    >
        <div
            class="relative overflow-hidden border-b border-slate-200/80 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white"
        >
            <div class="absolute inset-0 bg-white/5" />
            <div
                class="relative flex items-start justify-between px-5 pt-4 text-xs font-medium"
            >
                <span
                    class="rounded-full bg-white/15 px-2.5 py-1 text-white/90"
                >
                    ID #{{ tutor.id }}
                </span>
                <Badge
                    v-if="tutor.verified_at"
                    class="border-emerald-300/30 bg-emerald-500/15 text-emerald-200 hover:bg-emerald-500/15"
                >
                    <BadgeCheck class="mr-1 h-3.5 w-3.5" />
                    Verified
                </Badge>
                <Badge
                    v-else
                    class="border-amber-300/30 bg-amber-500/15 text-amber-200 hover:bg-amber-500/15"
                >
                    <ShieldAlert class="mr-1 h-3.5 w-3.5" />
                    Unverified
                </Badge>
            </div>

            <div class="relative flex items-end gap-4 px-5 pb-5">
                <div
                    class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white/10 ring-2 ring-white/30"
                >
                    <img
                        v-if="tutor.photo_url"
                        :src="tutor.photo_url"
                        :alt="tutor.name"
                        class="h-full w-full object-cover"
                    />
                    <User v-else class="h-8 w-8 text-white/70" />
                </div>

                <div class="min-w-0 flex-1">
                    <h3
                        class="line-clamp-1 text-lg font-semibold tracking-tight"
                    >
                        {{ tutor.name }}
                    </h3>
                    <p class="line-clamp-1 text-xs text-white/75">
                        {{ getEducationLine() }}
                    </p>
                </div>
            </div>
        </div>

        <CardContent class="flex flex-1 flex-col gap-4 p-5">
            <div class="flex items-center justify-between">
                <Badge
                    variant="outline"
                    class="border-slate-200 bg-slate-50 text-slate-700"
                >
                    {{ getGenderLabel() }}
                </Badge>
                <span class="text-xs text-slate-400">
                    Member since {{ memberSinceYear() }}
                </span>
            </div>

            <div class="space-y-2.5 text-sm">
                <div class="flex items-start gap-2.5 text-slate-600">
                    <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                    <span class="line-clamp-1">
                        {{
                            tutor.tutor_profile?.present_address ||
                            'Location not specified'
                        }}
                    </span>
                </div>

                <div class="flex items-start gap-2.5 text-slate-600">
                    <Wallet class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                    <span class="font-medium text-slate-900">
                        {{ formatSalary() }}
                    </span>
                </div>

                <div class="flex items-start gap-2.5 text-slate-600">
                    <Clock class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                    <span class="line-clamp-1">{{
                        getAvailableSummary()
                    }}</span>
                </div>
            </div>

            <div
                v-if="preferredSubjectPreview.length > 0"
                class="flex flex-wrap gap-1.5"
            >
                <Badge
                    v-for="subject in preferredSubjectPreview"
                    :key="subject"
                    variant="outline"
                    class="rounded-lg border-blue-200 bg-blue-50 px-2 py-1 text-[11px] text-blue-700"
                >
                    <GraduationCap class="mr-1 h-3 w-3" />
                    {{ subject }}
                </Badge>
            </div>

            <div class="mt-auto">
                <StarRating
                    v-if="hasRating"
                    :rating="Number(tutor.tutor_reviews_avg_rating ?? 0)"
                    :review-count="tutor.tutor_reviews_count ?? 0"
                    size="sm"
                    show-value
                />
                <span v-else class="text-xs text-slate-400"
                    >No reviews yet</span
                >
            </div>
        </CardContent>

        <CardFooter class="p-5 pt-0">
            <Button
                as-child
                class="h-10 w-full rounded-xl bg-slate-900 text-sm font-semibold text-white transition-colors hover:bg-slate-800"
            >
                <Link :href="`/tutors/${tutor.id}`">View Full Profile</Link>
            </Button>
        </CardFooter>
    </Card>
</template>
