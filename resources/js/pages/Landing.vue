<template>
    <Head :title="invite ? `${invite.inviter.name} invited you` : headline" />

    <div class="min-h-dvh">
        <header
            class="sticky top-0 z-40 w-full bg-paper/75 pt-[env(safe-area-inset-top)] backdrop-blur-md"
        >
            <div class="flex h-16 w-full items-center justify-between px-5 sm:px-10 lg:px-14 xl:px-20">
                <Link href="/" class="flex items-center gap-2.5 rounded-lg" :aria-label="`${appName} home`">
                    <AppMark class="size-8" />
                    <span class="text-[17px] font-bold tracking-tight text-ink">{{ appName }}</span>
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
                        <span class="block">{{ headlineLead }}</span>
                        <span class="mt-1 block text-lagoon-800">{{ emphasis }}</span>
                    </h1>
                    <p class="mt-5 max-w-lg text-lg leading-relaxed text-ink-soft">
                        {{ subline }}
                    </p>
                    <p class="mt-6 text-lg leading-snug font-medium text-moss-700">
                        {{ closer }}
                    </p>

                    <Link
                        href="/join"
                        class="mt-4 inline-flex h-14 w-full items-center justify-center rounded-2xl bg-lagoon-700 px-8 text-base font-semibold text-white shadow-lift transition hover:bg-lagoon-800 active:scale-[0.98] sm:w-auto lg:h-16 lg:px-10 lg:text-lg"
                    >
                        Create your account
                    </Link>
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
                        <p class="mt-3 max-w-xs text-sm leading-relaxed text-ink-soft">
                            {{ doHere.note }}
                        </p>
                    </div>
                </div>
            </section>

            <section v-if="communities.length" aria-labelledby="communities-heading">
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

            <section aria-labelledby="steps-heading">
                <div class="mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-24">
                    <h2 id="steps-heading" class="text-2xl font-semibold tracking-tight text-ink sm:text-3xl">
                        How it works
                    </h2>
                    <ol class="mt-10 grid gap-4 md:grid-cols-3">
                        <li
                            v-for="(step, index) in steps"
                            :key="step.title"
                            class="flex h-full flex-col rounded-[28px] bg-white p-7 shadow-lift ring-1 ring-black/5"
                        >
                            <span class="grid size-12 place-items-center rounded-2xl bg-lagoon-50 text-lagoon-800">
                                <BaseIcon :name="step.icon" class="size-6" />
                            </span>
                            <p class="mt-8 text-xs font-semibold tracking-[0.16em] text-ink-soft uppercase">
                                Step {{ index + 1 }}
                            </p>
                            <h3 class="mt-2 text-xl font-semibold tracking-tight text-ink">{{ step.title }}</h3>
                            <p class="mt-3 text-[15px] leading-relaxed text-ink-soft">{{ step.body }}</p>
                        </li>
                    </ol>
                </div>
            </section>

            <section aria-labelledby="trust-heading">
                <div class="mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-24">
                    <div class="max-w-2xl">
                        <h2 id="trust-heading" class="text-2xl font-semibold tracking-tight text-ink sm:text-3xl">
                            Why you can trust it
                        </h2>
                        <p class="mt-3 text-base leading-relaxed text-ink-soft">{{ trustIntro }}</p>
                    </div>
                    <ul class="mt-10 grid gap-4 sm:grid-cols-2">
                        <li
                            v-for="item in trust"
                            :key="item.title"
                            class="flex gap-4 rounded-3xl border border-white/80 bg-white/90 p-6 shadow-card ring-1 ring-lagoon-100/80 backdrop-blur-sm"
                        >
                            <span class="grid size-11 shrink-0 place-items-center rounded-full bg-moss-50 text-moss-700 ring-1 ring-moss-100">
                                <BaseIcon :name="item.icon" class="size-5" />
                            </span>
                            <div>
                                <h3 class="text-base font-semibold tracking-tight text-ink">{{ item.title }}</h3>
                                <p class="mt-1.5 text-[15px] leading-relaxed text-ink-soft">{{ item.body }}</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </section>

            <section aria-labelledby="founder-heading">
                <div class="mx-auto max-w-2xl px-5 py-16 sm:px-8 sm:py-24">
                    <h2 id="founder-heading" class="text-sm font-semibold tracking-wide text-ink-soft uppercase">
                        A note from the founder
                    </h2>
                    <div class="mt-6 rounded-sm bg-[#fffdf8] px-6 py-8 shadow-card ring-1 ring-[#e6dfd2] sm:px-10 sm:py-10">
                        <div class="space-y-4 font-serif text-lg leading-relaxed text-ink">
                            <p v-for="paragraph in founder.paragraphs" :key="paragraph">{{ paragraph }}</p>
                        </div>
                        <p v-if="founder.name" class="mt-8 text-sm font-semibold text-ink">{{ founder.name }}</p>
                    </div>
                </div>
            </section>

            <section aria-labelledby="faq-heading">
                <div class="mx-auto max-w-3xl px-5 py-16 sm:px-8 sm:py-24">
                    <h2 id="faq-heading" class="text-2xl font-semibold tracking-tight text-ink sm:text-3xl">
                        Questions
                    </h2>
                    <div class="mt-8 overflow-hidden rounded-[28px] bg-white shadow-lift ring-1 ring-black/5">
                        <div v-for="(item, index) in faqs" :key="item.question" class="border-b border-line last:border-b-0">
                            <h3>
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between gap-4 px-5 py-5 text-left transition-colors duration-300 hover:bg-lagoon-50/40 sm:px-7"
                                    :aria-expanded="openFaq === index"
                                    :aria-controls="`faq-${index}`"
                                    @click="toggleFaq(index)"
                                >
                                    <span class="text-base font-semibold text-ink">{{ item.question }}</span>
                                    <span
                                        class="grid size-8 shrink-0 place-items-center rounded-full bg-lagoon-50 text-lg leading-none text-lagoon-800 transition duration-300 ease-out"
                                        :class="openFaq === index ? 'rotate-45 bg-lagoon-700 text-white' : ''"
                                        aria-hidden="true"
                                    >+</span>
                                </button>
                            </h3>
                            <div
                                :id="`faq-${index}`"
                                class="grid transition-[grid-template-rows] duration-300 ease-out"
                                :class="openFaq === index ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
                            >
                                <div class="overflow-hidden">
                                    <p
                                        class="px-5 pb-5 text-[15px] leading-relaxed text-ink-soft transition-opacity duration-300 sm:px-7"
                                        :class="openFaq === index ? 'opacity-100' : 'opacity-0'"
                                    >
                                        {{ item.answer }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section>
                <div class="h-20 bg-linear-to-b from-paper to-lagoon-800" aria-hidden="true"></div>
                <div class="bg-lagoon-800">
                    <div class="mx-auto max-w-6xl px-5 py-16 text-center sm:px-8 sm:py-20">
                        <h2 class="text-3xl font-semibold tracking-[-0.03em] text-white sm:text-5xl">
                            <span class="block">{{ headlineLead }}</span>
                            <span class="mt-1 block text-lagoon-200">{{ emphasis }}</span>
                        </h2>
                        <Link
                            href="/join"
                            class="mt-8 inline-flex h-14 items-center justify-center rounded-2xl bg-white px-8 text-base font-semibold text-lagoon-800 transition hover:bg-lagoon-50"
                        >
                            Create your account
                        </Link>
                    </div>
                </div>
                <div class="h-20 bg-linear-to-b from-lagoon-800 to-paper" aria-hidden="true"></div>
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
import { computed, ref } from 'vue';

import AppMark from '@/components/AppMark.vue';
import BaseIcon from '@/components/BaseIcon.vue';
import DoorwayCard from '@/components/landing/DoorwayCard.vue';
import NoticeCard from '@/components/landing/NoticeCard.vue';
import { appName } from '@/lib/app';
import type { BannerAside, BoardCard, DoHere, Faq, FooterLink, FounderNote, HowStep, Invite, PublicCommunity, TrustPromise } from '@/types/landing';

const props = defineProps<{
    headline: string;
    emphasis: string;
    subline: string;
    closer: string;
    assurance: string;
    aside: BannerAside;
    invite: Invite | null;
    board: BoardCard[];
    doHere: DoHere;
    communities: PublicCommunity[];
    steps: HowStep[];
    trustIntro: string;
    trust: TrustPromise[];
    founder: FounderNote;
    faqs: Faq[];
    footer: FooterLink[];
}>();

const headlineLead = computed(() => {
    const at = props.headline.lastIndexOf(props.emphasis);

    if (props.emphasis === '' || at <= 0) {
        return props.headline;
    }

    return props.headline.slice(0, at).trimEnd();
});

const openFaq = ref<number | null>(null);

function toggleFaq(index: number): void {
    openFaq.value = openFaq.value === index ? null : index;
}

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
