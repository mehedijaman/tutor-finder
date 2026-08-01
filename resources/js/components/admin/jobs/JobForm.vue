<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import {
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    FileText,
    MapPin,
    Wallet,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Taxonomy {
    id: number | string;
    name: string;
    category_id?: number | string;
    country_id?: number | string;
    city_id?: number | string;
    class_id?: number | string;
}
interface Option {
    value: string;
    label: string;
}

const props = defineProps({
    action: { type: String, required: true },
    method: { type: String, default: 'post' },
    submitLabel: { type: String, required: true },
    cancelHref: { type: String, required: true },
    isAdmin: { type: Boolean, default: false },
    tuitionTypes: { type: Array as () => Taxonomy[], default: () => [] },
    categories: { type: Array as () => Taxonomy[], default: () => [] },
    schoolClasses: { type: Array as () => Taxonomy[], default: () => [] },
    countries: { type: Array as () => Taxonomy[], default: () => [] },
    cities: { type: Array as () => Taxonomy[], default: () => [] },
    areas: { type: Array as () => Taxonomy[], default: () => [] },
    subjects: { type: Array as () => Taxonomy[], default: () => [] },
    guardians: { type: Array as () => any[], default: () => [] },
    statusOptions: { type: Array as () => Option[], default: () => [] },
    genderOptions: { type: Array as () => Option[], default: () => [] },
    dayOptions: { type: Array as () => Option[], default: () => [] },
    hideCurrency: { type: Boolean, default: false },
    initial: {
        type: Object,
        default: () => ({
            title: '',
            description: '',
            tuition_type_id: '',
            category_id: '',
            class_id: '',
            country_id: '',
            city_id: '',
            area_id: '',
            guardian_id: '',
            location: '',
            latitude: '',
            longitude: '',
            student_gender: 'any',
            tutor_gender: 'any',
            tuition_days: [],
            tuition_time: '',
            tuition_duration: '',
            no_of_students: '',
            salary_amount: '',
            salary_currency: 'BDT',
            salary_negotiable: false,
            status: 'pending',
            expires_at: '',
            published_at: '',
            subject_ids: [],
            requested_tutor_id: null,
        }),
    },
});

const currentStep = ref(1);

const form = useForm({
    title: props.initial.title ?? '',
    description: props.initial.description ?? '',
    tuition_type_id: props.initial.tuition_type_id ?? '',
    category_id: props.initial.category_id ?? '',
    class_id: props.initial.class_id ?? '',
    country_id: props.initial.country_id ?? '',
    city_id: props.initial.city_id ?? '',
    area_id: props.initial.area_id ?? '',
    guardian_id: props.initial.guardian_id ?? '',
    location: props.initial.location ?? '',
    latitude: props.initial.latitude ?? '',
    longitude: props.initial.longitude ?? '',
    student_gender: props.initial.student_gender ?? 'any',
    tutor_gender: props.initial.tutor_gender ?? 'any',
    tuition_days: props.initial.tuition_days ?? [],
    tuition_time: props.initial.tuition_time ?? '',
    tuition_duration: props.initial.tuition_duration ?? '',
    no_of_students: props.initial.no_of_students ?? '',
    salary_amount: props.initial.salary_amount ?? '',
    salary_currency: props.initial.salary_currency ?? 'BDT',
    salary_negotiable: Boolean(props.initial.salary_negotiable ?? false),
    status: props.initial.status ?? 'pending',
    expires_at: props.initial.expires_at ?? '',
    published_at: props.initial.published_at ?? '',
    subject_ids: props.initial.subject_ids ?? [],
    requested_tutor_id: props.initial.requested_tutor_id ?? null,
});

const filteredClasses = computed(() => {
    const categoryId = Number(form.category_id);
    if (!categoryId) return props.schoolClasses;
    return props.schoolClasses.filter(
        (schoolClass) => Number(schoolClass.category_id) === categoryId,
    );
});

const filteredCities = computed(() => {
    const countryId = Number(form.country_id);
    if (!countryId) return props.cities;
    return props.cities.filter((city) => Number(city.country_id) === countryId);
});

const filteredAreas = computed(() => {
    const cityId = Number(form.city_id);
    if (!cityId) return props.areas;
    return props.areas.filter((area) => Number(area.city_id) === cityId);
});

const filteredSubjects = computed(() => {
    const classId = Number(form.class_id);
    if (!classId) return props.subjects;
    return props.subjects.filter(
        (subject) => Number(subject.class_id) === classId,
    );
});

const subjectError = computed(() => {
    if (form.errors.subject_ids) return form.errors.subject_ids;
    // @ts-ignore
    return form.errors['subject_ids.0'] ?? '';
});

const selectedCategoryName = computed(() => {
    const cat = props.categories.find((c) => String(c.id) === String(form.category_id));
    return cat?.name ?? 'Not selected';
});

const selectedClassName = computed(() => {
    const sc = props.schoolClasses.find((c) => String(c.id) === String(form.class_id));
    return sc?.name ?? 'Not selected';
});

const selectedCityName = computed(() => {
    const city = props.cities.find((c) => String(c.id) === String(form.city_id));
    return city?.name ?? 'Not selected';
});

const selectedSubjectNames = computed(() => {
    return props.subjects
        .filter((s) => form.subject_ids.includes(s.id as never))
        .map((s) => s.name);
});

watch(
    () => form.category_id,
    () => {
        const classIds = filteredClasses.value.map((schoolClass) => Number(schoolClass.id));
        if (form.class_id && !classIds.includes(Number(form.class_id))) {
            form.class_id = '';
            form.subject_ids = [];
        }
    },
);

watch(
    () => form.country_id,
    () => {
        const cityIds = filteredCities.value.map((city) => Number(city.id));
        if (form.city_id && !cityIds.includes(Number(form.city_id))) {
            form.city_id = '';
            form.area_id = '';
        }
    },
);

watch(
    () => form.city_id,
    () => {
        const areaIds = filteredAreas.value.map((area) => Number(area.id));
        if (form.area_id && !areaIds.includes(Number(form.area_id))) {
            form.area_id = '';
        }
    },
);

watch(
    () => form.class_id,
    () => {
        const subjectIds = filteredSubjects.value.map((subject) => Number(subject.id));
        form.subject_ids = (form.subject_ids ?? []).filter((subjectId: any) =>
            subjectIds.includes(Number(subjectId)),
        );
    },
);

function goToNextStep() {
    if (currentStep.value < 3) {
        currentStep.value++;
    }
}

function goToPrevStep() {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
}

function submit() {
    const transformPayload = (data: any) => {
        if (props.isAdmin) return data;
        const payload = { ...data };
        delete payload.guardian_id;
        delete payload.status;
        delete payload.published_at;
        return payload;
    };

    if (props.method.toLowerCase() === 'put') {
        form.transform((data) => ({
            ...transformPayload(data),
            _method: 'put',
        })).post(props.action, { preserveScroll: true });
        return;
    }

    form.transform(transformPayload).post(props.action, {
        preserveScroll: true,
    });
}
</script>

<template>
    <div class="space-y-6">
        <!-- 3-Step Wizard Navigation Bar -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="grid grid-cols-3 gap-2">
                <!-- Step 1 Indicator -->
                <button
                    type="button"
                    class="flex items-center gap-3 rounded-xl p-3 text-left transition-all"
                    :class="
                        currentStep === 1
                            ? 'bg-blue-600 text-white shadow-md shadow-blue-200'
                            : currentStep > 1
                              ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                              : 'bg-slate-50 text-slate-500 border border-slate-200/60'
                    "
                    @click="currentStep = 1"
                >
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg font-bold text-xs"
                        :class="
                            currentStep === 1
                                ? 'bg-white/20 text-white'
                                : currentStep > 1
                                  ? 'bg-emerald-100 text-emerald-700'
                                  : 'bg-slate-200 text-slate-600'
                        "
                    >
                        <CheckCircle2 v-if="currentStep > 1" class="h-4 w-4 text-emerald-600" />
                        <span v-else>1</span>
                    </div>
                    <div class="hidden min-w-0 sm:block">
                        <p class="text-xs font-bold uppercase tracking-wider">Step 1</p>
                        <p class="truncate text-xs font-semibold">Category & Class</p>
                    </div>
                </button>

                <!-- Step 2 Indicator -->
                <button
                    type="button"
                    class="flex items-center gap-3 rounded-xl p-3 text-left transition-all"
                    :class="
                        currentStep === 2
                            ? 'bg-blue-600 text-white shadow-md shadow-blue-200'
                            : currentStep > 2
                              ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                              : 'bg-slate-50 text-slate-500 border border-slate-200/60'
                    "
                    @click="currentStep = 2"
                >
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg font-bold text-xs"
                        :class="
                            currentStep === 2
                                ? 'bg-white/20 text-white'
                                : currentStep > 2
                                  ? 'bg-emerald-100 text-emerald-700'
                                  : 'bg-slate-200 text-slate-600'
                        "
                    >
                        <CheckCircle2 v-if="currentStep > 2" class="h-4 w-4 text-emerald-600" />
                        <span v-else>2</span>
                    </div>
                    <div class="hidden min-w-0 sm:block">
                        <p class="text-xs font-bold uppercase tracking-wider">Step 2</p>
                        <p class="truncate text-xs font-semibold">Schedule & Location</p>
                    </div>
                </button>

                <!-- Step 3 Indicator -->
                <button
                    type="button"
                    class="flex items-center gap-3 rounded-xl p-3 text-left transition-all"
                    :class="
                        currentStep === 3
                            ? 'bg-blue-600 text-white shadow-md shadow-blue-200'
                            : 'bg-slate-50 text-slate-500 border border-slate-200/60'
                    "
                    @click="currentStep = 3"
                >
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg font-bold text-xs"
                        :class="currentStep === 3 ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'"
                    >
                        3
                    </div>
                    <div class="hidden min-w-0 sm:block">
                        <p class="text-xs font-bold uppercase tracking-wider">Step 3</p>
                        <p class="truncate text-xs font-semibold">Salary & Review</p>
                    </div>
                </button>
            </div>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <!-- STEP 1: Category, Curriculum & Class -->
            <div v-show="currentStep === 1" class="space-y-6">
                <section class="grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <FileText class="h-5 w-5 text-blue-600" />
                        <h2 class="text-lg font-bold text-slate-900">Job Title & Description</h2>
                    </div>

                    <div class="grid gap-2">
                        <Label for="job-title">Job Title</Label>
                        <Input
                            id="job-title"
                            v-model="form.title"
                            type="text"
                            placeholder="e.g. Need Class 8 English Medium Tutor for Math & Science"
                            required
                        />
                        <InputError :message="form.errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="job-description">Description / Requirements</Label>
                        <textarea
                            id="job-description"
                            v-model="form.description"
                            class="min-h-32 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Describe any specific requirements, timing preferences, or expectations..."
                            required
                        />
                        <InputError :message="form.errors.description" />
                    </div>
                </section>

                <section class="grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 sm:grid-cols-2">
                    <div class="sm:col-span-2 border-b border-slate-100 pb-3">
                        <h2 class="text-lg font-bold text-slate-900">Tuition Type, Curriculum & Class</h2>
                    </div>

                    <div class="grid gap-2">
                        <Label for="job-tuition-type">Tuition Type</Label>
                        <select
                            id="job-tuition-type"
                            v-model="form.tuition_type_id"
                            class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm"
                            required
                        >
                            <option value="">Select tuition type</option>
                            <option
                                v-for="item in tuitionTypes"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.tuition_type_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="job-category">Category / Curriculum</Label>
                        <select
                            id="job-category"
                            v-model="form.category_id"
                            class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm"
                            required
                        >
                            <option value="">Select curriculum / category</option>
                            <option
                                v-for="item in categories"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.category_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="job-class">Class / Standard</Label>
                        <select
                            id="job-class"
                            v-model="form.class_id"
                            class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm"
                            required
                        >
                            <option value="">Select class</option>
                            <option
                                v-for="item in filteredClasses"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.class_id" />
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <Label>Subjects (select all that apply)</Label>
                        <div class="grid gap-2 rounded-xl border border-slate-200 bg-slate-50/50 p-4 sm:grid-cols-2 lg:grid-cols-3">
                            <label
                                v-for="subject in filteredSubjects"
                                :key="subject.id"
                                class="flex items-center gap-2 rounded-lg bg-white p-2 border border-slate-200 text-sm font-medium transition hover:border-blue-300 cursor-pointer"
                            >
                                <input
                                    v-model="form.subject_ids"
                                    type="checkbox"
                                    :value="subject.id"
                                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span>{{ subject.name }}</span>
                            </label>

                            <p
                                v-if="filteredSubjects.length === 0"
                                class="text-xs text-muted-foreground col-span-full"
                            >
                                Please select a class to view subjects.
                            </p>
                        </div>
                        <InputError :message="subjectError" />
                    </div>
                </section>
            </div>

            <!-- STEP 2: Schedule & Location -->
            <div v-show="currentStep === 2" class="space-y-6">
                <section class="grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 sm:grid-cols-2">
                    <div class="sm:col-span-2 flex items-center gap-2 border-b border-slate-100 pb-3">
                        <MapPin class="h-5 w-5 text-rose-500" />
                        <h2 class="text-lg font-bold text-slate-900">Location</h2>
                    </div>

                    <div class="grid gap-2">
                        <Label for="job-country">Country</Label>
                        <select
                            id="job-country"
                            v-model="form.country_id"
                            class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm"
                            required
                        >
                            <option value="">Select country</option>
                            <option
                                v-for="item in countries"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.country_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="job-city">City</Label>
                        <select
                            id="job-city"
                            v-model="form.city_id"
                            class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm"
                            required
                        >
                            <option value="">Select city</option>
                            <option
                                v-for="item in filteredCities"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.city_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="job-area">Area</Label>
                        <select
                            id="job-area"
                            v-model="form.area_id"
                            class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm"
                        >
                            <option value="">Select area (optional)</option>
                            <option
                                v-for="item in filteredAreas"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.area_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="job-location">Location / Landmark Address</Label>
                        <Input id="job-location" v-model="form.location" type="text" placeholder="e.g. Near City College, Dhanmondi 2" />
                        <InputError :message="form.errors.location" />
                    </div>
                </section>

                <section class="grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 sm:grid-cols-2">
                    <div class="sm:col-span-2 border-b border-slate-100 pb-3">
                        <h2 class="text-lg font-bold text-slate-900">Schedule & Days</h2>
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <Label>Tuition Days</Label>
                        <div class="grid gap-2 rounded-xl border border-slate-200 bg-slate-50/50 p-4 sm:grid-cols-3 lg:grid-cols-4">
                            <label
                                v-for="day in dayOptions"
                                :key="day.value"
                                class="flex items-center gap-2 rounded-lg bg-white p-2 border border-slate-200 text-sm font-medium cursor-pointer"
                            >
                                <input
                                    v-model="form.tuition_days"
                                    type="checkbox"
                                    :value="day.value"
                                    class="h-4 w-4 rounded border-slate-300 text-blue-600"
                                />
                                <span>{{ day.label }}</span>
                            </label>
                        </div>
                        <InputError :message="form.errors.tuition_days" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="job-tuition-time">Tuition Time</Label>
                        <Input
                            id="job-tuition-time"
                            v-model="form.tuition_time"
                            type="text"
                            placeholder="e.g. 5:00 PM - 7:00 PM"
                        />
                        <InputError :message="form.errors.tuition_time" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="job-tuition-duration">Tuition Duration</Label>
                        <Input
                            id="job-tuition-duration"
                            v-model="form.tuition_duration"
                            type="text"
                            placeholder="e.g. 1.5 hours per session"
                        />
                        <InputError :message="form.errors.tuition_duration" />
                    </div>
                </section>
            </div>

            <!-- STEP 3: Salary, Tutor Requirements & Review -->
            <div v-show="currentStep === 3" class="space-y-6">
                <section class="grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 sm:grid-cols-2">
                    <div class="sm:col-span-2 flex items-center gap-2 border-b border-slate-100 pb-3">
                        <Wallet class="h-5 w-5 text-emerald-600" />
                        <h2 class="text-lg font-bold text-slate-900">Salary & Gender Requirements</h2>
                    </div>

                    <div class="grid gap-2">
                        <Label for="job-student-gender">Student Gender</Label>
                        <select
                            id="job-student-gender"
                            v-model="form.student_gender"
                            class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm"
                            required
                        >
                            <option
                                v-for="option in genderOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <InputError :message="form.errors.student_gender" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="job-tutor-gender">Tutor Gender Preference</Label>
                        <select
                            id="job-tutor-gender"
                            v-model="form.tutor_gender"
                            class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm"
                            required
                        >
                            <option
                                v-for="option in genderOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <InputError :message="form.errors.tutor_gender" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="job-students">No. of Students</Label>
                        <Input
                            id="job-students"
                            v-model="form.no_of_students"
                            type="number"
                            min="1"
                            placeholder="e.g. 1"
                        />
                        <InputError :message="form.errors.no_of_students" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="job-salary-amount">Salary Amount (BDT / month)</Label>
                        <Input
                            id="job-salary-amount"
                            v-model="form.salary_amount"
                            type="number"
                            min="0"
                            placeholder="e.g. 8000"
                        />
                        <InputError :message="form.errors.salary_amount" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input
                                v-model="form.salary_negotiable"
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600"
                            />
                            <span class="font-semibold text-slate-700">Salary is negotiable</span>
                        </label>
                        <InputError :message="form.errors.salary_negotiable" />
                    </div>

                    <template v-if="isAdmin">
                        <div class="grid gap-2">
                            <Label for="job-guardian">Guardian</Label>
                            <select
                                id="job-guardian"
                                v-model="form.guardian_id"
                                class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm"
                                required
                            >
                                <option value="">Select guardian</option>
                                <option
                                    v-for="guardian in guardians"
                                    :key="guardian.id"
                                    :value="guardian.id"
                                >
                                    {{ guardian.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.guardian_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="job-status">Initial Status</Label>
                            <select
                                id="job-status"
                                v-model="form.status"
                                class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm"
                                required
                            >
                                <option
                                    v-for="status in statusOptions"
                                    :key="status.value"
                                    :value="status.value"
                                >
                                    {{ status.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.status" />
                        </div>
                    </template>
                </section>

                <!-- Summary Preview Card -->
                <div class="rounded-2xl border border-blue-200 bg-blue-50/40 p-5 space-y-3">
                    <h3 class="font-bold text-blue-900 text-sm uppercase tracking-wide">Review Job Summary</h3>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-slate-400 font-semibold block">Title</span>
                            <span class="font-bold text-slate-800">{{ form.title || '—' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold block">Curriculum & Class</span>
                            <span class="font-bold text-slate-800">{{ selectedCategoryName }} — {{ selectedClassName }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold block">City</span>
                            <span class="font-bold text-slate-800">{{ selectedCityName }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold block">Salary</span>
                            <span class="font-bold text-slate-800">
                                {{ form.salary_negotiable ? 'Negotiable' : `${form.salary_currency} ${form.salary_amount || '—'}` }}
                            </span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-slate-400 font-semibold block">Selected Subjects</span>
                            <div class="flex flex-wrap gap-1 pt-1">
                                <Badge v-for="subj in selectedSubjectNames" :key="subj" variant="secondary" class="bg-blue-100 text-blue-800 text-[10px]">
                                    {{ subj }}
                                </Badge>
                                <span v-if="!selectedSubjectNames.length" class="text-slate-400">—</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-200">
                <Button
                    v-if="currentStep > 1"
                    type="button"
                    variant="outline"
                    class="gap-2"
                    @click="goToPrevStep"
                >
                    <ChevronLeft class="h-4 w-4" />
                    Previous Step
                </Button>
                <div v-else />

                <div class="flex items-center gap-3">
                    <Link
                        :href="cancelHref"
                        class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-4 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                    >
                        Cancel
                    </Link>

                    <Button
                        v-if="currentStep < 3"
                        type="button"
                        class="bg-blue-600 hover:bg-blue-700 gap-2"
                        @click="goToNextStep"
                    >
                        Next Step
                        <ChevronRight class="h-4 w-4" />
                    </Button>

                    <Button
                        v-else
                        type="submit"
                        class="bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-200"
                        :disabled="form.processing"
                    >
                        {{ submitLabel }}
                    </Button>
                </div>
            </div>
        </form>
    </div>
</template>
