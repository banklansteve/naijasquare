<template>
    <section
        v-if="items.length"
        class="rounded-[1.35rem] border border-line/90 bg-white p-5 shadow-card sm:p-6"
        aria-labelledby="coming-up"
    >
        <h2 id="coming-up" class="font-heading text-lg font-bold tracking-tight text-ink">
            Coming up
        </h2>
        <ul class="mt-4 space-y-3">
            <li
                v-for="item in items"
                :key="item.id"
                class="rounded-[1.15rem] border border-line/80 bg-paper/50 p-4"
            >
                <p class="font-heading text-sm font-bold text-ink">{{ item.title }}</p>
                <p v-if="item.when_label" class="mt-1 text-xs font-medium text-ink-soft">
                    {{ item.when_label }}
                </p>
                <p v-if="item.community_name" class="mt-1 text-xs text-ink-soft">
                    {{ item.community_name }}
                </p>
                <Link
                    v-if="item.href"
                    :href="item.href"
                    class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-lagoon-800"
                >
                    View
                    <BaseIcon name="arrow" class="size-3.5" />
                </Link>
            </li>
        </ul>
    </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import BaseIcon from '@/components/BaseIcon.vue';

export type ComingUpItem = {
    id: number | string;
    title: string;
    when_label?: string | null;
    community_name?: string | null;
    href?: string | null;
};

defineProps<{
    items: ComingUpItem[];
}>();
</script>
