<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Loader2,
    MessageSquare,
    RefreshCw,
    ShieldCheck,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { useSiteSettings } from '@/composables/useSiteSettings';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout, register } from '@/routes';
import { resend, store } from '@/routes/otp/verify';

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

const code = ref('');
const resendError = ref<string | null>(null);
const resendTimer = ref(0);
const isResending = ref(false);
const isResent = ref(false);
const isReturning = ref(false);

const RESEND_COOLDOWN_SECONDS = 60;
let timerInterval: ReturnType<typeof setInterval> | null = null;

const canResend = computed(() => resendTimer.value === 0);
const canSubmit = computed(() => code.value.length === 6);
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
        code.value = props.localOtp;
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
        description="Enter the 6-digit OTP sent to your mobile"
    >
        <Head title="Verify OTP" />

        <div class="space-y-6">
            <Alert class="border-muted">
                <ShieldCheck class="size-4" />
                <AlertTitle>Secure verification</AlertTitle>
                <AlertDescription>
                    A one-time code was sent to
                    <span class="font-medium text-foreground">
                        {{ maskedPhone }}
                    </span>
                    for your {{ siteName }} account.
                </AlertDescription>
            </Alert>

            <Alert
                v-if="status"
                class="border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300"
            >
                <AlertDescription>{{ status }}</AlertDescription>
            </Alert>

            <Form
                v-bind="store.form()"
                class="space-y-5"
                reset-on-error
                @error="code = ''"
                #default="{ errors, processing }"
            >
                <input type="hidden" name="code" :value="code" />

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label for="otp-code">Verification code</Label>
                        <span class="text-xs text-muted-foreground">
                            6 digits
                        </span>
                    </div>
                    <div
                        class="flex justify-center rounded-lg border border-input/70 bg-muted/20 p-3"
                    >
                        <InputOTP
                            id="otp-code"
                            v-model="code"
                            :maxlength="6"
                            :disabled="processing"
                            pattern="^[0-9]+$"
                            autofocus
                        >
                            <InputOTPGroup>
                                <InputOTPSlot
                                    v-for="index in 6"
                                    :key="index"
                                    :index="index - 1"
                                    class="h-11 w-11 text-base md:h-12 md:w-12"
                                />
                            </InputOTPGroup>
                        </InputOTP>
                    </div>
                    <InputError :message="errors.code" />
                </div>

                <div v-if="localOtp" class="space-y-2">
                    <Button
                        type="button"
                        variant="outline"
                        class="w-full justify-between border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100"
                        @click="fillLocalOtp"
                    >
                        <span>Use local debug OTP</span>
                        <span class="font-mono">{{ localOtp }}</span>
                    </Button>
                </div>

                <Button
                    type="submit"
                    class="w-full"
                    size="lg"
                    :disabled="processing || !canSubmit"
                >
                    <Loader2
                        v-if="processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    Verify and continue
                </Button>
            </Form>

            <Separator />

            <div class="space-y-3 rounded-lg border bg-muted/20 p-4">
                <p class="flex items-start gap-2 text-sm text-muted-foreground">
                    <MessageSquare class="mt-0.5 size-4" />
                    Didn't receive the code? Request a new one via SMS.
                </p>

                <Button
                    variant="secondary"
                    class="w-full sm:w-auto"
                    :disabled="!canResend || isResending"
                    @click="handleResend"
                >
                    <RefreshCw
                        :class="['mr-2 h-4 w-4', isResending && 'animate-spin']"
                    />
                    {{ resendLabel }}
                </Button>

                <p v-if="isResent" class="text-sm text-emerald-600">
                    A new code has been sent. Please check your phone.
                </p>
                <InputError :message="resendError ?? undefined" />
            </div>

            <div class="text-center text-sm text-muted-foreground">
                <button
                    type="button"
                    class="inline-flex items-center gap-1 underline decoration-neutral-300 underline-offset-4 transition-colors hover:decoration-current dark:decoration-neutral-600"
                    :disabled="isReturning"
                    @click="handleBack"
                >
                    <ArrowLeft
                        :class="['h-4 w-4', isReturning && 'animate-pulse']"
                    />
                    Back
                </button>
            </div>
        </div>
    </AuthLayout>
</template>
