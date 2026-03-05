<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { X, Search, SlidersHorizontal } from 'lucide-vue-next';
import { ref, watch } from 'vue';

type FilterOption = {
    id: number;
    name: string;
    slug?: string;
};

type FilterProps = {
    open: boolean;
    onClose: () => void;
    basePath?: string;
    filters: {
        q: string;
        category: string;
        tuition_type: string;
        subject_id: number | null;
        city_id: number | null;
        tutor_gender: string;
        days_per_week: number | null;
        min_salary: number | null;
        max_salary: number | null;
        sort: string;
    };
    categoryOptions: FilterOption[];
    tuitionTypeOptions: FilterOption[];
    subjectOptions: FilterOption[];
    cityOptions: FilterOption[];
    sortOptions: { value: string; label: string }[];
    genderOptions: { value: string; label: string }[];
    daysOptions: { value: string; label: string }[];
};

const props = withDefaults(defineProps<FilterProps>(), {
    basePath: '/jobs',
});

const emit = defineEmits<{
    close: [];
}>();

const form = ref({
    q: props.filters.q ?? '',
    category: props.filters.category ?? '',
    tuition_type: props.filters.tuition_type ?? '',
    subject_id: props.filters.subject_id
        ? String(props.filters.subject_id)
        : '',
    city_id: props.filters.city_id ? String(props.filters.city_id) : '',
    tutor_gender: props.filters.tutor_gender ?? 'any',
    days_per_week: props.filters.days_per_week
        ? String(props.filters.days_per_week)
        : '',
    min_salary:
        props.filters.min_salary != null
            ? String(props.filters.min_salary)
            : '',
    max_salary:
        props.filters.max_salary != null
            ? String(props.filters.max_salary)
            : '',
    sort: props.filters.sort ?? 'newest',
});

watch(
    () => props.filters,
    (newFilters) => {
        form.value = {
            q: newFilters.q ?? '',
            category: newFilters.category ?? '',
            tuition_type: newFilters.tuition_type ?? '',
            subject_id: newFilters.subject_id
                ? String(newFilters.subject_id)
                : '',
            city_id: newFilters.city_id ? String(newFilters.city_id) : '',
            tutor_gender: newFilters.tutor_gender ?? 'any',
            days_per_week: newFilters.days_per_week
                ? String(newFilters.days_per_week)
                : '',
            min_salary:
                newFilters.min_salary != null
                    ? String(newFilters.min_salary)
                    : '',
            max_salary:
                newFilters.max_salary != null
                    ? String(newFilters.max_salary)
                    : '',
            sort: newFilters.sort ?? 'newest',
        };
    },
    { deep: true },
);

function applyFilters(): void {
    const params: Record<string, string> = {};

    if (form.value.q) params.q = form.value.q;
    if (form.value.category) params.category = form.value.category;
    if (form.value.tuition_type) params.tuition_type = form.value.tuition_type;
    if (form.value.subject_id) params.subject_id = form.value.subject_id;
    if (form.value.city_id) params.city_id = form.value.city_id;
    if (form.value.tutor_gender && form.value.tutor_gender !== 'any')
        params.tutor_gender = form.value.tutor_gender;
    if (form.value.days_per_week)
        params.days_per_week = form.value.days_per_week;
    if (form.value.min_salary) params.min_salary = form.value.min_salary;
    if (form.value.max_salary) params.max_salary = form.value.max_salary;
    if (form.value.sort && form.value.sort !== 'newest')
        params.sort = form.value.sort;

    router.get(props.basePath, params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });

    emit('close');
}

function resetFilters(): void {
    form.value = {
        q: '',
        category: '',
        tuition_type: '',
        subject_id: '',
        city_id: '',
        tutor_gender: 'any',
        days_per_week: '',
        min_salary: '',
        max_salary: '',
        sort: 'newest',
    };

    router.get(
        props.basePath,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );

    emit('close');
}
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            leave-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm"
                @click="emit('close')"
            />
        </Transition>

        <!-- Drawer -->
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            leave-active-class="transition-transform duration-200 ease-in"
            enter-from-class="translate-x-full"
            leave-to-class="translate-x-full"
        >
            <div
                v-if="open"
                class="fixed top-0 right-0 z-50 flex h-full w-full flex-col bg-white shadow-2xl sm:w-105"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-slate-200 px-6 py-5"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100"
                        >
                            <SlidersHorizontal class="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">
                                Filter Jobs
                            </h2>
                            <p class="text-sm text-slate-500">
                                Refine jobs by your preferences
                            </p>
                        </div>
                    </div>
                    <button
                        @click="emit('close')"
                        class="flex h-10 w-10 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto px-6 py-5">
                    <div class="space-y-6">
                        <!-- Search -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                                >Search</label
                            >
                            <div class="relative">
                                <Search
                                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                                />
                                <input
                                    v-model="form.q"
                                    type="text"
                                    placeholder="Search by title or keyword"
                                    class="h-11 w-full rounded-xl border border-slate-200 pr-4 pl-10 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                />
                            </div>
                        </div>

                        <!-- City -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                                >City</label
                            >
                            <select
                                v-model="form.city_id"
                                class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            >
                                <option value="">All Cities</option>
                                <option
                                    v-for="city in cityOptions"
                                    :key="city.id"
                                    :value="String(city.id)"
                                >
                                    {{ city.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Category -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                                >Category</label
                            >
                            <select
                                v-model="form.category"
                                class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            >
                                <option value="">All Categories</option>
                                <option
                                    v-for="cat in categoryOptions"
                                    :key="cat.id"
                                    :value="cat.slug"
                                >
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Tuition Type -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                                >Tuition Type</label
                            >
                            <select
                                v-model="form.tuition_type"
                                class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            >
                                <option value="">All Types</option>
                                <option
                                    v-for="type in tuitionTypeOptions"
                                    :key="type.id"
                                    :value="type.slug"
                                >
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Tutor Gender -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                                >Tutor Gender</label
                            >
                            <select
                                v-model="form.tutor_gender"
                                class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            >
                                <option
                                    v-for="opt in genderOptions"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Days per Week -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                                >Days per Week</label
                            >
                            <select
                                v-model="form.days_per_week"
                                class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            >
                                <option value="">Any</option>
                                <option
                                    v-for="opt in daysOptions"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Subject -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                                >Subject</label
                            >
                            <select
                                v-model="form.subject_id"
                                class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            >
                                <option value="">All Subjects</option>
                                <option
                                    v-for="sub in subjectOptions"
                                    :key="sub.id"
                                    :value="String(sub.id)"
                                >
                                    {{ sub.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Salary Range -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                                >Salary Range (BDT)</label
                            >
                            <div class="grid grid-cols-2 gap-3">
                                <input
                                    v-model="form.min_salary"
                                    type="number"
                                    min="0"
                                    placeholder="Min"
                                    class="h-11 rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                />
                                <input
                                    v-model="form.max_salary"
                                    type="number"
                                    min="0"
                                    placeholder="Max"
                                    class="h-11 rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                />
                            </div>
                        </div>

                        <!-- Sort -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                                >Sort By</label
                            >
                            <select
                                v-model="form.sort"
                                class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            >
                                <option
                                    v-for="opt in sortOptions"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-slate-200 px-6 py-5">
                    <div class="flex gap-3">
                        <button
                            @click="resetFilters"
                            class="h-11 flex-1 rounded-xl border border-slate-300 px-4 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 focus:outline-none"
                        >
                            Reset
                        </button>
                        <button
                            @click="applyFilters"
                            class="h-11 flex-1 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none active:scale-[0.98]"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
