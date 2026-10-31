<template>
    <Head :title="invite ? `${invite.inviter.name} invited you` : headline" />

    <div class="min-h-dvh">
        <header
            class="sticky top-0 z-40 w-full bg-paper/75 pt-[env(safe-area-inset-top)] backdrop-blur-md"
        >
            <div class="flex h-16 w-full items-center justify-between px-5 sm:px-10 lg:px-14 xl:px-20">
                <Link href="/" class="flex items-center gap-2.5 rounded-lg" :aria-label="`${appName} home`">
                    <AppMark class="size-8" />
                    <span class="font-heading text-[17px] font-bold tracking-tight text-ink">{{ appName }}</span>
                </Link>
                <div class="flex items-center gap-2 sm:gap-4">
                    <Link
                        href="/sign-in"
                        class="inline-flex h-10 w-28 items-center justify-center rounded-full text-sm font-medium text-ink-soft transition-[background-color,color] duration-500 ease-out hover:bg-lagoon-700 hover:text-white sm:w-36"
                    >
                        Log in
                    </Link>
                    <Link
                        href="/join"
                        class="inline-flex h-10 w-28 items-center justify-center rounded-full border border-lagoon-200 bg-white text-sm font-semibold text-lagoon-800 shadow-sm transition duration-300 ease-out hover:border-lagoon-700 hover:bg-lagoon-700 hover:text-white sm:w-36"
                    >
                        Sign up
                    </Link>
                </div>
            </div>
        </header>

        <main>
            <section class="relative w-full px-5 py-14 sm:px-8 lg:grid lg:min-h-[calc(85dvh-4rem)] lg:grid-cols-2 lg:items-center lg:px-[14%] lg:py-16">
                <div class="relative z-10 max-w-xl lg:max-w-none lg:pr-10">
                    <p v-if="invite" class="mb-5 text-sm font-semibold text-lagoon-800">
                        {{ invite.inviter.name }} invited you to join {{ invite.community.name }}.
                    </p>

                    <h1 class="text-[2.75rem] leading-[1.02] font-semibold tracking-[-0.035em] text-ink sm:text-6xl lg:text-6xl xl:text-7xl">
                        <span v-for="line in headlineLines" :key="line" class="block">{{ line }}</span>
                        <span v-if="emphasis" class="mt-1 block text-lagoon-800">{{ emphasis }}</span>
                    </h1>
                    <p class="mt-5 max-w-xl text-lg leading-relaxed text-ink-soft">
                        {{ subline }}
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <Link
                            :href="banner.primaryHref"
                            class="inline-flex h-14 w-full items-center justify-center rounded-full bg-lagoon-700 px-8 text-base font-bold text-white shadow-lift transition-[background-color,box-shadow] duration-500 ease-out hover:bg-lagoon-800 hover:shadow-card sm:w-auto lg:h-16 lg:px-10 lg:text-lg"
                        >
                            {{ banner.primary }}
                        </Link>
                        <a
                            :href="communityHref"
                            class="inline-flex h-14 w-full items-center justify-center gap-2 rounded-full border-2 border-lagoon-700 bg-white px-8 text-base font-bold text-lagoon-800 transition-[background-color,color,border-color] duration-500 ease-out hover:border-lagoon-800 hover:bg-lagoon-700 hover:text-white sm:w-auto lg:h-16 lg:px-10 lg:text-lg"
                        >
                            {{ banner.secondary }}
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="relative mt-12 min-h-80 lg:mt-0 lg:h-full">
                    <div class="pointer-events-none absolute inset-y-0 -right-16 left-0 lg:-inset-y-10 lg:-right-20 lg:left-0" aria-hidden="true">
                        <img
                            :src="aside.image"
                            alt=""
                            class="h-full w-full object-cover object-[center_64%] opacity-[0.22] saturate-100 [mask-image:radial-gradient(42%_46%_at_58%_50%,black_20%,transparent_100%)]"
                        >
                    </div>

                    <div class="relative flex h-full flex-col justify-center">
                        <div class="flex flex-col">
                            <NoticeCard
                                v-for="(card, index) in board"
                                :key="card.line"
                                :card="card"
                                :class="desktopPlaces[index]"
                                :tilt="tilts[index]"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <section aria-labelledby="do-here-heading" class="doorways">
                <div class="mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-24">
                    <h2 id="do-here-heading" class="max-w-xl text-3xl font-semibold tracking-tight text-ink sm:text-4xl">
                        {{ doHere.heading }}
                    </h2>
                    <p class="mt-3 max-w-xl text-lg leading-relaxed text-ink-soft">
                        {{ doHere.subline }}
                    </p>
                    <ul class="mt-10 grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6">
                        <li v-for="(card, index) in doHere.cards" :key="card.title" class="min-w-0">
                            <DoorwayCard
                                :card="card"
                                :class="[doorSurfaces[index], doorTilts[index]]"
                            />
                        </li>
                    </ul>
                    <div class="mt-12 flex flex-col items-center text-center lg:items-end lg:text-right">
                        <Link
                            :href="doHere.signupHref"
                            class="inline-flex min-h-11 w-full items-center justify-center rounded-2xl bg-lagoon-700 px-8 py-3 text-base font-semibold text-white shadow-lift transition-[background-color,box-shadow] duration-500 ease-out hover:bg-lagoon-800 hover:shadow-card sm:w-auto"
                        >
                            {{ doHere.cta }}
                        </Link>
                    </div>
                </div>
            </section>

            <ValueCards :values="values" />

            <section v-if="communities.length" id="communities" aria-labelledby="communities-heading">
                <div class="mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-24">
                    <h2 id="communities-heading" class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">
                        Find your community
                    </h2>
                    <div class="mt-8 flex flex-wrap gap-2">
                        <Link
                            v-for="community in communities"
                            :key="community.href"
                            :href="community.href"
                            class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-ink ring-1 ring-line hover:ring-lagoon-300"
                        >
                            {{ community.name }}
                        </Link>
                    </div>
                    <p class="mt-6 max-w-xl text-base leading-relaxed text-ink-soft">
                        Some communities are open to all. Others, the moderators approve each new member.
                    </p>
                </div>
            </section>

            <TrustBoard :trust="trust" />

            <HowPath :how="how" />

            <FaqList :faq="faq" />

            <section aria-labelledby="close-heading" class="bg-linear-to-b from-lagoon-600 via-lagoon-800 to-lagoon-900">
                <div class="mx-auto max-w-4xl px-5 py-20 text-center sm:px-8 sm:py-28">
                    <h2 id="close-heading" class="text-[2.65rem] leading-[1.05] font-bold tracking-[-0.03em] text-white sm:text-6xl lg:text-7xl">
                        <span v-for="line in headlineLines" :key="line" class="block">{{ line }}</span>
                        <span v-if="emphasis" class="block">{{ emphasis }}</span>
                    </h2>
                    <p class="mx-auto mt-6 max-w-md text-base text-balance text-white/80 sm:text-lg">
                        {{ close.line }}
                    </p>
                    <Link
                        :href="close.ctaHref"
                        class="mt-8 inline-flex h-12 items-center justify-center rounded-full bg-white px-8 text-base font-semibold text-lagoon-900 shadow-[0_10px_28px_-16px_rgb(0_0_0/0.7)] transition-[background-color,box-shadow] duration-500 ease-out hover:bg-lagoon-50 sm:h-14 sm:px-10"
                    >
                        {{ close.cta }}
                    </Link>
                    <p class="mt-6 text-sm text-white/75">
                        {{ close.account }}
                        <Link
                            :href="close.signInHref"
                            class="ml-1 font-semibold text-white underline decoration-white/30 underline-offset-4 transition-colors duration-500 ease-out hover:decoration-white"
                        >
                            {{ close.signIn }}
                        </Link>
                    </p>
                </div>
            </section>
        </main>

        <footer class="mx-auto flex max-w-6xl flex-col gap-4 px-5 py-8 text-sm text-ink-soft sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <p>For adults 18+</p>
            <nav aria-label="Footer" class="flex flex-wrap gap-x-5 gap-y-2">
                <Link
                    v-for="link in footer"
                    :key="link.href"
                    :href="link.href"
                    class="underline-offset-4 hover:text-ink hover:underline"
                >
                    {{ link.label }}
                </Link>
            </nav>
        </footer>
    </div>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppMark from '@/components/AppMark.vue';
