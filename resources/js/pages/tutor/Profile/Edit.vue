<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import TutorLayout from '@/layouts/TutorLayout.vue';

const props = defineProps({
    profile: {
        type: Object,
        required: true,
    },
    tuitionTypes: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    schoolClasses: {
        type: Array,
        default: () => [],
    },
    subjects: {
        type: Array,
        default: () => [],
    },
    locations: {
        type: Array,
        default: () => [],
    },
    dayOptions: {
        type: Array,
        default: () => [],
    },
    genderOptions: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [{ title: 'Tutor Profile', href: '/tutor/profile' }];

const tabs = [
    { key: 'personal', label: 'Personal' },
    { key: 'education', label: 'Education' },
    { key: 'preferences', label: 'Preferences' },
];

const activeTab = ref('personal');

const form = useForm({
    name: props.profile.name ?? '',
    phone: props.profile.phone ?? '',
    gender: props.profile.gender ?? 'none',
    date_of_birth: props.profile.date_of_birth ?? '',
    present_address: props.profile.present_address ?? '',
    permanent_address: props.profile.permanent_address ?? '',
    nid_no: props.profile.nid_no ?? '',
    bio: props.profile.bio ?? '',
    preferred_tuition_types: Array.isArray(
        props.profile.preferred_tuition_types,
    )
        ? [...props.profile.preferred_tuition_types]
        : [],
    preferred_categories: Array.isArray(props.profile.preferred_categories)
        ? [...props.profile.preferred_categories]
        : [],
    preferred_classes: Array.isArray(props.profile.preferred_classes)
        ? [...props.profile.preferred_classes]
        : [],
    preferred_subjects: Array.isArray(props.profile.preferred_subjects)
        ? [...props.profile.preferred_subjects]
        : [],
    preferred_locations: Array.isArray(props.profile.preferred_locations)
        ? [...props.profile.preferred_locations]
        : [],
    expected_salary_min: props.profile.expected_salary_min ?? '',
    expected_salary_max: props.profile.expected_salary_max ?? '',
    available_days: Array.isArray(props.profile.available_days)
        ? [...props.profile.available_days]
        : [],
    available_time: props.profile.available_time ?? '',
    status: props.profile.status ?? 'active',
    educations: Array.isArray(props.profile.educations)
        ? props.profile.educations.map((education, index) => ({
              id: education.id ?? null,
              degree: education.degree ?? '',
              institute: education.institute ?? '',
              department: education.department ?? '',
              graduation_year: education.graduation_year ?? '',
              result: education.result ?? '',
              is_current: Boolean(education.is_current),
              sort_order: education.sort_order ?? index,
          }))
        : [],
});

const filteredClasses = computed(() => {
    const selectedCategories = new Set(form.preferred_categories);

    if (!selectedCategories.size) {
        return props.schoolClasses;
    }

    return props.schoolClasses.filter((schoolClass) =>
        selectedCategories.has(schoolClass.category_id),
    );
});

const filteredSubjects = computed(() => {
    const selectedClasses = new Set(form.preferred_classes);

    if (!selectedClasses.size) {
        return props.subjects;
    }

    return props.subjects.filter((subject) =>
        selectedClasses.has(subject.class_id),
    );
});

function submit() {
    form.transform((data) => ({
        ...data,
        gender: data.gender === 'none' ? null : data.gender,
        educations: data.educations.map((education, index) => ({
            ...education,
            sort_order: index,
        })),
    })).put('/tutor/profile', {
        preserveScroll: true,
    });
}

function addEducation() {
    form.educations.push({
        id: null,
        degree: '',
        institute: '',
        department: '',
        graduation_year: '',
        result: '',
        is_current: false,
        sort_order: form.educations.length,
    });

    activeTab.value = 'education';
}

function removeEducation(index) {
    form.educations.splice(index, 1);
}

function moveEducation(index, direction) {
    const targetIndex = index + direction;

    if (targetIndex < 0 || targetIndex >= form.educations.length) {
        return;
    }

    const [current] = form.educations.splice(index, 1);
    form.educations.splice(targetIndex, 0, current);
}

function toggleMultiSelect(field, value) {
    const current = new Set(form[field]);

    if (current.has(value)) {
        current.delete(value);
    } else {
        current.add(value);
    }

    form[field] = Array.from(current);
}

function hasSelected(field, value) {
    return form[field].includes(value);
}
</script>

<template>
    <Head title="Tutor Profile" />

    <TutorLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">
                            Tutor Profile
                        </h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Update your personal details, education, and tuition
                            preferences.
                        </p>
                    </div>

                    <Button
                        type="button"
                        variant="outline"
                        @click="addEducation"
                        >Add Education</Button
                    >
                </div>
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
            </div>

            <div
                class="flex flex-wrap gap-2 rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm"
            >
                <Button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    size="sm"
                    :variant="activeTab === tab.key ? 'default' : 'outline'"
                    @click="activeTab = tab.key"
                >
                    {{ tab.label }}
                </Button>
            </div>

            <form class="space-y-6" @submit.prevent="submit">
                <section
                    v-if="activeTab === 'personal'"
                    class="grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm md:grid-cols-2"
                >
                    <div class="grid gap-2">
                        <Label for="name">Full Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Phone</Label>
                        <Input
                            id="phone"
                            v-model="form.phone"
                            type="text"
                            placeholder="01XXXXXXXXX"
                        />
                        <InputError :message="form.errors.phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Gender</Label>
                        <Select v-model="form.gender">
                            <SelectTrigger>
                                <SelectValue placeholder="Select gender" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none"
                                    >Not specified</SelectItem
                                >
                                <SelectItem
                                    v-for="option in genderOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.gender" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="date_of_birth">Date of Birth</Label>
                        <Input
                            id="date_of_birth"
                            v-model="form.date_of_birth"
                            type="date"
                        />
                        <InputError :message="form.errors.date_of_birth" />
                    </div>

                    <div class="grid gap-2 md:col-span-2">
                        <Label for="present_address">Present Address</Label>
                        <textarea
                            id="present_address"
                            v-model="form.present_address"
                            rows="3"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        ></textarea>
                        <InputError :message="form.errors.present_address" />
                    </div>

                    <div class="grid gap-2 md:col-span-2">
                        <Label for="permanent_address">Permanent Address</Label>
                        <textarea
                            id="permanent_address"
                            v-model="form.permanent_address"
                            rows="3"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        ></textarea>
                        <InputError :message="form.errors.permanent_address" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="nid_no">NID Number</Label>
                        <Input id="nid_no" v-model="form.nid_no" type="text" />
                        <InputError :message="form.errors.nid_no" />
                    </div>

                    <div class="grid gap-2 md:col-span-2">
                        <Label for="bio">Bio</Label>
                        <textarea
                            id="bio"
                            v-model="form.bio"
                            rows="4"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        ></textarea>
                        <InputError :message="form.errors.bio" />
                    </div>
                </section>

                <section
                    v-if="activeTab === 'education'"
                    class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Education History</h2>
                        <Button
                            type="button"
                            variant="outline"
                            @click="addEducation"
                            >Add Row</Button
                        >
                    </div>

                    <p
                        v-if="!form.educations.length"
                        class="rounded-md border border-dashed px-4 py-5 text-sm text-muted-foreground"
                    >
                        No education records added yet.
                    </p>

                    <div
                        v-for="(education, index) in form.educations"
                        :key="education.id ?? `new-${index}`"
                        class="grid gap-3 rounded-xl border border-slate-200/80 p-4"
                    >
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold">
                                Education #{{ index + 1 }}
                            </h3>

                            <div class="flex items-center gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    :disabled="index === 0"
                                    @click="moveEducation(index, -1)"
                                >
                                    Up
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    :disabled="
                                        index === form.educations.length - 1
                                    "
                                    @click="moveEducation(index, 1)"
                                >
                                    Down
                                </Button>
                                <Button
                                    type="button"
                                    variant="destructive"
                                    size="sm"
                                    @click="removeEducation(index)"
                                >
                                    Remove
                                </Button>
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label>Degree</Label>
                                <Input
                                    v-model="education.degree"
                                    type="text"
                                    placeholder="BSc in CSE"
                                />
                                <InputError
                                    :message="
                                        form.errors[
                                            `educations.${index}.degree`
                                        ]
                                    "
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label>Institute</Label>
                                <Input
                                    v-model="education.institute"
                                    type="text"
                                    placeholder="University Name"
                                />
                                <InputError
                                    :message="
                                        form.errors[
                                            `educations.${index}.institute`
                                        ]
                                    "
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label>Department</Label>
                                <Input
                                    v-model="education.department"
                                    type="text"
                                />
                                <InputError
                                    :message="
                                        form.errors[
                                            `educations.${index}.department`
                                        ]
                                    "
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label>Graduation Year</Label>
                                <Input
                                    v-model="education.graduation_year"
                                    type="number"
                                    min="1900"
                                    max="2100"
                                />
                                <InputError
                                    :message="
                                        form.errors[
                                            `educations.${index}.graduation_year`
                                        ]
                                    "
                                />
                            </div>

                            <div class="grid gap-2 md:col-span-2">
                                <Label>Result</Label>
                                <Input
                                    v-model="education.result"
                                    type="text"
                                    placeholder="CGPA / Division"
                                />
                                <InputError
                                    :message="
                                        form.errors[
                                            `educations.${index}.result`
                                        ]
                                    "
                                />
                            </div>

                            <label
                                class="inline-flex items-center gap-2 text-sm"
                            >
                                <input
                                    v-model="education.is_current"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border"
                                />
                                Currently studying
                            </label>
                        </div>
                    </div>

                    <InputError :message="form.errors.educations" />
                </section>

                <section
                    v-if="activeTab === 'preferences'"
                    class="space-y-5 rounded-xl border bg-white p-5"
                >
                    <h2 class="text-lg font-semibold">Tuition Preferences</h2>

                    <div class="grid gap-2">
                        <Label>Preferred Tuition Types</Label>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            <label
                                v-for="option in tuitionTypes"
                                :key="`type-${option.id}`"
                                class="inline-flex items-center gap-2 rounded border px-3 py-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :checked="
                                        hasSelected(
                                            'preferred_tuition_types',
                                            option.id,
                                        )
                                    "
                                    @change="
                                        toggleMultiSelect(
                                            'preferred_tuition_types',
                                            option.id,
                                        )
                                    "
                                />
                                {{ option.name }}
                            </label>
                        </div>
                        <InputError
                            :message="form.errors.preferred_tuition_types"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label>Preferred Categories</Label>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            <label
                                v-for="option in categories"
                                :key="`category-${option.id}`"
                                class="inline-flex items-center gap-2 rounded border px-3 py-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :checked="
                                        hasSelected(
                                            'preferred_categories',
                                            option.id,
                                        )
                                    "
                                    @change="
                                        toggleMultiSelect(
                                            'preferred_categories',
                                            option.id,
                                        )
                                    "
                                />
                                {{ option.name }}
                            </label>
                        </div>
                        <InputError
                            :message="form.errors.preferred_categories"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label>Preferred Classes</Label>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            <label
                                v-for="option in filteredClasses"
                                :key="`class-${option.id}`"
                                class="inline-flex items-center gap-2 rounded border px-3 py-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :checked="
                                        hasSelected(
                                            'preferred_classes',
                                            option.id,
                                        )
                                    "
                                    @change="
                                        toggleMultiSelect(
                                            'preferred_classes',
                                            option.id,
                                        )
                                    "
                                />
                                {{ option.name }}
                            </label>
                        </div>
                        <InputError :message="form.errors.preferred_classes" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Preferred Subjects</Label>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            <label
                                v-for="option in filteredSubjects"
                                :key="`subject-${option.id}`"
                                class="inline-flex items-center gap-2 rounded border px-3 py-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :checked="
                                        hasSelected(
                                            'preferred_subjects',
                                            option.id,
                                        )
                                    "
                                    @change="
                                        toggleMultiSelect(
                                            'preferred_subjects',
                                            option.id,
                                        )
                                    "
                                />
                                {{ option.name }}
                            </label>
                        </div>
                        <InputError :message="form.errors.preferred_subjects" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Preferred Locations</Label>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            <label
                                v-for="option in locations"
                                :key="`location-${option.id}`"
                                class="inline-flex items-center gap-2 rounded border px-3 py-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :checked="
                                        hasSelected(
                                            'preferred_locations',
                                            option.id,
                                        )
                                    "
                                    @change="
                                        toggleMultiSelect(
                                            'preferred_locations',
                                            option.id,
                                        )
                                    "
                                />
                                {{ option.name }}
                            </label>
                        </div>
                        <InputError
                            :message="form.errors.preferred_locations"
                        />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="expected_salary_min"
                                >Expected Salary Min</Label
                            >
                            <Input
                                id="expected_salary_min"
                                v-model="form.expected_salary_min"
                                type="number"
                                min="0"
                                step="0.01"
                            />
                            <InputError
                                :message="form.errors.expected_salary_min"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="expected_salary_max"
                                >Expected Salary Max</Label
                            >
                            <Input
                                id="expected_salary_max"
                                v-model="form.expected_salary_max"
                                type="number"
                                min="0"
                                step="0.01"
                            />
                            <InputError
                                :message="form.errors.expected_salary_max"
                            />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label>Available Days</Label>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                            <label
                                v-for="option in dayOptions"
                                :key="`day-${option.value}`"
                                class="inline-flex items-center gap-2 rounded border px-3 py-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :checked="
                                        hasSelected(
                                            'available_days',
                                            option.value,
                                        )
                                    "
                                    @change="
                                        toggleMultiSelect(
                                            'available_days',
                                            option.value,
                                        )
                                    "
                                />
                                {{ option.label }}
                            </label>
                        </div>
                        <InputError :message="form.errors.available_days" />
                    </div>

                    <div class="grid gap-2 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="available_time">Available Time</Label>
                            <Input
                                id="available_time"
                                v-model="form.available_time"
                                type="text"
                                placeholder="e.g. 4 PM - 9 PM"
                            />
                            <InputError :message="form.errors.available_time" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Status</Label>
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active"
                                        >Active</SelectItem
                                    >
                                    <SelectItem value="inactive"
                                        >Inactive</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.status" />
                        </div>
                    </div>
                </section>

                <div class="flex items-center gap-3">
                    <Button type="submit" :disabled="form.processing"
                        >Save Profile</Button
                    >
                    <span
                        v-if="form.processing"
                        class="text-sm text-muted-foreground"
                        >Saving...</span
                    >
                </div>
            </form>
        </div>
    </TutorLayout>
</template>
