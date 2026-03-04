<script setup lang="ts">
import { reactive, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    userName: {
        type: String,
        default: '',
    },
    processing: {
        type: Boolean,
        default: false,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['update:open', 'submit', 'cancel']);

const form = reactive({
    password: '',
    password_confirmation: '',
});

watch(
    () => props.open,
    (open) => {
        if (!open) {
            form.password = '';
            form.password_confirmation = '';
        }
    },
);

function closeDialog() {
    emit('cancel');
    emit('update:open', false);
}

function handleOpenChange(value) {
    emit('update:open', value);

    if (!value) {
        emit('cancel');
    }
}

function submit() {
    emit('submit', {
        password: form.password,
        password_confirmation: form.password_confirmation,
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="handleOpenChange">
        <DialogContent>
            <DialogHeader class="space-y-2">
                <DialogTitle>Reset Password</DialogTitle>
                <DialogDescription>
                    Set a new password for {{ userName || 'this user' }}.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <div class="grid gap-2">
                    <Label for="reset-password">New password</Label>
                    <Input
                        id="reset-password"
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="reset-password-confirmation"
                        >Confirm password</Label
                    >
                    <Input
                        id="reset-password-confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>
            </div>

            <DialogFooter class="gap-2">
                <Button
                    type="button"
                    variant="outline"
                    :disabled="processing"
                    @click="closeDialog"
                >
                    Cancel
                </Button>
                <Button type="button" :disabled="processing" @click="submit">
                    Reset Password
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
