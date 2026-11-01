<template>
    <Head title="Communities you might like" />

    <AuthFrame kicker="Almost there" title="Communities you might like">
        <p class="text-base leading-relaxed text-ink-soft">
            Join any that fit. Or skip and look around later.
        </p>

        <form class="mt-8 space-y-3" @submit.prevent="join">
            <UiCheckbox
                v-for="community in communities"
                :key="community.id"
                v-model="selected"
                :value="community.id"
                card
            >
                <span class="block text-base font-bold text-ink">{{ community.name }}</span>
                <span class="mt-1 block text-sm font-semibold text-lagoon-800">
                    {{ community.members }} {{ community.members === 1 ? 'member' : 'members' }}
                </span>
                <span class="mt-2 block text-sm leading-relaxed text-ink-soft">{{ community.description }}</span>
            </UiCheckbox>

            <p v-if="form.errors.communities" class="text-sm text-clay-800">{{ form.errors.communities }}</p>

            <button
                type="submit"
                class="inline-flex h-12 w-full items-center justify-center rounded-full bg-lagoon-700 text-base font-bold text-white transition-[background-color] duration-500 ease-out hover:bg-lagoon-800 disabled:opacity-60"
                :disabled="form.processing || selected.length === 0"
            >
                Join
            </button>
            <Link
                href="/home"
                class="inline-flex h-12 w-full items-center justify-center rounded-full text-base font-semibold text-lagoon-800 transition-colors duration-500 ease-out hover:bg-lagoon-50"
            >
                Skip for now
            </Link>
        </form>
    </AuthFrame>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import AuthFrame from '@/components/auth/AuthFrame.vue';
import UiCheckbox from '@/components/ui/UiCheckbox.vue';

defineProps<{
    communities: Array<{
        id: number;
        name: string;
        description: string;
        members: number;
    }>;
}>();

const selected = ref<number[]>([]);
const form = useForm({
    communities: [] as number[],
});

function join(): void {
    form.communities = [...selected.value];
    form.post('/join/communities');
}
</script>
