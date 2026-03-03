<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { X, SlidersHorizontal, Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';

type FilterProps = {
    open: boolean;
    filters: {
        area?: string;
        gender?: string;
        min_budget?: string;
        max_budget?: string;
    };
};

const props = defineProps<FilterProps>();

const emit = defineEmits<{
    close: [];
}>();

const form = ref({
    area: props.filters?.area || '',
    gender: props.filters?.gender || '',
    min_budget: props.filters?.min_budget || '',
    max_budget: props.filters?.max_budget || '',
});

watch(
    () => props.filters,
    (newFilters) => {
        form.value = {
            area: newFilters.area || '',
            gender: newFilters.gender || '',
            min_budget: newFilters?.min_budget || '',
            max_budget: newFilters?.max_budget || '',
        };
    },
    { deep: true },
);

function applyFilters(): void {
    const params: Record<string, string> = {};

    if (form.value.area) params.area = form.value.area;
    if (form.value.gender) params.gender = form.value.gender;
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
                class="fixed top-0 right-0 z-50 flex h-full w-full flex-col bg-white shadow-2xl sm:w-[420px]"
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
                                Filter Tutors
                            </h2>
                            <p class="text-sm text-slate-500">
                                Refine tutors by your preferences
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
                        <!-- Area Search -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                                >Area</label
                            >
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

                        <!-- Gender -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                                >Gender</label
                            >
                            <select
                                v-model="form.gender"
                                class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            >
                                <option value="">All</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>

                        <!-- Budget Range -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                                >Expected Budget (BDT)</label
                            >
                            <div class="grid grid-cols-2 gap-3">
                                <input
                                    v-model="form.min_budget"
                                    type="number"
                                    min="0"
                                    placeholder="Min"
                                    class="h-11 rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                />
                                <input
                                    v-model="form.max_budget"
                                    type="number"
                                    min="0"
                                    placeholder="Max"
                                    class="h-11 rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                />
                            </div>
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
