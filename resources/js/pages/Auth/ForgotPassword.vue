<template>
    <Head title="Forgot password" />

    <AuthFrame kicker="" title="Reset your password">
        <form class="space-y-5" @submit.prevent="submit">
            <p class="text-base leading-relaxed text-ink-soft">
                Enter the email on your account and we will send a reset link if it matches.
            </p>

            <p
                v-if="status"
                class="rounded-2xl border border-lagoon-100 bg-lagoon-50 px-4 py-3 text-sm leading-relaxed text-lagoon-800"
            >
                {{ status }}
            </p>

            <AuthField
                v-model="form.email"
                label="Email address"
                name="email"
                type="email"
                inputmode="email"
                autocomplete="username"
                :error="form.errors.email"
            />

            <button
                type="submit"
                class="inline-flex h-12 w-full items-center justify-center rounded-full bg-lagoon-700 px-6 text-base font-bold text-white transition-[background-color] duration-500 ease-out hover:bg-lagoon-800 disabled:opacity-60"
                :disabled="form.processing"
            >
                Send reset link
            </button>

            <p class="text-center text-sm text-ink-soft">
                Remembered it?
                <Link href="/sign-in" class="ml-1 font-semibold text-lagoon-800 underline decoration-lagoon-200 underline-offset-4">
                    Sign in
                </Link>
            </p>
        </form>
    </AuthFrame>
</template>

<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import AuthField from '@/components/auth/AuthField.vue';
import AuthFrame from '@/components/auth/AuthFrame.vue';

const page = usePage();
const status = computed(() => (page.props.flash as { status?: string | null }).status ?? null);

const form = useForm({
    email: '',
});

function submit(): void {
    form.post('/forgot-password');
}
</script>
