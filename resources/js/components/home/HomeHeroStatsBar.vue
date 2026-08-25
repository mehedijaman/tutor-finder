<script setup lang="ts">
import { Briefcase, Star, Users, UsersRound } from 'lucide-vue-next';
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
    <section
        class="w-full bg-gradient-to-r from-[#0077b6] via-[#0eb0e6] to-[#0096c7] py-8 shadow-inner sm:py-12"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                class="grid grid-cols-2 gap-3 sm:grid-cols-2 md:gap-6 lg:grid-cols-4"
            >
                <!-- Card 1: Live Tuitions -->
                <div
                    class="group flex flex-col items-center justify-center rounded-2xl border border-white/80 bg-white/95 px-3.5 py-5 shadow-xl ring-1 shadow-cyan-950/15 ring-cyan-500/10 backdrop-blur-md transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl sm:rounded-full sm:px-5 sm:py-6"
                >
                    <div
                        class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-[#1b2880] text-white shadow-md transition-transform duration-300 group-hover:scale-110 sm:h-13 sm:w-13"
                    >
                        <Briefcase class="h-5 w-5 sm:h-6 sm:w-6" />
                    </div>
                    <span
                        class="text-center text-[11px] font-extrabold tracking-wider text-slate-700 uppercase sm:text-xs"
                    >
                        Live Tuitions
                    </span>
                    <span
                        class="mt-0.5 text-xl font-black text-slate-900 sm:text-3xl"
                    >
                        {{ displayLiveTuitions.toLocaleString() }}
                    </span>
                </div>

                <!-- Card 2: Active Tutors -->
                <div
                    class="group flex flex-col items-center justify-center rounded-2xl border border-white/80 bg-white/95 px-3.5 py-5 shadow-xl ring-1 shadow-cyan-950/15 ring-cyan-500/10 backdrop-blur-md transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl sm:rounded-full sm:px-5 sm:py-6"
                >
                    <div
                        class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-[#0eb0e6] text-white shadow-md transition-transform duration-300 group-hover:scale-110 sm:h-13 sm:w-13"
                    >
                        <Users class="h-5 w-5 sm:h-6 sm:w-6" />
                    </div>
                    <span
                        class="text-center text-[11px] font-extrabold tracking-wider text-slate-700 uppercase sm:text-xs"
                    >
                        Active Tutors
                    </span>
                    <span
                        class="mt-0.5 text-xl font-black text-slate-900 sm:text-3xl"
                    >
                        {{ displayActiveTutors.toLocaleString() }}
                    </span>
                </div>

                <!-- Card 3: Guardians / Students -->
                <div
                    class="group flex flex-col items-center justify-center rounded-2xl border border-white/80 bg-white/95 px-3.5 py-5 shadow-xl ring-1 shadow-cyan-950/15 ring-cyan-500/10 backdrop-blur-md transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl sm:rounded-full sm:px-5 sm:py-6"
                >
                    <div
                        class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-[#1b2880] text-white shadow-md transition-transform duration-300 group-hover:scale-110 sm:h-13 sm:w-13"
                    >
                        <UsersRound class="h-5 w-5 sm:h-6 sm:w-6" />
                    </div>
                    <span
                        class="text-center text-[11px] font-extrabold tracking-wider text-slate-700 uppercase sm:text-xs"
                    >
                        Guardians / Students
                    </span>
                    <span
                        class="mt-0.5 text-xl font-black text-slate-900 sm:text-3xl"
                    >
                        {{ displayGuardiansStudents.toLocaleString() }}
                    </span>
                </div>

                <!-- Card 4: Rating -->
                <div
                    class="group flex flex-col items-center justify-center rounded-2xl border border-white/80 bg-white/95 px-3.5 py-5 shadow-xl ring-1 shadow-cyan-950/15 ring-cyan-500/10 backdrop-blur-md transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl sm:rounded-full sm:px-5 sm:py-6"
                >
                    <div
                        class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-[#0eb0e6] text-amber-300 shadow-md transition-transform duration-300 group-hover:scale-110 sm:h-13 sm:w-13"
                    >
                        <Star class="h-5 w-5 fill-amber-300 sm:h-6 sm:w-6" />
                    </div>
                    <span
                        class="text-center text-[11px] font-extrabold tracking-wider text-slate-700 uppercase sm:text-xs"
                    >
                        Rating
                    </span>
                    <span
                        class="mt-0.5 text-xl font-black text-slate-900 sm:text-3xl"
                    >
                        {{ (props.stats?.rating || 5.0).toFixed(1) }}
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>
