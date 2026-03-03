<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { computed, toRef, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { slugify, useAutoSlug } from '@/composables/useAutoSlug';

const props = defineProps({
    action: { type: String, required: true },
    method: { type: String, default: 'post' },
    submitLabel: { type: String, required: true },
    cancelHref: { type: String, required: true },
    isAdmin: { type: Boolean, default: false },
    tuitionTypes: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    schoolClasses: { type: Array, default: () => [] },
    countries: { type: Array, default: () => [] },
    cities: { type: Array, default: () => [] },
    areas: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    guardians: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
    genderOptions: { type: Array, default: () => [] },
    dayOptions: { type: Array, default: () => [] },
    initial: {
        type: Object,
        default: () => ({
            title: '',
            slug: '',
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
        }),
    },
});

const form = useForm({
    title: props.initial.title ?? '',
    slug: props.initial.slug ?? '',
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
});

const isInitiallyAuto = (() => {
    const sourceTitle = String(props.initial.title ?? '');
    const currentSlug = String(props.initial.slug ?? '');

    if (currentSlug === '') {
        return true;
    }

    return slugify(sourceTitle) === currentSlug;
})();

const { autoSlug, onManualSlugInput, toggleAutoSlug } = useAutoSlug(
    toRef(form, 'title'),
    toRef(form, 'slug'),
    {
        initiallyAuto: isInitiallyAuto,
    },
);

const filteredClasses = computed(() => {
    const categoryId = Number(form.category_id);

    if (!categoryId) {
        return props.schoolClasses;
    }

    return props.schoolClasses.filter(
        (schoolClass) => Number(schoolClass.category_id) === categoryId,
    );
});

const filteredCities = computed(() => {
    const countryId = Number(form.country_id);

    if (!countryId) {
        return props.cities;
    }

    return props.cities.filter((city) => Number(city.country_id) === countryId);
});

const filteredAreas = computed(() => {
    const cityId = Number(form.city_id);

    if (!cityId) {
        return props.areas;
    }

    return props.areas.filter((area) => Number(area.city_id) === cityId);
});

const filteredSubjects = computed(() => {
    const classId = Number(form.class_id);

    if (!classId) {
        return props.subjects;
    }

    return props.subjects.filter(
        (subject) => Number(subject.class_id) === classId,
    );
});

const subjectError = computed(() => {
    if (form.errors.subject_ids) {
        return form.errors.subject_ids;
    }

    return form.errors['subject_ids.0'] ?? '';
});

watch(
    () => form.category_id,
    () => {
        const classIds = filteredClasses.value.map((schoolClass) =>
            Number(schoolClass.id),
        );

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
        const subjectIds = filteredSubjects.value.map((subject) =>
            Number(subject.id),
        );
        form.subject_ids = (form.subject_ids ?? []).filter((subjectId) =>
            subjectIds.includes(Number(subjectId)),
        );
    },
);

function submit() {
    const transformPayload = (data) => {
        if (props.isAdmin) {
            return data;
        }

        const { guardian_id, status, published_at, ...payload } = data;

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
    <form class="space-y-6" @submit.prevent="submit">
        <section class="grid gap-4 rounded-xl border bg-white p-4">
            <h2 class="text-lg font-semibold">Job Details</h2>

            <div class="grid gap-2">
                <Label for="job-title">Title</Label>
                <Input
                    id="job-title"
                    v-model="form.title"
                    type="text"
                    required
                />
                <InputError :message="form.errors.title" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between gap-3">
                    <Label for="job-slug">Slug</Label>
                    <Button
                        type="button"
                        size="sm"
                        variant="outline"
                        @click="toggleAutoSlug"
                    >
                        Auto: {{ autoSlug ? 'On' : 'Off' }}
                    </Button>
                </div>
                <Input
                    id="job-slug"
                    :model-value="form.slug"
                    type="text"
                    @update:model-value="onManualSlugInput"
                />
                <InputError :message="form.errors.slug" />
            </div>

            <div class="grid gap-2">
                <Label for="job-description">Description</Label>
                <textarea
                    id="job-description"
                    v-model="form.description"
                    class="min-h-36 rounded-md border px-3 py-2 text-sm"
                    required
                />
                <InputError :message="form.errors.description" />
            </div>
        </section>

        <section
            class="grid gap-4 rounded-xl border bg-white p-4 lg:grid-cols-2"
        >
            <h2 class="text-lg font-semibold lg:col-span-2">
                Taxonomies and Location
            </h2>

            <div class="grid gap-2">
                <Label for="job-tuition-type">Tuition Type</Label>
                <select
                    id="job-tuition-type"
                    v-model="form.tuition_type_id"
                    class="h-10 rounded-md border px-3 text-sm"
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
                <Label for="job-category">Category</Label>
                <select
                    id="job-category"
                    v-model="form.category_id"
                    class="h-10 rounded-md border px-3 text-sm"
                    required
                >
                    <option value="">Select category</option>
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
                <Label for="job-class">Class</Label>
                <select
                    id="job-class"
                    v-model="form.class_id"
                    class="h-10 rounded-md border px-3 text-sm"
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

            <div class="grid gap-2">
                <Label for="job-country">Country</Label>
                <select
                    id="job-country"
                    v-model="form.country_id"
                    class="h-10 rounded-md border px-3 text-sm"
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
                    class="h-10 rounded-md border px-3 text-sm"
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
                    class="h-10 rounded-md border px-3 text-sm"
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

            <div class="grid gap-2 lg:col-span-2">
                <Label for="job-location">Location (landmark)</Label>
                <Input id="job-location" v-model="form.location" type="text" />
                <InputError :message="form.errors.location" />
            </div>

            <div class="grid gap-2">
                <Label for="job-latitude">Latitude</Label>
                <Input
                    id="job-latitude"
                    v-model="form.latitude"
                    type="number"
                    step="0.00000001"
                />
                <InputError :message="form.errors.latitude" />
            </div>

            <div class="grid gap-2">
                <Label for="job-longitude">Longitude</Label>
                <Input
                    id="job-longitude"
                    v-model="form.longitude"
                    type="number"
                    step="0.00000001"
                />
                <InputError :message="form.errors.longitude" />
            </div>

            <div class="grid gap-2 lg:col-span-2">
                <Label>Subjects (select at least one)</Label>
                <div
                    class="grid gap-2 rounded-md border p-3 md:grid-cols-2 lg:grid-cols-3"
                >
                    <label
                        v-for="subject in filteredSubjects"
                        :key="subject.id"
                        class="flex items-center gap-2 text-sm"
                    >
                        <input
                            v-model="form.subject_ids"
                            type="checkbox"
                            :value="subject.id"
                            class="h-4 w-4"
                        />
                        <span>{{ subject.name }}</span>
                    </label>

                    <p
                        v-if="filteredSubjects.length === 0"
                        class="text-xs text-muted-foreground"
                    >
                        No subjects available for the selected class.
                    </p>
                </div>
                <InputError :message="subjectError" />
            </div>
        </section>

        <section
            class="grid gap-4 rounded-xl border bg-white p-4 lg:grid-cols-2"
        >
            <h2 class="text-lg font-semibold lg:col-span-2">
                Schedule and Preferences
            </h2>

            <div class="grid gap-2">
                <Label for="job-student-gender">Student Gender</Label>
                <select
                    id="job-student-gender"
                    v-model="form.student_gender"
                    class="h-10 rounded-md border px-3 text-sm"
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
                    class="h-10 rounded-md border px-3 text-sm"
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

            <div class="grid gap-2 lg:col-span-2">
                <Label>Tuition Days</Label>
                <div
                    class="grid gap-2 rounded-md border p-3 md:grid-cols-3 lg:grid-cols-4"
                >
                    <label
                        v-for="day in dayOptions"
                        :key="day.value"
                        class="flex items-center gap-2 text-sm"
                    >
                        <input
                            v-model="form.tuition_days"
                            type="checkbox"
                            :value="day.value"
                            class="h-4 w-4"
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
                    placeholder="e.g. 3 months"
                />
                <InputError :message="form.errors.tuition_duration" />
            </div>

            <div class="grid gap-2">
                <Label for="job-students">No. of Students</Label>
                <Input
                    id="job-students"
                    v-model="form.no_of_students"
                    type="number"
                    min="1"
                />
                <InputError :message="form.errors.no_of_students" />
            </div>

            <div class="grid gap-2">
                <Label for="job-expires-at">Expires At</Label>
                <Input
                    id="job-expires-at"
                    v-model="form.expires_at"
                    type="datetime-local"
                />
                <InputError :message="form.errors.expires_at" />
            </div>
        </section>

        <section
            class="grid gap-4 rounded-xl border bg-white p-4 lg:grid-cols-2"
        >
            <h2 class="text-lg font-semibold lg:col-span-2">
                Salary and Publishing
            </h2>

            <div class="grid gap-2">
                <Label for="job-salary-amount">Salary Amount</Label>
                <Input
                    id="job-salary-amount"
                    v-model="form.salary_amount"
                    type="number"
                    min="0"
                    step="0.01"
                />
                <InputError :message="form.errors.salary_amount" />
            </div>

            <div class="grid gap-2">
                <Label for="job-salary-currency">Salary Currency</Label>
                <Input
                    id="job-salary-currency"
                    v-model="form.salary_currency"
                    type="text"
                />
                <InputError :message="form.errors.salary_currency" />
            </div>

            <label class="flex items-center gap-2 text-sm lg:col-span-2">
                <input
                    v-model="form.salary_negotiable"
                    type="checkbox"
                    class="h-4 w-4"
                />
                <span>Salary negotiable</span>
            </label>
            <InputError :message="form.errors.salary_negotiable" />

            <template v-if="isAdmin">
                <div class="grid gap-2">
                    <Label for="job-guardian">Guardian</Label>
                    <select
                        id="job-guardian"
                        v-model="form.guardian_id"
                        class="h-10 rounded-md border px-3 text-sm"
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
                        class="h-10 rounded-md border px-3 text-sm"
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

                <div class="grid gap-2 lg:col-span-2">
                    <Label for="job-published-at"
                        >Published At (optional)</Label
                    >
                    <Input
                        id="job-published-at"
                        v-model="form.published_at"
                        type="datetime-local"
                    />
                    <InputError :message="form.errors.published_at" />
                </div>
            </template>
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <Button type="submit" :disabled="form.processing">{{
                submitLabel
            }}</Button>
            <Link
                :href="cancelHref"
                class="text-sm text-muted-foreground underline"
                >Cancel</Link
            >
        </div>
    </form>
</template>
