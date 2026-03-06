<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Search, SlidersHorizontal, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

type FilterOption = {
    id: number;
    name: string;
};

type ClassFilterOption = {
    id: number;
    name: string;
    category_id: number;
};

type SubjectFilterOption = {
    id: number;
    name: string;
    class_id: number;
};

type DayFilterOption = {
    value: string;
    label: string;
};

type SimpleSelectOption = {
    value: string;
    label: string;
};

type FilterProps = {
    open: boolean;
    filters: {
        area?: string | null;
        gender?: string | null;
        min_budget?: string | null;
        max_budget?: string | null;
        tuition_type_id?: number | null;
        category_id?: number | null;
        class_id?: number | null;
        subject_id?: number | null;
        location_id?: number | null;
        available_day?: string | null;
        verified?: string | null;
    };
    filterOptions: {
        tuitionTypes: FilterOption[];
        categories: FilterOption[];
        classes: ClassFilterOption[];
        subjects: SubjectFilterOption[];
        locations: FilterOption[];
        days: DayFilterOption[];
        genders: SimpleSelectOption[];
        verified: SimpleSelectOption[];
    };
};

const props = defineProps<FilterProps>();

const emit = defineEmits<{
    close: [];
}>();

const form = ref({
    area: props.filters?.area || '',
    gender: props.filters?.gender || '',
    verified: props.filters?.verified || '',
    tuition_type_id:
        props.filters?.tuition_type_id !== null &&
        props.filters?.tuition_type_id !== undefined
            ? String(props.filters.tuition_type_id)
            : '',
    category_id:
        props.filters?.category_id !== null &&
        props.filters?.category_id !== undefined
            ? String(props.filters.category_id)
            : '',
    class_id:
        props.filters?.class_id !== null &&
        props.filters?.class_id !== undefined
            ? String(props.filters.class_id)
            : '',
    subject_id:
        props.filters?.subject_id !== null &&
        props.filters?.subject_id !== undefined
            ? String(props.filters.subject_id)
            : '',
    location_id:
        props.filters?.location_id !== null &&
        props.filters?.location_id !== undefined
            ? String(props.filters.location_id)
            : '',
    available_day: props.filters?.available_day || '',
    min_budget: props.filters?.min_budget || '',
    max_budget: props.filters?.max_budget || '',
});

const classesById = computed(() => {
    return new Map(
        props.filterOptions.classes.map((item) => [String(item.id), item]),
    );
});

const filteredClasses = computed(() => {
    if (!form.value.category_id) {
        return props.filterOptions.classes;
    }

    return props.filterOptions.classes.filter(
        (item) => String(item.category_id) === form.value.category_id,
    );
});

const filteredSubjects = computed(() => {
    if (form.value.class_id) {
        return props.filterOptions.subjects.filter(
            (item) => String(item.class_id) === form.value.class_id,
        );
    }

    if (form.value.category_id) {
        return props.filterOptions.subjects.filter((item) => {
            const linkedClass = classesById.value.get(String(item.class_id));

            return (
                linkedClass !== undefined &&
                String(linkedClass.category_id) === form.value.category_id
            );
        });
    }

    return props.filterOptions.subjects;
});

watch(
    () => props.filters,
    (newFilters) => {
        form.value = {
            area: newFilters.area || '',
            gender: newFilters.gender || '',
            verified: newFilters.verified || '',
            tuition_type_id:
                newFilters.tuition_type_id !== null &&
                newFilters.tuition_type_id !== undefined
                    ? String(newFilters.tuition_type_id)
                    : '',
            category_id:
                newFilters.category_id !== null &&
                newFilters.category_id !== undefined
                    ? String(newFilters.category_id)
                    : '',
            class_id:
                newFilters.class_id !== null &&
                newFilters.class_id !== undefined
                    ? String(newFilters.class_id)
                    : '',
            subject_id:
                newFilters.subject_id !== null &&
                newFilters.subject_id !== undefined
                    ? String(newFilters.subject_id)
                    : '',
            location_id:
                newFilters.location_id !== null &&
                newFilters.location_id !== undefined
                    ? String(newFilters.location_id)
                    : '',
            available_day: newFilters.available_day || '',
            min_budget: newFilters.min_budget || '',
            max_budget: newFilters.max_budget || '',
        };
    },
    { deep: true },
);

watch(
    () => form.value.category_id,
    (categoryId) => {
        if (!categoryId) {
            return;
        }

        if (!form.value.class_id) {
            return;
        }

        const selectedClass = classesById.value.get(form.value.class_id);

        if (
            !selectedClass ||
            String(selectedClass.category_id) !== categoryId
        ) {
            form.value.class_id = '';
            form.value.subject_id = '';
        }
    },
);

watch(
    () => form.value.class_id,
    (classId) => {
        if (!form.value.subject_id || !classId) {
            if (!classId) {
                form.value.subject_id = '';
            }

            return;
        }

        const isSubjectValid = filteredSubjects.value.some(
            (subject) => String(subject.id) === form.value.subject_id,
        );

        if (!isSubjectValid) {
            form.value.subject_id = '';
        }
    },
);

