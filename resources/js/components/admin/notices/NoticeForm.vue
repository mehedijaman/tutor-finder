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
                class="grid gap-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm sm:col-span-8 sm:p-6"
            >
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Notice Content</h2>

                <div class="grid gap-2">
                    <Label for="notice-title" class="text-slate-800 dark:text-slate-200">Title</Label>
                    <Input
                        id="notice-title"
                        v-model="form.title"
                        type="text"
                        maxlength="180"
                        required
                        class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label class="text-slate-800 dark:text-slate-200">Body</Label>
                    <TiptapEditor
                        v-model="form.body"
                        placeholder="Write the notice content"
                    />
                    <InputError :message="form.errors.body" />
                </div>
            </section>

            <aside class="space-y-4 sm:col-span-4">
                <section
                    class="grid gap-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm sm:p-6"
                >
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Settings</h2>

                    <div class="grid gap-2">
                        <Label for="notice-audience" class="text-slate-800 dark:text-slate-200">Audience</Label>
                        <select
                            id="notice-audience"
                            v-model="form.audience"
                            class="h-10 w-full rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 text-sm text-slate-900 dark:text-slate-100"
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
                        <Label for="notice-published-at" class="text-slate-800 dark:text-slate-200">Publish Date</Label>
                        <Input
                            id="notice-published-at"
                            v-model="form.published_at"
                            type="datetime-local"
                            class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        />
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Leave empty for immediate publication
                        </p>
                        <InputError :message="form.errors.published_at" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="notice-expires-at" class="text-slate-800 dark:text-slate-200">Expiry Date</Label>
                        <Input
                            id="notice-expires-at"
                            v-model="form.expires_at"
                            type="datetime-local"
                            class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        />
                        <p class="text-xs text-slate-500 dark:text-slate-400">
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
                        <Label for="notice-active" class="cursor-pointer text-slate-800 dark:text-slate-200">
                            Active
                        </Label>
                    </div>
                    <InputError :message="form.errors.is_active" />
                </section>

                <section
                    class="grid gap-3 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm"
                >
                    <Button type="submit" :disabled="form.processing">
                        {{ submitLabel }}
                    </Button>
                    <Link
                        :href="cancelHref"
                        class="inline-flex h-9 items-center justify-center rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 text-sm font-medium text-slate-700 dark:text-slate-200 transition hover:bg-slate-50 dark:hover:bg-slate-700"
                    >
                        Cancel
                    </Link>
                </section>
            </aside>
        </div>
    </Form>
</template>
