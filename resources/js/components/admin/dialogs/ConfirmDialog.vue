<script setup>
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Are you sure?',
    },
    description: {
        type: String,
        default: '',
    },
    confirmLabel: {
        type: String,
        default: 'Confirm',
    },
    cancelLabel: {
        type: String,
        default: 'Cancel',
    },
    processing: {
        type: Boolean,
        default: false,
    },
    destructive: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:open', 'confirm', 'cancel']);

function handleOpenChange(value) {
    emit('update:open', value);

    if (!value) {
        emit('cancel');
    }
}

function confirm() {
    emit('confirm');
}

function cancel() {
    emit('cancel');
    emit('update:open', false);
}
</script>

<template>
    <Dialog :open="open" @update:open="handleOpenChange">
        <DialogContent>
            <DialogHeader class="space-y-2">
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription v-if="description">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="gap-2">
                <Button
                    type="button"
                    variant="outline"
                    :disabled="processing"
                    @click="cancel"
                >
                    {{ cancelLabel }}
                </Button>

                <Button
                    type="button"
                    :variant="destructive ? 'destructive' : 'default'"
                    :disabled="processing"
                    @click="confirm"
                >
                    {{ confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
