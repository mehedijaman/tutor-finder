<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import TiptapEditor from '@/components/admin/blog/TiptapEditor.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps({
    action: {
        type: String,
        required: true,
    },
    method: {
        type: String,
        default: 'post',
    },
    submitLabel: {
        type: String,
        required: true,
    },
    cancelHref: {
        type: String,
        default: '/admin/notices',
    },
    audienceOptions: {
        type: Array,
        default: () => [],
    },
    initial: {
        type: Object,
        default: () => ({
            title: '',
            body: '<p></p>',
            audience: 'both',
            expires_at: '',
            published_at: '',
            is_active: true,
        }),
    },
});

const form = useForm({
    title: props.initial.title ?? '',
    body: props.initial.body ?? '<p></p>',
    audience: props.initial.audience ?? 'both',
    expires_at: formatDateForInput(props.initial.expires_at),
    published_at: formatDateForInput(props.initial.published_at),
    is_active: props.initial.is_active ?? true,
});

function formatDateForInput(date: string | null | undefined): string {
    if (!date) {
        return '';
    }

    const d = new Date(date);

    if (isNaN(d.getTime())) {
        return '';
    }

    return d.toISOString().slice(0, 16);
}

function submit() {
    if (props.method.toLowerCase() === 'put') {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(props.action, {
            preserveScroll: true,
        });

        return;
    }

    form.post(props.action, {
        preserveScroll: true,
    });
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-6 sm:grid-cols-12">
            <section
                class="grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:col-span-8 sm:p-6"
            >
                <h2 class="text-lg font-semibold">Notice Content</h2>

                <div class="grid gap-2">
                    <Label for="notice-title">Title</Label>
                    <Input
                        id="notice-title"
                        v-model="form.title"
                        type="text"
                        maxlength="180"
                        required
                    />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label>Body</Label>
                    <TiptapEditor
                        v-model="form.body"
                        placeholder="Write the notice content"
                    />
                    <InputError :message="form.errors.body" />
                </div>
            </section>

            <aside class="space-y-4 sm:col-span-4">
                <section
                    class="grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                >
                    <h2 class="text-lg font-semibold">Settings</h2>

                    <div class="grid gap-2">
                        <Label for="notice-audience">Audience</Label>
                        <select
                            id="notice-audience"
                            v-model="form.audience"
                            class="h-10 w-full rounded-md border px-3 text-sm"
                        >
                            <option
                                v-for="option in audienceOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <InputError :message="form.errors.audience" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="notice-published-at">Publish Date</Label>
                        <Input
                            id="notice-published-at"
                            v-model="form.published_at"
                            type="datetime-local"
                        />
                        <p class="text-xs text-slate-500">
                            Leave empty for immediate publication
                        </p>
                        <InputError :message="form.errors.published_at" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="notice-expires-at">Expiry Date</Label>
                        <Input
                            id="notice-expires-at"
                            v-model="form.expires_at"
                            type="datetime-local"
                        />
                        <p class="text-xs text-slate-500">
                            Leave empty for no expiration
                        </p>
                        <InputError :message="form.errors.expires_at" />
                    </div>

                    <div class="flex items-center gap-2">
                        <Checkbox
                            id="notice-active"
                            :checked="form.is_active"
                            @update:checked="form.is_active = $event"
                        />
                        <Label for="notice-active" class="cursor-pointer">
                            Active
                        </Label>
                    </div>
                    <InputError :message="form.errors.is_active" />
                </section>

                <section
                    class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
                >
                    <Button type="submit" :disabled="form.processing">
                        {{ submitLabel }}
                    </Button>
                    <Link
                        :href="cancelHref"
                        class="inline-flex h-9 items-center justify-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Cancel
                    </Link>
                </section>
            </aside>
        </div>
    </form>
</template>
