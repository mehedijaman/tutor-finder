<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
</script>

<template>
    <AuthLayout title="Admin login" description="Sign in to the admin panel">
        <Head title="Admin Login" />

        <Form
            action="/admin/login"
            method="post"
            class="space-y-4"
            #default="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    name="email"
                    type="email"
                    required
                    autofocus
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <Input id="password" name="password" type="password" required />
                <InputError :message="errors.password" />
            </div>

            <Label for="remember" class="flex items-center gap-3 text-sm">
                <Checkbox id="remember" name="remember" />
                <span>Remember me</span>
            </Label>

            <Button type="submit" class="w-full" :disabled="processing"
                >Login to Admin</Button
            >
        </Form>
    </AuthLayout>
</template>
