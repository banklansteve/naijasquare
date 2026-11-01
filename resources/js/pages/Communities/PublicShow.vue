<template>
    <Head :title="community.name">
        <meta head-key="description" name="description" :content="metaDescription" />
    </Head>

    <PublicShell>
        <main class="mx-auto w-full max-w-5xl px-5 pt-8 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-12">
            <header class="pb-8">
                <div
                    v-if="community.cover_url"
                    class="mb-6 overflow-hidden rounded-[1.35rem] border border-gray-200/80"
                >
                    <img :src="community.cover_url" :alt="`${community.name} cover`" class="h-44 w-full object-cover sm:h-56">
                </div>
                <p v-if="community.location_label" class="text-sm font-medium text-ink-soft">{{ community.location_label }}</p>
                <h1 class="mt-2 font-heading text-3xl font-bold tracking-tight text-balance text-gray-800 sm:text-[2.5rem] sm:leading-tight">
                    {{ community.name }}
                </h1>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="rounded-full border border-line bg-white px-3 py-1 text-xs font-semibold text-ink-soft">
                        {{ community.access_label }}
                    </span>
                    <span class="text-xs font-medium text-ink-soft">
                        {{ community.members }} {{ community.members === 1 ? 'member' : 'members' }}
                    </span>
                </div>
                <p class="mt-5 max-w-2xl text-base leading-relaxed text-gray-600">{{ community.description }}</p>
                <div v-if="community.tags.length" class="mt-4 flex flex-wrap gap-2">
                    <Link
                        v-for="tag in community.tags"
                        :key="tag.key"
                        :href="`/explore/tag/${tag.key}`"
                        class="rounded-full border border-line bg-white px-3 py-1 text-xs font-medium text-ink-soft transition hover:border-lagoon-300 hover:text-lagoon-800"
                    >
                        {{ tag.label }}
                    </Link>
                </div>
            </header>

            <section class="rounded-[1.35rem] border border-gray-200/80 bg-white p-5 sm:p-6">
                <h2 class="font-heading text-lg font-bold text-gray-800">Join this community</h2>
                <p class="mt-2 text-sm leading-relaxed text-gray-600">
                    Create a free account to {{ community.access_mode === 'open' ? 'join' : 'request to join' }}.
                    Email verification is required before you can become a member.
                </p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <Link
                        :href="authLinks.register"
                        class="inline-flex h-11 items-center justify-center rounded-full bg-lagoon-800 px-6 font-heading text-sm font-semibold text-white hover:bg-lagoon-900"
                    >
                        Create account
                    </Link>
                    <Link
                        :href="authLinks.login"
                        class="inline-flex h-11 items-center justify-center rounded-full border border-line bg-white px-6 font-heading text-sm font-semibold text-gray-800"
                    >
                        Sign in
                    </Link>
                </div>
            </section>

            <p class="mt-8 text-sm text-ink-soft">
                Looking for more nearby?
                <Link href="/explore" class="font-semibold text-lagoon-800 hover:underline">Explore communities</Link>
            </p>
        </main>
    </PublicShell>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import PublicShell from '@/components/PublicShell.vue';

const props = defineProps<{
    community: {
        id: number;
        slug: string;
        name: string;
        description: string;
        members: number;
        access_mode: string;
        access_label: string;
        location_label: string | null;
        cover_url: string | null;
        tag_keys: string[];
        tags: Array<{ key: string; label: string }>;
    };
    authLinks: {
        register: string;
        login: string;
    };
}>();

const metaDescription = computed(() => {
    const trimmed = props.community.description.trim();
    if (trimmed.length > 0) {
        return trimmed.length > 155 ? `${trimmed.slice(0, 152)}…` : trimmed;
    }

    const place = props.community.location_label || 'the UK';

    return `Join ${props.community.name}, a Nigerian diaspora community in ${place}.`;
});
</script>
