<template>
    <section
        v-if="offers.length"
        class="w-full max-w-full min-w-0 rounded-[1.35rem] border border-lagoon-200/80 bg-lagoon-50/60 p-4 sm:p-5"
        :aria-labelledby="headingId"
    >
        <h2 :id="headingId" class="font-heading text-base font-bold tracking-tight text-lagoon-950 sm:text-lg">
            {{ title }}
        </h2>
        <p class="mt-1 text-sm leading-6 text-lagoon-900/80">
            {{ subtitle }}
        </p>

        <ul class="mt-4 space-y-2.5">
            <li
                v-for="offer in offers"
                :key="offer.id"
            >
                <Link
                    :href="offer.href"
                    class="block rounded-[1.15rem] border border-line/90 bg-white p-4 shadow-card transition hover:shadow-lift"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-moss-100 px-2.5 py-0.5 text-xs font-semibold text-moss-800">
                            Offer
                        </span>
                        <span
                            v-for="label in (offer.category_labels?.length ? offer.category_labels : [offer.category_label])"
                            :key="label"
                            class="text-xs font-medium text-ink-soft"
                        >
                            {{ label }}
                        </span>
                    </div>
                    <p class="mt-2 font-heading text-sm font-bold text-ink">{{ offer.title }}</p>
                    <p class="mt-1 line-clamp-2 text-sm leading-6 text-ink-soft">{{ offer.body }}</p>
                    <p class="mt-2 text-xs font-medium text-ink-soft">
                        {{ offer.author.name }}
                        <template v-if="offerLocation(offer)"> · {{ offerLocation(offer) }}</template>
                        · Reply on this Offer
                    </p>
                </Link>
            </li>
        </ul>
    </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

export type RelatedOfferCard = {
    id: number;
    title: string;
    body: string;
    category_key: string;
    category_keys?: string[];
    category_label: string;
    category_labels?: string[];
    location_label: string | null;
    location_labels?: string[];
    location_specific?: boolean;
    href: string;
    author: { id: number | null; name: string };
};

withDefaults(defineProps<{
    offers: RelatedOfferCard[];
    title?: string;
    subtitle?: string;
    headingId?: string;
}>(), {
    title: 'Offers that might help',
    subtitle: 'Someone is already offering help nearby. Reply on their Offer if it fits — or keep posting your Need.',
    headingId: 'related-offers',
});

function offerLocation(offer: RelatedOfferCard): string | null {
    const labels = (offer.location_labels ?? []).filter(Boolean);
    if (labels.length > 0) {
        return labels.join(', ');
    }

    if (offer.location_label) {
        return offer.location_label;
    }

    if (offer.location_specific === false) {
        return 'Anywhere';
    }

    return null;
}
</script>
