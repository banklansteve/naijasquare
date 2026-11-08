<template>
    <section class="w-full max-w-full min-w-0" aria-labelledby="start-here">
        <h2 id="start-here" class="font-heading text-xl font-bold tracking-tight text-ink">
            Start here
        </h2>
        <div class="mt-4 grid w-full grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
            <Link
                v-for="action in actions"
                :key="action.key"
                :href="action.href"
                class="group flex min-w-0 flex-col rounded-[1.35rem] border border-line/90 bg-white p-5 shadow-card transition duration-500 ease-out hover:shadow-lift focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lagoon-700 sm:min-h-[10.5rem] sm:p-5"
            >
                <span class="grid size-12 place-items-center text-lagoon-800">
                    <BaseIcon
                        :name="actionIcon(action)"
                        light
                        class="size-12"
                    />
                </span>
                <span class="mt-4 font-heading text-base font-bold tracking-tight text-ink">
                    {{ action.title }}
                </span>
                <span class="mt-1 grow text-sm leading-6 text-ink-soft">
                    {{ action.description }}
                </span>
            </Link>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import BaseIcon from '@/components/BaseIcon.vue';
import type { IconName } from '@/types/icons';

type Action = {
    key: string;
    title: string;
    description: string;
    href: string;
    icon: IconName;
};

defineProps<{
    actions: Action[];
}>();

function actionIcon(action: Action): IconName {
    if (action.key === 'post') {
        return 'start-post';
    }

    if (action.key === 'communities') {
        return 'start-communities';
    }

    if (action.key === 'explore') {
        return 'start-explore';
    }

    return action.icon;
}
</script>
