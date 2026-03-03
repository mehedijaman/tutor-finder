<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
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
import GuardianLayout from '@/layouts/GuardianLayout.vue';

const props = defineProps({
    profile: {
        type: Object,
        required: true,
    },
});

const breadcrumbs = [{ title: 'Guardian Profile', href: '/guardian/profile' }];

const activeTab = ref('personal');

const form = useForm({
    name: props.profile.name ?? '',
    phone: props.profile.phone ?? '',
    phone_alt: props.profile.phone_alt ?? '',
    guardian_name: props.profile.guardian_name ?? '',
    address: props.profile.address ?? '',
    occupation: props.profile.occupation ?? '',
    notes: props.profile.notes ?? '',
    status: props.profile.status ?? 'active',
});

function submit() {
    form.put('/guardian/profile', {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Guardian Profile" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-2xl font-semibold">Guardian Profile</h1>
                <p class="text-sm text-muted-foreground">
                    Keep your personal and contact information up to date.
                </p>
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
            </div>

            <div class="flex flex-wrap gap-2 rounded-xl border bg-white p-3">
                <Button
                    type="button"
                    size="sm"
                    :variant="activeTab === 'personal' ? 'default' : 'outline'"
                    @click="activeTab = 'personal'"
                >
                    Personal
                </Button>
                <Button
                    type="button"
                    size="sm"
                    :variant="activeTab === 'contact' ? 'default' : 'outline'"
                    @click="activeTab = 'contact'"
                >
                    Contact
                </Button>
            </div>

            <form class="space-y-6" @submit.prevent="submit">
                <section
                    v-if="activeTab === 'personal'"
                    class="grid gap-4 rounded-xl border bg-white p-5 md:grid-cols-2"
                >
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
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
                        <Label for="guardian_name">Guardian Name</Label>
                        <Input
                            id="guardian_name"
                            v-model="form.guardian_name"
                            type="text"
                        />
                        <InputError :message="form.errors.guardian_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Status</Label>
                        <Select v-model="form.status">
                            <SelectTrigger>
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive"
                                    >Inactive</SelectItem
                                >
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.status" />
                    </div>

                    <div class="grid gap-2 md:col-span-2">
                        <Label for="occupation">Occupation</Label>
                        <Input
                            id="occupation"
                            v-model="form.occupation"
                            type="text"
                        />
                        <InputError :message="form.errors.occupation" />
                    </div>
                </section>

                <section
                    v-if="activeTab === 'contact'"
                    class="grid gap-4 rounded-xl border bg-white p-5 md:grid-cols-2"
                >
                    <div class="grid gap-2">
                        <Label for="phone_alt">Alternative Phone</Label>
                        <Input
                            id="phone_alt"
                            v-model="form.phone_alt"
                            type="text"
                            placeholder="Optional"
                        />
                        <InputError :message="form.errors.phone_alt" />
                    </div>

                    <div class="grid gap-2 md:col-span-2">
                        <Label for="address">Address</Label>
                        <textarea
                            id="address"
                            v-model="form.address"
                            rows="4"
                            class="rounded-md border px-3 py-2 text-sm"
                            placeholder="House, road, area"
                        ></textarea>
                        <InputError :message="form.errors.address" />
                    </div>

                    <div class="grid gap-2 md:col-span-2">
                        <Label for="notes">Notes</Label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="4"
                            class="rounded-md border px-3 py-2 text-sm"
                            placeholder="Additional information"
                        ></textarea>
                        <InputError :message="form.errors.notes" />
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
    </GuardianLayout>
</template>
