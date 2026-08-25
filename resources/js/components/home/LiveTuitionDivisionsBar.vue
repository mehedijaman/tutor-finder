<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Wifi } from 'lucide-vue-next';
import { jobs } from '@/routes';

type DivisionItem = {
    name: string;
    count: number;
    slug: string;
};

withDefaults(
    defineProps<{
        divisions?: DivisionItem[];
    }>(),
    {
        divisions: () => [
            { name: 'Dhaka', count: 20, slug: 'dhaka' },
            { name: 'Chottogram', count: 10, slug: 'chottogram' },
            { name: 'Khulna', count: 7, slug: 'khulna' },
            { name: 'Rajshahi', count: 12, slug: 'rajshahi' },
            { name: 'Sylhet', count: 8, slug: 'sylhet' },
            { name: 'Barishal', count: 5, slug: 'barishal' },
            { name: 'Rangpur', count: 6, slug: 'rangpur' },
            { name: 'Mymensingh', count: 4, slug: 'mymensingh' },
            { name: 'Online', count: 15, slug: 'online' },
        ],
    },
);
</script>

<template>
    <section class="w-full bg-slate-50/80 py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                class="flex flex-col items-center gap-4 rounded-3xl border border-slate-200/90 bg-white/90 p-5 shadow-xl shadow-slate-200/40 backdrop-blur-md sm:p-6 lg:flex-row lg:gap-6"
            >
                <!-- Live Tuition Badge with Glowing Radar Pulse -->
                <div
                    class="inline-flex shrink-0 items-center gap-2.5 rounded-2xl bg-[#1b2880] px-5 py-2.5 text-base font-black text-white shadow-md select-none"
                >
                    <div class="relative flex items-center justify-center">
                        <span
                            class="absolute -top-1.5 h-4 w-4 animate-ping rounded-full bg-red-400 opacity-75"
                        ></span>
                        <span
                            class="relative flex h-3.5 w-3.5 animate-bounce items-center justify-center rounded-full bg-red-500 shadow-xs ring-2 ring-red-300"
                        >
                            <Wifi class="h-2.5 w-2.5 text-white" />
                        </span>
                    </div>
                    <span class="tracking-tight text-white">Live Tuition</span>
                </div>

                <!-- Division Filter Pills Container -->
                <div
                    class="flex w-full flex-1 flex-wrap items-center justify-center gap-2.5 sm:gap-3 lg:justify-start"
                >
                    <Link
                        v-for="div in divisions"
                        :key="div.name"
                        :href="
                            div.slug === 'online'
                                ? jobs({ query: { method: 'online' } })
                                : jobs({ query: { division: div.name } })
                        "
                        class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-[#0eb0e6] to-[#0096c7] px-5 py-2 text-xs font-bold text-white shadow-2xs transition-all duration-200 hover:-translate-y-0.5 hover:from-[#0096c7] hover:to-[#0077b6] hover:shadow-md sm:text-sm"
                    >
                        <span>{{ div.name }}</span>
                        <span
                            class="rounded-full bg-white/20 px-2 py-0.5 text-[11px] font-extrabold text-white"
                        >
                            {{ String(div.count).padStart(2, '0') }}
                        </span>
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