function applyFilters(): void {
    const params: Record<string, string> = {};

    if (form.value.area) params.area = form.value.area.trim();
    if (form.value.gender) params.gender = form.value.gender;
    if (form.value.verified) params.verified = form.value.verified;
    if (form.value.tuition_type_id)
        params.tuition_type_id = form.value.tuition_type_id;
    if (form.value.category_id) params.category_id = form.value.category_id;
    if (form.value.class_id) params.class_id = form.value.class_id;
    if (form.value.subject_id) params.subject_id = form.value.subject_id;
    if (form.value.location_id) params.location_id = form.value.location_id;
    if (form.value.available_day)
        params.available_day = form.value.available_day;
    if (form.value.min_budget) params.min_budget = form.value.min_budget;
    if (form.value.max_budget) params.max_budget = form.value.max_budget;

    router.get('/tutors', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });

    emit('close');
}

function resetFilters(): void {
    form.value = {
        area: '',
        gender: '',
        verified: '',
        tuition_type_id: '',
        category_id: '',
        class_id: '',
        subject_id: '',
        location_id: '',
        available_day: '',
        min_budget: '',
        max_budget: '',
    };

    router.get(
        '/tutors',
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

        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            leave-active-class="transition-transform duration-200 ease-in"
            enter-from-class="translate-x-full"
            leave-to-class="translate-x-full"
        >
            <div
                v-if="open"
                class="fixed top-0 right-0 z-50 flex h-full w-full flex-col bg-white shadow-2xl sm:w-[460px]"
            >
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
                                Filter Tutors
                            </h2>
                            <p class="text-sm text-slate-500">
                                Use all available criteria to refine results
                            </p>
                        </div>
                    </div>
                    <button
                        class="flex h-10 w-10 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                        @click="emit('close')"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex-1 space-y-8 overflow-y-auto px-6 py-5">
                    <section class="space-y-4">
                        <h3
                            class="text-xs font-semibold tracking-wider text-slate-500 uppercase"
                        >
                            Basic
                        </h3>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Area / Location Search
                            </label>
                            <div class="relative">
                                <Search
                                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                                />
                                <input
                                    v-model="form.area"
                                    type="text"
                                    placeholder="e.g. Dhanmondi"
                                    class="h-11 w-full rounded-xl border border-slate-200 pr-4 pl-10 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Gender
                                </label>
                                <select
                                    v-model="form.gender"
                                    class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
                                    <option value="">All</option>
                                    <option
                                        v-for="option in filterOptions.genders"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Verification
                                </label>
                                <select
                                    v-model="form.verified"
                                    class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
                                    <option value="">All</option>
                                    <option
                                        v-for="option in filterOptions.verified"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <h3
                            class="text-xs font-semibold tracking-wider text-slate-500 uppercase"
                        >
                            Preferences
                        </h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Tuition Type
                                </label>
                                <select
                                    v-model="form.tuition_type_id"
                                    class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
                                    <option value="">All</option>
                                    <option
                                        v-for="item in filterOptions.tuitionTypes"
                                        :key="item.id"
                                        :value="String(item.id)"
                                    >
                                        {{ item.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Category
                                </label>
                                <select
                                    v-model="form.category_id"
                                    class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
                                    <option value="">All</option>
                                    <option
                                        v-for="item in filterOptions.categories"
                                        :key="item.id"
                                        :value="String(item.id)"
                                    >
                                        {{ item.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Class
                                </label>
                                <select
                                    v-model="form.class_id"
                                    class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
                                    <option value="">All</option>
                                    <option
                                        v-for="item in filteredClasses"
                                        :key="item.id"
                                        :value="String(item.id)"
                                    >
                                        {{ item.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Subject
                                </label>
                                <select
                                    v-model="form.subject_id"
                                    class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
                                    <option value="">All</option>
                                    <option
                                        v-for="item in filteredSubjects"
                                        :key="item.id"
                                        :value="String(item.id)"
                                    >
                                        {{ item.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Preferred Location
                                </label>
                                <select
                                    v-model="form.location_id"
                                    class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
                                    <option value="">All</option>
                                    <option
                                        v-for="item in filterOptions.locations"
                                        :key="item.id"
                                        :value="String(item.id)"
                                    >
                                        {{ item.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Available Day
                                </label>
                                <select
                                    v-model="form.available_day"
                                    class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
                                    <option value="">All</option>
                                    <option
                                        v-for="item in filterOptions.days"
                                        :key="item.value"
                                        :value="item.value"
                                    >
                                        {{ item.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <h3
                            class="text-xs font-semibold tracking-wider text-slate-500 uppercase"
                        >
                            Budget
                        </h3>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Min (BDT)
                                </label>
                                <input
                                    v-model="form.min_budget"
                                    type="number"
                                    min="0"
                                    placeholder="Min"
                                    class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Max (BDT)
                                </label>
                                <input
                                    v-model="form.max_budget"
                                    type="number"
                                    min="0"
                                    placeholder="Max"
                                    class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                />
                            </div>
                        </div>
                    </section>
                </div>

                <div class="border-t border-slate-200 px-6 py-5">
                    <div class="flex gap-3">
                        <button
                            class="h-11 flex-1 rounded-xl border border-slate-300 px-4 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 focus:outline-none"
                            @click="resetFilters"
                        >
                            Reset
                        </button>
                        <button
                            class="h-11 flex-1 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none active:scale-[0.98]"
                            @click="applyFilters"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
