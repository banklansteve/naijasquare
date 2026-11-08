<template>
    <Head title="Home" />

    <AppNav>
        <main class="mx-auto w-full max-w-6xl overflow-x-clip px-5 pt-7 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-10 lg:pt-12">
            <p
                v-if="flashStatus"
                class="mb-6 rounded-2xl border border-lagoon-200 bg-lagoon-50 px-4 py-3 text-sm text-lagoon-900"
            >
                {{ flashStatus }}
            </p>

            <div class="grid min-w-0 gap-8 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] lg:items-start lg:gap-10 xl:gap-12">
                <div class="min-w-0 space-y-7 sm:space-y-8">
                    <HomeGreeting
                        :greeting="greeting"
                        :first-name="firstName"
                        :city="city"
                        :ask-href="askHref"
                        :offer-href="offerHref"
                    />

                    <HomeFirstWeek
                        :first-week="firstWeek"
                        :help-with="helpWith"
                    />

                    <HomePostHero
                        :ask-href="askHref"
                        :offer-href="offerHref"
                    />

                    <HomeMyPosts
                        :posts="myPosts"
                        :empty-href="postHref"
                        list-href="/communities"
                    />

                    <HomeHelpableNeeds
                        v-if="showHelpableSection"
                        :posts="helpableNeeds"
                        :category-filter="categoryFilter"
                        :category-filters="categoryFilters"
                    />

                    <div class="space-y-7 lg:hidden">
                        <HomeMembershipCommunities
                            v-if="administeredCommunities.length"
                            title="Communities you run"
                            heading-id="communities-you-run-mobile"
                            :communities="administeredCommunities"
                            :limit="4"
                        />

                        <HomeBringPeople :invite="invite" />

                        <HomeMembershipCommunities
                            title="Your communities"
                            heading-id="your-communities-mobile"
                            :communities="yourCommunities"
                            :limit="3"
                            pinnable
                            :empty-message="showYourCommunitiesEmpty ? 'Join a community to see it here — and to post or answer Needs.' : null"
                            empty-href="/communities"
                            empty-cta="Find communities"
                        />

                        <HomeNearCommunities
                            :communities="nearCommunities"
                            :empty="nearCommunitiesEmpty"
                        />
                    </div>
                </div>

                <aside class="hidden min-w-0 space-y-7 lg:sticky lg:top-24 lg:block">
                    <HomeMembershipCommunities
                        v-if="administeredCommunities.length"
                        title="Communities you run"
                        heading-id="communities-you-run"
                        :communities="administeredCommunities"
                        :limit="4"
                    />

                    <HomeBringPeople :invite="invite" />

                    <HomeMembershipCommunities
                        title="Your communities"
                        heading-id="your-communities"
                        :communities="yourCommunities"
                        :limit="3"
                        pinnable
                        :empty-message="showYourCommunitiesEmpty ? 'Join a community to see it here — and to post or answer Needs.' : null"
                        empty-href="/communities"
                        empty-cta="Find communities"
                    />

                    <HomeNearCommunities
                        :communities="nearCommunities"
                        :empty="nearCommunitiesEmpty"
                    />
                </aside>
            </div>
        </main>
    </AppNav>
</template>

<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppNav from '@/components/AppNav.vue';
import HomeBringPeople from '@/components/home/HomeBringPeople.vue';
import HomeFirstWeek from '@/components/home/HomeFirstWeek.vue';
import HomeGreeting from '@/components/home/HomeGreeting.vue';
import HomeHelpableNeeds from '@/components/home/HomeHelpableNeeds.vue';
import HomeMembershipCommunities, { type MembershipCommunityCard } from '@/components/home/HomeMembershipCommunities.vue';
import HomeMyPosts from '@/components/home/HomeMyPosts.vue';
import HomeNearCommunities from '@/components/home/HomeNearCommunities.vue';
import type { HomePostCard } from '@/components/home/HomeNeedCard.vue';
import HomePostHero from '@/components/home/HomePostHero.vue';

const props = defineProps<{
    greeting: string;
    firstName: string;
    city: string | null;
    askHref: string;
    offerHref: string;
    postHref: string;
    myPosts: HomePostCard[];
    helpableNeeds: HomePostCard[];
    categoryFilter: string | null;
    categoryFilters: Array<{ key: string; label: string }>;
    yourCommunities: MembershipCommunityCard[];
    administeredCommunities: MembershipCommunityCard[];
    nearCommunities: Array<{
        id: number;
        slug: string;
        name: string;
        members: number;
        monogram: { initials: string; tone: string };
        join_href: string;
    }>;
    nearCommunitiesEmpty: boolean;
    invite: {
        url: string;
        message: string;
        whatsapp_url: string;
    };
    firstWeek: {
        done_count: number;
        total: number;
        progress: number;
        active_key: string | null;
        steps: Array<{
            key: string;
            label: string;
            done: boolean;
            cta_label: string;
            cta_href: string | null;
            kind: string;
        }>;
    } | null;
    helpWith: {
        category_keys: string[];
        note: string | null;
        options: Array<{ key: string; label: string }>;
    };
}>();

const page = usePage();

const flashStatus = computed(() => {
    const flash = page.props.flash as { status?: string } | undefined;

    return flash?.status ?? null;
});

const showHelpableSection = computed(() => {
    return props.helpableNeeds.length > 0 || props.categoryFilter !== null;
});

const showYourCommunitiesEmpty = computed(() => {
    return props.yourCommunities.length === 0 && props.administeredCommunities.length === 0;
});
</script>
