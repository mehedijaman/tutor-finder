<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
</script>

<template>
    <AuthBase
        title="Create an account"
        description="Enter your details below to create your account"
    >
        <Head title="Register" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label>I want to register as</Label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input
                                type="radio"
                                name="role"
                                value="tutor"
                                checked
                                class="peer sr-only"
                            />
                            <div
                                class="flex items-center justify-center rounded-xl border-2 border-slate-100 bg-slate-50 py-3 text-sm font-medium text-slate-600 transition-all peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:border-slate-200 hover:bg-slate-100"
                            >
                                Tutor
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input
                                type="radio"
                                name="role"
                                value="guardian"
                                class="peer sr-only"
                            />
                            <div
                                class="flex items-center justify-center rounded-xl border-2 border-slate-100 bg-slate-50 py-3 text-sm font-medium text-slate-600 transition-all peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:border-slate-200 hover:bg-slate-100"
                            >
                                Guardian
                            </div>
                        </label>
                    </div>
                    <InputError :message="errors.role" />
                </div>

                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="Full name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="phone">Phone number</Label>
                    <Input
                        id="phone"
                        type="text"
                        required
                        :tabindex="2"
                        autocomplete="tel"
                        name="phone"
                        placeholder="+15551234567"
                    />
                    <InputError :message="errors.phone" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address (optional)</Label>
                    <Input
                        id="email"
                        type="email"
                        :tabindex="3"
                        autocomplete="email"
                        name="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="5"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    tabindex="6"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="processing" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-slate-600">
                Already have an account?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="7"
                    >Log in</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>
