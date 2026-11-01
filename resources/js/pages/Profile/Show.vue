<template>
    <Head title="Profile" />

    <AppNav>
        <main class="mx-auto w-full max-w-2xl px-5 pt-8 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-12">
            <header class="flex items-start gap-4 pb-8">
                <span class="grid size-16 place-items-center overflow-hidden rounded-full bg-lagoon-100 text-lagoon-800 ring-1 ring-line sm:size-20">
                    <img
                        v-if="profile.avatar_url"
                        :src="profile.avatar_url"
                        :alt="profile.name"
                        class="size-full object-cover"
                    >
                    <BaseIcon v-else name="user" class="size-7" />
                </span>
                <div class="min-w-0 pt-1">
                    <h1 class="font-heading text-3xl font-bold tracking-tight text-gray-800">{{ profile.name }}</h1>
                    <p class="mt-2 text-sm text-gray-600">{{ profile.email }}</p>
                    <p v-if="profile.place_label || profile.city" class="mt-1 text-sm text-ink-soft">
                        {{ profile.place_label || profile.city }}
                    </p>
                </div>
            </header>

            <div class="border-t border-gray-200/90" />

            <section class="mt-8 space-y-3">
                <h2 class="text-xl font-semibold tracking-wide text-gray-700">Account</h2>
                <div class="rounded-[1.35rem] border border-dashed border-gray-200 bg-white px-5 py-8 text-center">
                    <p class="text-sm leading-relaxed text-gray-500">
                        Profile editing opens with the next part of account settings.
                    </p>
                </div>
            </section>

            <section class="mt-10">
                <button
                    type="button"
                    class="inline-flex h-11 items-center justify-center rounded-full border border-line bg-white px-5 font-heading text-sm font-semibold text-clay-800 transition duration-300 hover:bg-clay-50"
                    @click="signOut"
                >
                    Sign out
                </button>
            </section>
        </main>
    </AppNav>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

import AppNav from '@/components/AppNav.vue';
import BaseIcon from '@/components/BaseIcon.vue';

defineProps<{
    profile: {
        name: string;
        first_name: string;
        email: string;
        city: string | null;
        place_label: string | null;
        avatar_url: string | null;
    };
}>();

function signOut(): void {
    router.post('/sign-out');
}
</script>
