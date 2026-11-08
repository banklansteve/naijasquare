<template>
    <Head title="Notifications" />

    <AppNav>
        <main class="mx-auto w-full max-w-2xl px-5 pt-8 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-12">
            <h1 class="font-heading text-3xl font-bold tracking-tight text-ink">Notifications</h1>
            <p class="mt-2 text-sm leading-6 text-ink-soft">
                Needs that match your Offers, invites, and other quiet updates.
            </p>

            <ul v-if="notifications.length" class="mt-8 space-y-3">
                <li
                    v-for="item in notifications"
                    :key="item.id"
                >
                    <Link
                        v-if="item.href"
                        :href="`/notifications/${item.id}/read`"
                        method="post"
                        as="button"
                        class="flex w-full flex-col rounded-[1.25rem] border border-line/90 bg-white px-4 py-4 text-left shadow-card transition hover:shadow-lift"
                        :class="item.unread ? 'ring-1 ring-lagoon-200' : ''"
                    >
                        <span class="font-heading text-sm font-bold text-ink">{{ item.title }}</span>
                        <span class="mt-1 text-sm leading-6 text-ink-soft">{{ item.body }}</span>
                    </Link>
                    <div
                        v-else
                        class="rounded-[1.25rem] border border-line/90 bg-white px-4 py-4 shadow-card"
                    >
                        <p class="font-heading text-sm font-bold text-ink">{{ item.title }}</p>
                        <p class="mt-1 text-sm leading-6 text-ink-soft">{{ item.body }}</p>
                    </div>
                </li>
            </ul>

            <div
                v-else
                class="mt-8 rounded-[1.35rem] border border-dashed border-line bg-white px-5 py-10 text-center"
            >
                <p class="text-sm text-ink-soft">Nothing waiting for you right now.</p>
            </div>
        </main>
    </AppNav>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import AppNav from '@/components/AppNav.vue';

defineProps<{
    notifications: Array<{
        id: number;
        type: string;
        title: string;
        body: string;
        href: string | null;
        read_at: string | null;
        created_at: string | null;
        unread: boolean;
    }>;
}>();
</script>
