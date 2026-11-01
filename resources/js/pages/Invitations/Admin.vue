<template>
    <Head title="Admin invitation" />

    <AppNav>
        <main class="mx-auto w-full max-w-lg px-5 pt-10 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8">
            <p class="text-sm font-medium text-ink-soft">Admin invitation</p>
            <h1 class="mt-2 font-heading text-3xl font-bold tracking-tight text-gray-800">
                Help run {{ invitation.community.name }}
            </h1>
            <p class="mt-4 text-base leading-relaxed text-gray-600">
                {{ invitation.inviter.name }} invited you to be an admin of this community.
            </p>
            <p class="mt-3 text-sm leading-relaxed text-gray-500">
                {{ invitation.community.description }}
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <button
                    type="button"
                    class="inline-flex h-12 flex-1 items-center justify-center rounded-full bg-lagoon-800 font-heading text-sm font-semibold text-white disabled:opacity-60"
                    :disabled="busy"
                    @click="accept"
                >
                    Accept
                </button>
                <button
                    type="button"
                    class="inline-flex h-12 flex-1 items-center justify-center rounded-full border border-line bg-white font-heading text-sm font-semibold text-gray-800 disabled:opacity-60"
                    :disabled="busy"
                    @click="decline"
                >
                    Decline
                </button>
            </div>
        </main>
    </AppNav>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

import AppNav from '@/components/AppNav.vue';

const props = defineProps<{
    invitation: {
        token: string;
        email: string;
        community: {
            name: string;
            slug: string;
            description: string;
        };
        inviter: {
            name: string;
        };
    };
}>();

const busy = ref(false);

function accept(): void {
    busy.value = true;
    router.post(`/invitations/admin/${props.invitation.token}/accept`, {}, {
        onFinish: () => { busy.value = false; },
    });
}

function decline(): void {
    busy.value = true;
    router.post(`/invitations/admin/${props.invitation.token}/decline`, {}, {
        onFinish: () => { busy.value = false; },
    });
}
</script>
