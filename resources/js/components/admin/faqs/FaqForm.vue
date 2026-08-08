<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import TiptapEditor from '@/components/admin/blog/TiptapEditor.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
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
        default: '/admin/faqs',
    },
    audienceOptions: {
        type: Array,
        default: () => [],
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
    initial: {
        type: Object,
        default: () => ({
            question: '',
            answer: '<p></p>',
            audience: 'both',
            status: 'active',
            sort_order: 0,
        }),
    },
});

const form = useForm({
    question: props.initial.question ?? '',
    answer: props.initial.answer ?? '<p></p>',
    audience: props.initial.audience ?? 'both',
    status: props.initial.status ?? 'active',
    sort_order: props.initial.sort_order ?? 0,
});

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
                <h2 class="text-lg font-semibold">FAQ Content</h2>

                <div class="grid gap-2">
                    <Label for="faq-question">Question</Label>
                    <Input
                        id="faq-question"
                        v-model="form.question"
                        type="text"
                        required
                    />
                    <InputError :message="form.errors.question" />
                </div>

                <div class="grid gap-2">
                    <Label>Answer</Label>
                    <TiptapEditor
                        v-model="form.answer"
                        placeholder="Write the answer with rich formatting"
                    />
                    <InputError :message="form.errors.answer" />
                </div>
            </section>

            <aside class="space-y-4 sm:col-span-4">
                <section
                    class="grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                >
                    <h2 class="text-lg font-semibold">Settings</h2>

                    <div class="grid gap-2">
                        <Label for="faq-audience">Audience</Label>
                        <select
                            id="faq-audience"
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
                        <Label for="faq-status">Status</Label>
                        <select
                            id="faq-status"
                            v-model="form.status"
                            class="h-10 w-full rounded-md border px-3 text-sm"
                        >
                            <option
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <InputError :message="form.errors.status" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="faq-sort-order">Sort Order</Label>
                        <Input
                            id="faq-sort-order"
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                        />
                        <InputError :message="form.errors.sort_order" />
                    </div>
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