import DoorwayCard from '@/components/landing/DoorwayCard.vue';
import FaqList from '@/components/landing/FaqList.vue';
import HowPath from '@/components/landing/HowPath.vue';
import NoticeCard from '@/components/landing/NoticeCard.vue';
import TrustBoard from '@/components/landing/TrustBoard.vue';
import ValueCards from '@/components/landing/ValueCards.vue';
import { appName } from '@/lib/app';
import type { BannerActions, BannerAside, BoardCard, CloseBand, DoHere, FaqSection, FooterLink, HowItWorks, Invite, PublicCommunity, TrustSection, ValueSection } from '@/types/landing';

const props = defineProps<{
    headline: string;
    emphasis: string;
    subline: string;
    closer: string;
    banner: BannerActions;
    assurance: string;
    aside: BannerAside;
    invite: Invite | null;
    board: BoardCard[];
    values: ValueSection;
    doHere: DoHere;
    communities: PublicCommunity[];
    how: HowItWorks;
    trust: TrustSection;
    faq: FaqSection;
    close: CloseBand;
    footer: FooterLink[];
}>();

const headlineLead = computed(() => {
    const at = props.headline.lastIndexOf(props.emphasis);

    if (props.emphasis === '' || at <= 0) {
        return props.headline;
    }

    return props.headline.slice(0, at).trimEnd();
});

const headlineLines = computed(() => headlineLead.value.split(/(?<=\.)\s+/).filter((line) => line !== ''));

const communityHref = computed(() => (props.communities.length > 0 ? '#communities' : '#find-communities'));

const doorSurfaces = [
    'bg-door-mist',
    'bg-door-foam',
    'bg-door-tide',
    'bg-door-pool',
];
const doorTilts = [
    'md:-rotate-[0.7deg]',
    'md:rotate-[0.6deg]',
    'md:rotate-[0.5deg]',
    'md:-rotate-[0.4deg]',
];

const tilts = [
    'origin-top -rotate-2 lg:-rotate-[2.5deg]',
    'origin-top rotate-[1.5deg] lg:rotate-[2deg]',
    'origin-top -rotate-[1.5deg] lg:-rotate-[1.5deg]',
    'origin-top rotate-2 lg:rotate-[2deg]',
];
const desktopPlaces = [
    'relative z-[1] lg:w-[84%] lg:self-start',
    'relative z-[2] mt-1 ml-6 lg:mt-3 lg:ml-0 lg:w-[84%] lg:self-end',
    'relative z-[3] mt-1 mr-5 lg:mt-3 lg:mr-0 lg:ml-[7%] lg:w-[84%] lg:self-start',
    'relative z-[4] mt-1 ml-4 lg:mt-3 lg:ml-0 lg:w-[84%] lg:self-end',
];
</script>
