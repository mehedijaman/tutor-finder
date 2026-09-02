<script setup lang="ts">
import {
    Atom,
    BookOpen,
    Briefcase,
    Dna,
    Globe,
    GraduationCap,
    Lightbulb,
    Search,
    Star,
    Target,
    Users,
    UsersRound,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        stats?: {
            live_tuitions?: number;
            active_tutors?: number;
            guardians_students?: number;
            rating?: number;
        };
    }>(),
    {
        stats: () => ({
            live_tuitions: 1692,
            active_tutors: 1532,
            guardians_students: 1532,
            rating: 5.0,
        }),
    },
);

const displayLiveTuitions = ref(0);
const displayActiveTutors = ref(0);
const displayGuardiansStudents = ref(0);

const animateCount = (
    target: number,
    durationMs: number,
    onUpdate: (val: number) => void,
) => {
    const startTime = performance.now();
    const step = (currentTime: number) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / durationMs, 1);
        const easedProgress =
            progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
        onUpdate(Math.floor(easedProgress * target));

        if (progress < 1) {
            requestAnimationFrame(step);
        } else {
            onUpdate(target);
        }
    };
    requestAnimationFrame(step);
};

onMounted(() => {
    const targetLive = props.stats?.live_tuitions || 1692;
    const targetTutors = props.stats?.active_tutors || 1532;
    const targetGuardians = props.stats?.guardians_students || 1532;

    animateCount(targetLive, 2000, (v) => (displayLiveTuitions.value = v));
    animateCount(targetTutors, 2000, (v) => (displayActiveTutors.value = v));
    animateCount(
        targetGuardians,
        2000,
        (v) => (displayGuardiansStudents.value = v),
    );
});
</script>

<template>
    <section class="relative overflow-hidden bg-[#0999da] py-10 sm:py-14">
        <!-- Ambient Education Background Doodles Floating Icons -->
        <div
            class="pointer-events-none absolute inset-0 -z-0 opacity-15 select-none"
        >
            <Globe class="absolute top-10 left-[8%] h-12 w-12 text-white" />
            <Lightbulb class="absolute top-4 left-[20%] h-8 w-8 text-white" />
            <Atom class="absolute bottom-6 left-[32%] h-10 w-10 text-white" />
            <Search class="absolute top-8 right-[32%] h-7 w-7 text-white" />
            <Dna class="absolute top-6 right-[12%] h-12 w-12 text-white" />
            <GraduationCap
                class="absolute right-[5%] bottom-6 h-10 w-10 text-white"
            />
            <BookOpen class="absolute bottom-4 left-[55%] h-9 w-9 text-white" />
        </div>

        <div class="relative z-10 mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div
                class="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-4 lg:gap-8"
            >
                <!-- Circle 1: Live Tuitions -->
                <div
                    class="group flex aspect-square h-40 w-40 flex-col items-center justify-center rounded-full bg-white p-3 text-center shadow-xl transition-all duration-300 hover:scale-105 sm:h-52 sm:w-52 sm:p-5 lg:h-56 lg:w-56"
                >
                    <div
                        class="mb-1.5 flex h-11 w-11 items-center justify-center rounded-full text-[#1c2346] transition-transform duration-300 group-hover:scale-110 sm:mb-2 sm:h-14 sm:w-14"
                    >
                        <Briefcase
                            class="h-7 w-7 stroke-[2.2] text-[#1c2346] sm:h-9 sm:w-9"
                        />
                    </div>
                    <span
                        class="text-xs font-black tracking-tight text-[#1c2346] sm:text-base lg:text-lg"
                    >
                        Live Tuitions
                    </span>
                    <span
                        class="mt-0.5 text-2xl font-black text-slate-900 sm:text-3xl lg:text-4xl"
                    >
                        {{ displayLiveTuitions.toLocaleString() }}
                    </span>
                </div>

                <!-- Circle 2: Active Tutors -->
                <div
                    class="group flex aspect-square h-40 w-40 flex-col items-center justify-center rounded-full bg-white p-3 text-center shadow-xl transition-all duration-300 hover:scale-105 sm:h-52 sm:w-52 sm:p-5 lg:h-56 lg:w-56"
                >
                    <div
                        class="relative mb-1.5 flex h-11 w-11 items-center justify-center rounded-full bg-[#38bdf8] text-white shadow-xs transition-transform duration-300 group-hover:scale-110 sm:mb-2 sm:h-14 sm:w-14"
                    >
                        <Users
                            class="h-6 w-6 stroke-[2.2] text-white sm:h-8 sm:w-8"
                        />
                        <span
                            class="absolute -right-0.5 -bottom-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 ring-2 ring-white sm:h-5 sm:w-5"
                        >
                            <Target
                                class="h-2.5 w-2.5 text-white sm:h-3 sm:w-3"
                            />
                        </span>
                    </div>
                    <span
                        class="text-xs font-black tracking-tight text-[#1c2346] sm:text-base lg:text-lg"
                    >
                        Active Tutors
                    </span>
                    <span
                        class="mt-0.5 text-2xl font-black text-slate-900 sm:text-3xl lg:text-4xl"
                    >
                        {{ displayActiveTutors.toLocaleString() }}
                    </span>
                </div>

                <!-- Circle 3: Guardians / Students -->
                <div
                    class="group flex aspect-square h-40 w-40 flex-col items-center justify-center rounded-full bg-white p-3 text-center shadow-xl transition-all duration-300 hover:scale-105 sm:h-52 sm:w-52 sm:p-5 lg:h-56 lg:w-56"
                >
                    <div
                        class="mb-1.5 flex h-11 w-11 items-center justify-center rounded-full bg-[#38bdf8] text-white shadow-xs transition-transform duration-300 group-hover:scale-110 sm:mb-2 sm:h-14 sm:w-14"
                    >
                        <UsersRound
                            class="h-6 w-6 stroke-[2.2] text-white sm:h-8 sm:w-8"
                        />
                    </div>
                    <span
                        class="text-xs leading-tight font-black tracking-tight text-[#1c2346] sm:text-base lg:text-lg"
                    >
                        Guardians /<br class="hidden sm:inline" />
                        Students
                    </span>
                    <span
                        class="mt-0.5 text-2xl font-black text-slate-900 sm:text-3xl lg:text-4xl"
                    >
                        {{ displayGuardiansStudents.toLocaleString() }}
                    </span>
                </div>

                <!-- Circle 4: Rating -->
                <div
                    class="group flex aspect-square h-40 w-40 flex-col items-center justify-center rounded-full bg-white p-3 text-center shadow-xl transition-all duration-300 hover:scale-105 sm:h-52 sm:w-52 sm:p-5 lg:h-56 lg:w-56"
                >
                    <div
                        class="mb-1.5 flex h-11 w-11 items-center justify-center rounded-full bg-[#38bdf8] text-amber-300 shadow-xs transition-transform duration-300 group-hover:scale-110 sm:mb-2 sm:h-14 sm:w-14"
                    >
                        <div class="flex items-center justify-center gap-0.5">
                            <Star
                                class="h-5 w-5 fill-amber-300 text-amber-300 sm:h-7 sm:w-7"
                            />
                        </div>
                    </div>
                    <span
                        class="text-xs font-black tracking-tight text-[#1c2346] sm:text-base lg:text-lg"
                    >
                        Rating
                    </span>
                    <span
                        class="mt-0.5 text-2xl font-black text-slate-900 sm:text-3xl lg:text-4xl"
                    >
                        {{ (props.stats?.rating || 5.0).toFixed(1) }}
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>
