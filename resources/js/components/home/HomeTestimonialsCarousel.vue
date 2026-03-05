<script setup lang="ts">
import { Quote, Star } from 'lucide-vue-next';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/components/ui/carousel';

type HomepageTestimonial = {
    id: number;
    name: string;
    role: string | null;
    avatar_url: string | null;
    content: string;
    rating: number;
};

defineProps<{
    testimonials: HomepageTestimonial[];
}>();

function avatarInitials(name: string): string {
    return name
        .split(/\s+/)
        .filter((part) => part.trim() !== '')
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
}
</script>

<template>
    <div v-if="testimonials.length" class="relative mt-12">
        <Carousel class="w-full">
            <CarouselContent class="-ml-4">
                <CarouselItem
                    v-for="testimonial in testimonials"
                    :key="testimonial.id"
                    class="pl-4 md:basis-1/2 lg:basis-1/3"
                >
                    <article
                        class="flex h-full flex-col rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1 text-amber-500">
                                <Star
                                    v-for="index in 5"
                                    :key="index"
                                    class="h-4 w-4"
                                    :class="
                                        index <= testimonial.rating
                                            ? 'fill-current'
                                            : 'fill-none text-slate-300'
                                    "
                                />
                            </div>
                            <Quote class="h-5 w-5 text-slate-300" />
                        </div>

                        <p
                            class="mt-4 flex-1 text-sm leading-relaxed text-slate-600"
                        >
                            "{{ testimonial.content }}"
                        </p>

                        <div class="mt-6 flex items-center gap-3">
                            <Avatar class="h-11 w-11">
                                <AvatarImage
                                    v-if="testimonial.avatar_url"
                                    :src="testimonial.avatar_url"
                                    :alt="testimonial.name"
                                />
                                <AvatarFallback
                                    class="bg-slate-200 text-xs font-semibold text-slate-700"
                                >
                                    {{ avatarInitials(testimonial.name) }}
                                </AvatarFallback>
                            </Avatar>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ testimonial.name }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ testimonial.role || 'Guardian' }}
                                </p>
                            </div>
                        </div>
                    </article>
                </CarouselItem>
            </CarouselContent>

            <CarouselPrevious
                v-if="testimonials.length > 1"
                class="left-2 h-9 w-9 rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-50"
            />
            <CarouselNext
                v-if="testimonials.length > 1"
                class="right-2 h-9 w-9 rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-50"
            />
        </Carousel>
    </div>
</template>
