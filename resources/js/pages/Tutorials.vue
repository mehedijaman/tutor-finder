<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { useSiteSettings } from '@/composables/useSiteSettings';
import PublicLayout from '@/layouts/PublicLayout.vue';

type Tutorial = {
    id: number;
    title: string;
    slug: string;
    video_url: string;
    audience: string;
    thumbnail_url: string | null;
};

type AudienceOption = {
    value: string;
    label: string;
};

const props = defineProps<{
    tutorials: Tutorial[];
    audienceOptions: AudienceOption[];
}>();

const { siteName } = useSiteSettings();

const activeFilter = ref('all');
const modalOpen = ref(false);
const activeVideo = ref<Tutorial | null>(null);

const filteredTutorials = computed(() => {
    if (activeFilter.value === 'all') {
        return props.tutorials;
    }
    return props.tutorials.filter(
        (t) => t.audience === activeFilter.value || t.audience === 'all',
    );
});

const filterTabs = computed(() => {
    return [
        { value: 'all', label: 'All' },
        ...props.audienceOptions.filter((o) => o.value !== 'all'),
    ];
});

/**
 * Convert a video URL to an embeddable URL for iframe playback.
 */
function getEmbedUrl(url: string): string {
    const ytMatch = url.match(
        /(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/,
    );
    if (ytMatch) {
        return `https://www.youtube.com/embed/${ytMatch[1]}?autoplay=1&rel=0`;
    }
    const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
    if (vimeoMatch) {
        return `https://player.vimeo.com/video/${vimeoMatch[1]}?autoplay=1`;
    }
    return url;
}

function openVideo(tutorial: Tutorial): void {
    activeVideo.value = tutorial;
    modalOpen.value = true;
}

function onModalChange(open: boolean): void {
    modalOpen.value = open;
    if (!open) {
        setTimeout(() => {
            activeVideo.value = null;
        }, 200);
    }
}

/**
 * Generate a YouTube thumbnail URL from a video URL.
 */
function getYouTubeThumbnail(url: string): string | null {
    const match = url.match(
        /(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/,
    );
    if (match) {
        return `https://img.youtube.com/vi/${match[1]}/mqdefault.jpg`;
    }
    return null;
}

function getThumbnailUrl(tutorial: Tutorial): string | null {
    if (tutorial.thumbnail_url) {
        return tutorial.thumbnail_url;
    }
    return getYouTubeThumbnail(tutorial.video_url);
}
</script>

<template>
    <Head title="Tutorials">
        <meta
            name="description"
            :content="`Video tutorials and guides for ${siteName} - Learn how to use our platform effectively.`"
        />
    </Head>

    <PublicLayout>
        <!-- Hero -->
        <section class="mx-auto max-w-7xl px-4 pb-8">
            <div
                class="rounded-3xl bg-gradient-to-r from-blue-600 to-sky-500 p-8 text-white shadow-sm md:p-10"
            >
                <p class="text-sm font-semibold text-white/90">
                    {{ siteName }}
                </p>
                <h1 class="mt-2 text-3xl font-extrabold md:text-4xl">
                    Tutorials
                </h1>
                <p class="mt-4 text-sm text-white/80">
                    Step-by-step video guides to help you get the most out of
                    our platform.
                </p>
            </div>
        </section>

        <!-- Filters & Content -->
        <main class="mx-auto max-w-7xl px-4 pb-16">
            <!-- Filter Tabs -->
            <div class="mb-8 flex flex-wrap gap-2">
                <button
                    v-for="tab in filterTabs"
                    :key="tab.value"
                    type="button"
                    class="rounded-lg border px-4 py-2 text-sm font-medium transition-colors"
                    :class="
                        activeFilter === tab.value
                            ? 'border-blue-600 bg-blue-600 text-white shadow-sm'
                            : 'border-slate-200 bg-white text-slate-700 hover:border-blue-300 hover:bg-blue-50'
                    "
                    @click="activeFilter = tab.value"
                >
                    {{ tab.label }}
                </button>
            </div>

            <!-- Tutorial Cards Grid -->
            <div
                v-if="filteredTutorials.length > 0"
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <div
                    v-for="tutorial in filteredTutorials"
                    :key="tutorial.id"
                    class="group cursor-pointer overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-shadow hover:shadow-md"
                    @click="openVideo(tutorial)"
                >
                    <!-- Thumbnail -->
                    <div
                        class="relative aspect-video w-full overflow-hidden bg-slate-100"
                    >
                        <img
                            v-if="getThumbnailUrl(tutorial)"
                            :src="getThumbnailUrl(tutorial)!"
                            :alt="tutorial.title"
                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center bg-slate-200"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-12 w-12 text-slate-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <!-- Play overlay -->
                        <div
                            class="absolute inset-0 flex items-center justify-center bg-black/0 transition-colors group-hover:bg-black/20"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-white/90 opacity-0 shadow-lg transition-opacity group-hover:opacity-100"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="ml-0.5 h-5 w-5 text-blue-600"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8.132v3.736a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664l-3.197-2.736z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <h3
                            class="line-clamp-2 text-sm font-semibold text-slate-900"
                        >
                            {{ tutorial.title }}
                        </h3>
                        <button
                            type="button"
                            class="mt-3 inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white transition-colors hover:bg-blue-700"
                            @click.stop="openVideo(tutorial)"
                        >
                            Watch Now
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="rounded-2xl border border-slate-200 bg-white py-16 text-center"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="mx-auto h-12 w-12 text-slate-300"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                    />
                </svg>
                <p class="mt-4 text-sm text-slate-500">
                    No tutorials available for this category yet.
                </p>
            </div>
        </main>

        <!-- Video Modal (shadcn Dialog) -->
        <Dialog :open="modalOpen" @update:open="onModalChange">
            <DialogContent
                class="max-w-4xl gap-0 overflow-hidden p-0"
                :show-close-button="true"
            >
                <div class="border-b border-slate-200 px-6 py-4">
                    <DialogTitle class="text-lg font-semibold">
                        {{ activeVideo?.title }}
                    </DialogTitle>
                </div>
                <div v-if="activeVideo" class="aspect-video w-full bg-black">
                    <iframe
                        :src="getEmbedUrl(activeVideo.video_url)"
                        class="h-full w-full"
                        frameborder="0"
                        allow="
                            accelerometer;
                            autoplay;
                            clipboard-write;
                            encrypted-media;
                            gyroscope;
                            picture-in-picture;
                            web-share;
                        "
                        allowfullscreen
                    />
                </div>
            </DialogContent>
        </Dialog>
    </PublicLayout>
</template>
