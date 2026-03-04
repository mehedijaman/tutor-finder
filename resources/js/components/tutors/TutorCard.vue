<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    MapPin,
    GraduationCap,
    Wallet,
    User,
    BadgeCheck,
    BadgeX,
} from 'lucide-vue-next';
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
    preferred_categories: string | null;
    preferred_classes: string | null;
    preferred_subjects: string | null;
    preferred_locations: string | null;
    available_days: string | null;
    available_time: string | null;
};

type Tutor = {
    id: number;
    name: string;
    slug: string;
    email: string;
    phone: string | null;
    verified_at: string | null;
    created_at: string;
    photo_url: string | null;
    tutor_profile: TutorProfile | null;
    tutor_educations: TutorEducation[];
};

const props = defineProps<{
    tutor: Tutor;
}>();

function getPrimaryEducation(): TutorEducation | null {
    const educations = props.tutor.tutor_educations ?? [];
    return educations.length > 0 ? educations[0] : null;
}

function getCityName(): string | null {
    return props.tutor.tutor_profile?.present_address ?? null;
}

function formatSalary(): string {
    const min = props.tutor.tutor_profile?.expected_salary_min ?? null;
    const max = props.tutor.tutor_profile?.expected_salary_max ?? null;

    if (!min && !max) return 'Negotiable';

    const format = (n: number) => `৳ ${n.toLocaleString()}`;

    if (min && max) return `${format(min)} - ${format(max)}`;
    if (min) return format(min);
    return max ? format(max) : 'Negotiable';
}

function getGenderLabel(): string {
    const gender = props.tutor.tutor_profile?.gender;
    if (gender === 'female') return 'Female';
    if (gender === 'male') return 'Male';
    return 'Any';
}
</script>

<template>
    <Card
        class="relative flex h-full flex-col overflow-hidden border-slate-200 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg"
    >
        <!-- Top Right: Verified/Unverified Badge -->
        <div class="absolute top-3 right-3 z-10">
            <Badge
                v-if="tutor.verified_at"
                class="bg-emerald-50 text-emerald-700 hover:bg-emerald-50"
            >
                <BadgeCheck class="mr-1 h-3.5 w-3.5" />
                Verified
            </Badge>
            <Badge
                v-else
                variant="outline"
                class="border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-50"
            >
                <BadgeX class="mr-1 h-3.5 w-3.5" />
                Unverified
            </Badge>
        </div>

        <!-- Top Left: Tutor ID -->
        <div class="absolute top-3 left-3 z-10">
            <span
                class="rounded-md bg-slate-900/80 px-2 py-1 text-xs font-medium text-white"
            >
                ID: {{ tutor.id }}
            </span>
        </div>

        <CardContent class="flex flex-1 flex-col items-center p-6 pt-12">
            <!-- Avatar Section -->
            <div class="relative">
                <div
                    class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full bg-slate-100 ring-4 ring-slate-100"
                >
                    <img
                        v-if="tutor.photo_url"
                        :src="tutor.photo_url"
                        :alt="tutor.name"
                        class="h-full w-full object-cover"
                    />
                    <User v-else class="h-10 w-10 text-slate-400" />
                </div>
            </div>

            <!-- Name -->
            <h3 class="mt-4 line-clamp-1 text-lg font-semibold text-slate-900">
                {{ tutor.name }}
            </h3>

            <!-- Gender Badge -->
            <div class="mt-2">
                <Badge variant="outline" class="text-slate-600">
                    {{ getGenderLabel() }}
                </Badge>
            </div>

            <!-- Education -->
            <div
                v-if="getPrimaryEducation()"
                class="mt-4 w-full rounded-lg bg-slate-50 p-3"
            >
                <div class="flex items-start gap-2">
                    <GraduationCap
                        class="mt-0.5 h-4 w-4 flex-shrink-0 text-slate-500"
                    />
                    <div class="min-w-0 flex-1">
                        <p
                            class="line-clamp-1 text-sm font-medium text-slate-900"
                        >
                            {{ getPrimaryEducation()?.department }}
                        </p>
                        <p class="line-clamp-1 text-xs text-slate-500">
                            {{ getPrimaryEducation()?.institute }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div
                v-if="getCityName()"
                class="mt-3 flex items-center gap-1.5 text-sm text-slate-500"
            >
                <MapPin class="h-4 w-4 flex-shrink-0" />
                <span class="line-clamp-1">{{ getCityName() }}</span>
            </div>

            <!-- Expected Salary -->
            <div class="mt-3 flex items-center gap-1.5 text-sm">
                <Wallet class="h-4 w-4 flex-shrink-0 text-slate-500" />
                <span class="font-medium text-slate-900">
                    {{ formatSalary() }}
                </span>
                <span class="text-slate-500">/month</span>
            </div>
        </CardContent>

        <!-- Footer -->
        <CardFooter class="p-0">
            <Button
                as-child
                class="h-auto w-full justify-center rounded-t-none rounded-b-xl py-3 text-sm font-semibold"
            >
                <Link :href="`/tutors/${tutor.id}`"> View Profile </Link>
            </Button>
        </CardFooter>
    </Card>
</template>
