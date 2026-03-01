<script setup>
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';

defineProps({
    phone: {
        type: String,
        default: '',
    },
    status: {
        type: String,
        default: null,
    },
    localOtp: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <AuthLayout
        title="Verify your phone"
        description="Enter the OTP code sent to your phone number"
    >
        <Head title="Verify OTP" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div class="mb-4 text-sm text-muted-foreground">
            Verification number: <span class="font-medium text-foreground">{{ phone }}</span>
        </div>

        <Form action="/verify-otp" method="post" class="space-y-4" #default="{ errors, processing }">
            <div class="grid gap-2">
                <Label for="code">Verification code</Label>
                <Input
                    id="code"
                    name="code"
                    type="text"
                    required
                    autofocus
                    maxlength="6"
                    placeholder="Enter 6-digit code"
                />
                <InputError :message="errors.code" />
                <p v-if="localOtp" class="text-xs font-medium text-amber-600">
                    Local debug OTP: {{ localOtp }}
                </p>
            </div>

            <Button type="submit" class="w-full" :disabled="processing">
                Verify and continue
            </Button>
        </Form>
    </AuthLayout>
</template>
