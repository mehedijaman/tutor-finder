<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useSiteSettings } from '@/composables/useSiteSettings';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { sanitizeHtml } from '@/lib/utils';

const props = defineProps<{
    page: {
        title: string;
        slug: string;
        content: string | null;
        meta_title: string | null;
        meta_description: string | null;
        featured_image_url: string | null;
        updated_at: string;
    };
}>();

const { siteName } = useSiteSettings();

const sanitizedContent = computed(() =>
    props.page.content ? sanitizeHtml(props.page.content) : null,
);

const formattedDate = new Date(props.page.updated_at).toLocaleDateString(
    'en-US',
    {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    },
);
</script>

<template>
    <Head :title="page.meta_title || page.title">
        <meta
            v-if="page.meta_description"
            name="description"
            :content="page.meta_description"
        />
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <PublicLayout>
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-8">
            <div
                class="rounded-3xl bg-gradient-to-r from-blue-600 to-sky-500 p-8 text-white shadow-sm md:p-10"
            >
                <p class="text-sm font-semibold text-white/90">
                    {{ siteName }}
                </p>
                <h1 class="mt-2 text-3xl font-extrabold md:text-4xl">
                    {{ page.title }}
                </h1>
                <p class="mt-4 text-sm text-white/80">
                    Last updated: {{ formattedDate }}
                </p>
            </div>
        </section>

        <section
            v-if="page.featured_image_url"
            class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-8"
        >
            <img
                :src="page.featured_image_url"
                :alt="page.title"
                class="h-auto w-full rounded-2xl object-cover shadow-sm"
            />
        </section>

        <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-16">
            <div
                class="rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_10px_30px_rgba(2,32,71,0.06)] md:p-10"
            >
                <div
                    v-if="sanitizedContent"
                    class="prose prose-slate prose-h2:mt-8 prose-h2:mb-3 prose-h3:mt-6 prose-h3:mb-2 max-w-none"
                    v-html="sanitizedContent"
                />
                <div v-else class="py-12 text-center text-slate-500">
                    <p>This page has no content yet.</p>
                </div>
            </div>
        </main>
    </PublicLayout>
</template>
