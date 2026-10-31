<template>
    <Head title="Choose a new password" />

    <AuthFrame kicker="" title="Choose a new password">
        <form class="space-y-5" @submit.prevent="submit">
            <AuthField
                v-model="form.email"
                label="Email address"
                name="email"
                type="email"
                inputmode="email"
                autocomplete="username"
                :error="form.errors.email"
            />
            <AuthField
                v-model="form.password"
                label="New password"
                name="password"
                type="password"
                autocomplete="new-password"
                :error="form.errors.password"
            />
            <AuthField
                v-model="form.password_confirmation"
                label="Confirm password"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                :error="form.errors.password_confirmation"
            />

            <button
                type="submit"
                class="inline-flex h-12 w-full items-center justify-center rounded-full bg-lagoon-700 px-6 text-base font-bold text-white transition-[background-color] duration-500 ease-out hover:bg-lagoon-800 disabled:opacity-60"
                :disabled="form.processing"
            >
                Update password
            </button>

            <p class="text-center text-sm text-ink-soft">
                <Link href="/sign-in" class="font-semibold text-lagoon-800 underline decoration-lagoon-200 underline-offset-4">
                    Back to sign in
                </Link>
            </p>
        </form>
    </AuthFrame>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import AuthField from '@/components/auth/AuthField.vue';
import AuthFrame from '@/components/auth/AuthFrame.vue';

const props = defineProps<{
    token: string;
    email: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit(): void {
    form.post('/reset-password');
}
</script>
