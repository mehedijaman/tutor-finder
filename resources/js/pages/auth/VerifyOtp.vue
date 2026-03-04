<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { useSiteSettings } from '@/composables/useSiteSettings';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout, register } from '@/routes';
import { resend, store as verifyRoute } from '@/routes/otp/verify'; // Renamed to avoid confusion with form store

type ResendErrorBag = {
    code?: string;
    resend?: string;
};

const props = defineProps<{
    phone: string;
    status: string | null;
    localOtp: string | null;
}>();

const { siteName } = useSiteSettings();

// Use Inertia form helper
const store = useForm({
    code: '',
});

const resendError = ref<string | null>(null);
const resendTimer = ref(0);
const isResending = ref(false);
const isResent = ref(false);
const isReturning = ref(false);

const RESEND_COOLDOWN_SECONDS = 60;
let timerInterval: ReturnType<typeof setInterval> | null = null;

const canResend = computed(() => resendTimer.value === 0);
const canSubmit = computed(() => store.code.length === 6);
const maskedPhone = computed(() => maskPhone(props.phone));
const resendLabel = computed(() => {
    if (isResending.value) {
        return 'Sending...';
    }

    if (!canResend.value) {
        return `Resend in ${resendTimer.value}s`;
    }

    return 'Resend code';
});

function startResendTimer(): void {
    if (timerInterval) {
        clearInterval(timerInterval);
    }

    resendTimer.value = RESEND_COOLDOWN_SECONDS;
    timerInterval = setInterval(() => {
        resendTimer.value = Math.max(0, resendTimer.value - 1);

        if (resendTimer.value === 0 && timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    }, 1000);
}

function handleResend(): void {
    if (!canResend.value || isResending.value) {
        return;
    }

    resendError.value = null;
    isResending.value = true;

    router.post(
        resend.url(),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                isResent.value = true;
                startResendTimer();
            },
            onError: (errors: ResendErrorBag) => {
                resendError.value =
                    errors.resend ??
                    errors.code ??
                    'Unable to resend the verification code right now.';
            },
            onFinish: () => {
                isResending.value = false;
            },
        },
    );
}

function handleBack(): void {
    if (isReturning.value) {
        return;
    }

    isReturning.value = true;

    router.post(
        logout.url(),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(register.url());
            },
            onFinish: () => {
                isReturning.value = false;
            },
        },
    );
}

function fillLocalOtp(): void {
    if (props.localOtp) {
        store.code = props.localOtp;
    }
}

function maskPhone(phone: string): string {
    if (!phone) {
        return '';
    }

    if (phone.length <= 4) {
        return phone;
    }

    return `****${phone.slice(-4)}`;
}

onMounted(() => {
    startResendTimer();
});

onUnmounted(() => {
    if (timerInterval) {
        clearInterval(timerInterval);
    }
});
</script>

<template>
    <AuthLayout
        title="Verify your phone number"
        :description="`We've sent a 6-digit verification code to ${maskedPhone}`"
    >
        <Head title="Verify OTP" />

        <div class="grid gap-6">
            <div
                v-if="status"
                class="rounded-md bg-emerald-50 p-3 text-sm font-medium text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400"
            >
                {{ status }}
            </div>

            <form
                @submit.prevent="
                    store.post(verifyRoute.url(), { preserveScroll: true })
                "
                class="grid gap-6"
            >
                <div class="flex flex-col gap-4">
                    <div class="flex justify-center">
                        <InputOTP
                            v-model="store.code"
                            :maxlength="6"
                            :disabled="store.processing"
                            pattern="^[0-9]+$"
                            autofocus
                        >
                            <InputOTPGroup>
                                <InputOTPSlot
                                    v-for="i in 6"
                                    :key="i"
                                    :index="i - 1"
                                    class="h-12 w-10 text-lg md:h-14 md:w-12"
                                />
                            </InputOTPGroup>
                        </InputOTP>
                    </div>
                    <div
                        v-if="store.errors.code"
                        class="text-center text-sm font-medium text-red-500"
                    >
                        {{ store.errors.code }}
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <Button
                        type="submit"
                        class="w-full"
                        size="lg"
                        :disabled="store.processing || store.code?.length !== 6"
                    >
                        <Loader2
                            v-if="store.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Verify Account
                    </Button>

                    <div class="text-center text-sm">
                        <span class="text-slate-500">Didn't receive code?</span>
                        <div class="mt-1">
                            <button
                                v-if="canResend"
                                type="button"
                                class="font-medium text-blue-600 hover:text-blue-500 hover:underline disabled:opacity-50"
                                @click="handleResend"
                                :disabled="isResending"
                            >
                                <span
                                    v-if="isResending"
                                    class="flex items-center gap-1"
                                >
                                    <Loader2 class="h-3 w-3 animate-spin" />
                                    Sending...
                                </span>
                                <span v-else>Resend Code</span>
                            </button>
                            <span v-else class="font-medium text-slate-400">
                                Resend in {{ resendTimer }}s
                            </span>
                        </div>
                    </div>
                </div>
            </form>

            <div
                v-if="localOtp"
                class="mt-2 rounded-lg border border-amber-200 bg-amber-50 p-3"
            >
                <div
                    class="flex items-center justify-between text-xs text-amber-800"
                >
                    <span class="font-semibold">Development Mode</span>
                    <button
                        type="button"
                        class="font-mono font-bold hover:underline"
                        @click="store.code = localOtp"
                    >
                        Use OTP: {{ localOtp }}
                    </button>
                </div>
            </div>

            <div class="mt-2 text-center">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 text-sm text-slate-500 transition-colors hover:text-slate-800"
                    @click="handleBack"
                    :disabled="isReturning"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Change phone number
                </button>
            </div>
        </div>
    </AuthLayout>
</template>
