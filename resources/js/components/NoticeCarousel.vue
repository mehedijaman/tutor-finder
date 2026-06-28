<script setup lang="ts">
import Autoplay from 'embla-carousel-autoplay';
import {
    Calendar,
    ChevronLeft,
    ChevronRight,
    Megaphone,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
} from '@/components/ui/carousel';
import type { CarouselApi } from '@/components/ui/carousel';

interface Notice {
    id: number;
    title: string;
    body: string;
    published_at: string;
    expires_at: string | null;
}

const props = defineProps<{
    notices: Notice[];
}>();

const emblaApi = ref<CarouselApi>();
const currentIndex = ref(0);

function setApi(api: CarouselApi) {
    emblaApi.value = api;
    api.on('select', () => {
        currentIndex.value = api.selectedScrollSnap();
    });
}

function scrollPrev() {
    emblaApi.value?.scrollPrev();
}

function scrollNext() {
    emblaApi.value?.scrollNext();
}

const canScrollPrev = computed(() => currentIndex.value > 0);
const canScrollNext = computed(
    () => currentIndex.value < props.notices.length - 1,
);

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function stripHtml(html: string): string {
    const div = document.createElement('div');
    div.innerHTML = html;
    return div.textContent || div.innerText || '';
}
</script>

<template>
    <div
        v-if="notices.length"
        class="relative overflow-hidden rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50 via-orange-50 to-yellow-50 shadow-sm"
    >
        <div
            class="flex items-center justify-between border-b border-amber-200/60 bg-amber-100/50 px-5 py-3"
        >
            <div class="flex items-center gap-2">
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500 text-white shadow-sm"
                >
                    <Megaphone class="h-4 w-4" />
                </div>
                <h2 class="text-sm font-semibold text-amber-900">Notices</h2>
            </div>
            <div v-if="notices.length > 1" class="flex items-center gap-1">
                <Button
                    variant="ghost"
                    size="icon"
                    class="h-7 w-7 text-amber-700 hover:bg-amber-200/50"
                    :disabled="!canScrollPrev"
                    @click="scrollPrev"
                >
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <span class="min-w-[3rem] text-center text-xs text-amber-700">
                    {{ currentIndex + 1 }} / {{ notices.length }}
                </span>
                <Button
                    variant="ghost"
                    size="icon"
                    class="h-7 w-7 text-amber-700 hover:bg-amber-200/50"
                    :disabled="!canScrollNext"
                    @click="scrollNext"
                >
                    <ChevronRight class="h-4 w-4" />
                </Button>
            </div>
        </div>

        <Carousel
            :plugins="[Autoplay({ delay: 5000, stopOnInteraction: true })]"
            class="w-full"
            @init-api="setApi"
        >
            <CarouselContent>
                <CarouselItem v-for="notice in notices" :key="notice.id">
                    <div class="p-5">
                        <h3
                            class="line-clamp-1 text-base font-semibold text-slate-900"
                        >
                            {{ notice.title }}
                        </h3>
                        <p
                            class="mt-2 line-clamp-2 text-sm leading-relaxed text-slate-600"
                        >
                            {{ stripHtml(notice.body) }}
                        </p>
                        <div
                            class="mt-3 flex items-center gap-1.5 text-xs text-amber-700"
                        >
                            <Calendar class="h-3.5 w-3.5" />
                            <span>{{ formatDate(notice.published_at) }}</span>
                            <span
                                v-if="notice.expires_at"
                                class="text-slate-400"
                            >
                                · Expires {{ formatDate(notice.expires_at) }}
                            </span>
                        </div>
                    </div>
                </CarouselItem>
            </CarouselContent>
        </Carousel>

        <div v-if="notices.length > 1" class="flex justify-center gap-1.5 pb-4">
            <button
                v-for="(_, idx) in notices"
                :key="idx"
                class="h-1.5 rounded-full transition-all duration-300"
                :class="[
                    idx === currentIndex
                        ? 'w-6 bg-amber-500'
                        : 'w-1.5 bg-amber-300 hover:bg-amber-400',
                ]"
                @click="emblaApi?.scrollTo(idx)"
            />
        </div>
    </div>
</template>
