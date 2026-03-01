<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

const page = usePage();

const isImpersonating = computed<boolean>(() => Boolean(page.props.auth?.impersonation?.is_impersonating));
</script>

<template>
    <div
        v-if="isImpersonating"
        class="border-b border-amber-200 bg-amber-50/80 px-4 py-2 text-amber-900 dark:border-amber-700/40 dark:bg-amber-900/20 dark:text-amber-100"
    >
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-sm">
                <Badge
                    variant="outline"
                    class="border-amber-300 bg-amber-100/80 text-amber-900 dark:border-amber-500/50 dark:bg-amber-800/30 dark:text-amber-100"
                >
                    Impersonating
                </Badge>
                <span>You are viewing this account as another user.</span>
            </div>

            <Button
                as-child
                size="sm"
                variant="outline"
                class="border-amber-300 bg-white text-amber-900 hover:bg-amber-100 dark:border-amber-500/50 dark:bg-amber-900/30 dark:text-amber-100 dark:hover:bg-amber-800/30"
            >
                <Link href="/impersonation/leave" method="post" as="button">
                    Leave Impersonation
                </Link>
            </Button>
        </div>
    </div>
</template>
