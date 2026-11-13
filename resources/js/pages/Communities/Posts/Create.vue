<template>
    <Head :title="isOffer ? 'Offer help' : 'Ask for help'" />

    <AppNav>
        <main class="mx-auto w-full max-w-6xl overflow-x-clip px-5 pt-7 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-10 lg:pt-12">
            <p class="text-sm font-medium text-ink-soft">
                <Link :href="`/communities/${community.slug}`" class="text-lagoon-800 hover:underline">
                    {{ community.name }}
                </Link>
                <span class="mx-1.5 text-gray-300">/</span>
                {{ isOffer ? 'Offer' : 'Need' }}
            </p>

            <div class="mt-5 grid min-w-0 gap-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] lg:items-start lg:gap-10">
                <div class="min-w-0 space-y-5">
                    <header class="rounded-[1.5rem] border border-line/80 bg-white p-5 shadow-card sm:p-7">
                        <div class="inline-flex rounded-full border border-line bg-paper p-1">
                            <button
                                type="button"
                                class="rounded-full px-4 py-2 font-heading text-sm font-semibold transition"
                                :class="!isOffer
                                    ? 'bg-lagoon-800 text-white shadow-sm'
                                    : 'text-ink-soft hover:text-ink'"
                                @click="setType('need')"
                            >
                                Ask for help
                            </button>
                            <button
                                type="button"
                                class="rounded-full px-4 py-2 font-heading text-sm font-semibold transition"
                                :class="isOffer
                                    ? 'bg-lagoon-800 text-white shadow-sm'
                                    : 'text-ink-soft hover:text-ink'"
                                @click="setType('offer')"
                            >
                                Offer help
                            </button>
                        </div>

                        <h1 class="mt-5 font-heading text-3xl font-bold tracking-tight text-balance text-ink sm:text-[2.1rem]">
                            {{ isOffer ? 'What can you help with?' : 'What do you need?' }}
                        </h1>
                        <p class="mt-2 max-w-xl text-sm leading-6 text-ink-soft sm:text-base">
                            {{ isOffer
                                ? 'Share something concrete people can ask you for. Replies stay on this post.'
                                : 'Be specific about place, timing, and what would help. Replies stay on this post.' }}
                        </p>

                        <div
                            class="mt-5 flex items-start gap-3 rounded-2xl border border-lagoon-200/80 bg-lagoon-50 px-4 py-3.5"
                            role="note"
                        >
                            <span class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-full bg-white text-lagoon-800 ring-1 ring-lagoon-100">
                                <BaseIcon name="shield" class="size-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-lagoon-950">We’ll help you stay safe</p>
                                <p class="mt-1 text-sm leading-6 text-lagoon-900/85">
                                    {{ selectedCategory?.safety_warning
                                        ? safetyNotice
                                        : 'Keep money, documents, and private details off this post. Meet in public when you meet offline.' }}
                                </p>
                            </div>
                        </div>
                    </header>

                    <form
                        class="space-y-6 rounded-[1.5rem] border border-line/80 bg-white p-5 shadow-card sm:p-7"
                        @submit.prevent="submit"
                    >
                        <div>
                            <UiTextField
                                v-model="form.title"
                                label="Title"
                                name="title"
                                :error="form.errors.title"
                                :placeholder="titlePlaceholder"
                            />
                            <p class="mt-2 text-sm leading-6 text-ink-soft">
                                {{ titleHint }}
                            </p>
                        </div>

                        <UiSelect
                            v-if="!isOffer"
                            v-model="form.category_key"
                            label="Category"
                            searchable
                            :options="categorySelectOptions"
                            :error="form.errors.category_key"
                            placeholder="Choose one category"
                            search-placeholder="Search categories…"
                            hint="Pick the main area this Need is about — we use it to draft details."
                        />

                        <UiMultiSelect
                            v-else
                            v-model="form.category_keys"
                            label="Categories"
                            :options="categorySelectOptions"
                            :max="3"
                            :error="form.errors.category_keys || form.errors['category_keys.0']"
                            placeholder="Add up to 3 categories"
                            search-placeholder="Search categories…"
                            hint="Choose the kinds of help you can give."
                        />

                        <NeedDetailsAssist
                            v-if="!isOffer && showDetailsAssistPanel"
                            :loading="suggestionLoading || suggestionPending"
                            :visible="suggestionVisible"
                            :text="suggestionText"
                            @use="useSuggestion"
                            @dismiss="dismissSuggestion"
                        />

                        <div>
                            <UiTextarea
                                v-model="form.body"
                                label="Details"
                                name="body"
                                :rows="3"
                                autoresize
                                :error="form.errors.body"
                                :placeholder="detailsPlaceholder"
                            />

                            <ul
                                v-if="isOffer || (! suggestionVisible && ! suggestionLoading)"
                                class="mt-3 space-y-1.5 text-sm leading-6 text-ink-soft"
                            >
                                <li
                                    v-for="tip in detailTips"
                                    :key="tip"
                                    class="flex gap-2"
                                >
                                    <span class="mt-2 size-1.5 shrink-0 rounded-full bg-lagoon-400" aria-hidden="true" />
                                    <span>{{ tip }}</span>
                                </li>
                            </ul>
                        </div>

                        <fieldset v-if="!isOffer">
                            <legend class="text-sm font-semibold text-ink">Urgency</legend>
                            <div class="mt-3 grid grid-cols-3 gap-2">
                                <button
                                    v-for="urgency in urgencies"
                                    :key="urgency.key"
                                    type="button"
                                    class="rounded-2xl border px-3 py-3 text-center text-sm font-semibold transition"
                                    :class="form.urgency === urgency.key
                                        ? urgency.key === 'urgent'
                                            ? 'border-clay-700 bg-clay-50 text-clay-800'
                                            : 'border-lagoon-700 bg-lagoon-50 text-lagoon-950'
                                        : 'border-line bg-paper text-ink-soft hover:border-lagoon-300 hover:text-ink'"
                                    @click="form.urgency = urgency.key"
                                >
                                    {{ urgency.label }}
                                </button>
                            </div>
                            <p v-if="form.errors.urgency" class="mt-2 text-sm text-clay-800">
                                {{ form.errors.urgency }}
                            </p>
                        </fieldset>

                        <div v-if="!isOffer" class="relative" @focusin="areaOpen = true">
                            <UiTextField
                                v-model="form.location_label"
                                label="Area"
                                name="location_label"
                                optional
                                :hint="locationHint"
                                :error="form.errors.location_label"
                                :placeholder="areaPlaceholder"
                                autocomplete="off"
                                @update:model-value="onAreaInput"
                            />
                            <ul
                                v-if="areaOpen && filteredLocations.length"
                                class="absolute z-20 mt-1 max-h-48 w-full overflow-auto rounded-2xl border border-line bg-white py-1 shadow-lift"
                                role="listbox"
                            >
                                <li
                                    v-for="place in filteredLocations"
                                    :key="place"
                                >
                                    <button
                                        type="button"
                                        class="flex w-full px-4 py-2.5 text-left text-sm text-ink transition hover:bg-lagoon-50"
                                        @mousedown.prevent="pickArea(place)"
                                    >
                                        {{ place }}
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div v-else class="space-y-3">
                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-line bg-paper px-4 py-3">
                                <input
                                    v-model="form.location_specific"
                                    type="checkbox"
                                    class="mt-1 size-4 rounded border-line text-lagoon-800 focus:ring-lagoon-700"
                                >
                                <span>
                                    <span class="block text-sm font-semibold text-ink">This Offer is for a specific place</span>
                                    <span class="mt-1 block text-sm leading-6 text-ink-soft">
                                        Leave unchecked for help that works anywhere (e.g. immigration advice).
                                    </span>
                                </span>
                            </label>

                            <UiMultiSelect
                                v-if="form.location_specific"
                                v-model="form.location_labels"
                                label="Areas"
                                :options="locationSelectOptions"
                                :max="3"
                                allow-custom
                                :error="form.errors.location_labels || form.errors['location_labels.0']"
                                placeholder="Add up to 3 areas"
                                search-placeholder="Search or type an area…"
                                hint="Keep it coarse — neighbourhood or city is enough. Press Enter to add."
                            />
                        </div>

                        <div
                            class="flex items-center gap-3 rounded-2xl border px-4 py-3"
                            :class="readiness.complete
                                ? 'border-moss-200 bg-moss-50 text-moss-800'
                                : 'border-line bg-paper text-ink-soft'"
                            role="status"
                        >
                            <BaseIcon
                                :name="readiness.complete ? 'verified' : 'bulb'"
                                class="size-5 shrink-0"
                                :class="readiness.complete ? 'text-moss-700' : 'text-lagoon-700'"
                            />
                            <p class="text-sm leading-6">
                                {{ readiness.message }}
                            </p>
                        </div>

                        <p v-if="form.errors.post" class="text-sm text-clay-800">{{ form.errors.post }}</p>

                        <div class="flex flex-wrap gap-3 border-t border-gray-100 pt-5">
                            <button
                                type="submit"
                                class="inline-flex h-12 flex-1 items-center justify-center rounded-full bg-lagoon-800 px-6 font-heading text-sm font-semibold text-white transition hover:bg-lagoon-900 disabled:opacity-60 sm:flex-none"
                                :disabled="form.processing"
                            >
                                {{ isOffer ? 'Share your Offer' : 'Share with the community' }}
                            </button>
                            <Link
                                :href="`/communities/${community.slug}`"
                                class="inline-flex h-12 items-center justify-center rounded-full border border-line bg-white px-5 font-heading text-sm font-semibold text-ink"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>
                </div>

                <aside class="min-w-0 space-y-5 lg:sticky lg:top-24">
                    <RelatedOffersPanel
                        v-if="isOffer === false"
                        :offers="liveRelatedOffers"
                        heading-id="related-offers-create"
                    />

                    <div class="rounded-[1.5rem] border border-line/80 bg-linear-to-b from-white to-lagoon-50/40 p-5 shadow-card sm:p-6">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="font-heading text-lg font-bold tracking-tight text-ink">
                                Live preview
                            </h2>
                            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-ink-soft ring-1 ring-line">
                                How it will look
                            </span>
                        </div>

                        <article class="mt-5 rounded-[1.25rem] border border-line/90 bg-white p-5 shadow-sm">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="isOffer ? 'bg-moss-100 text-moss-800' : 'bg-lagoon-100 text-lagoon-800'"
                                >
                                    {{ isOffer ? 'Offer' : 'Need' }}
                                </span>
                                <span
                                    v-for="label in previewCategoryLabels"
                                    :key="label"
                                    class="rounded-full border border-line bg-paper px-2.5 py-1 text-xs font-medium text-ink-soft"
                                >
                                    {{ label }}
                                </span>
                                <span
                                    v-if="!isOffer"
                                    class="rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="form.urgency === 'urgent'
                                        ? 'bg-clay-50 text-clay-800'
                                        : 'bg-paper text-ink-soft'"
                                >
                                    {{ selectedUrgency?.label ?? 'Soon' }}
                                </span>
                            </div>

                            <h3 class="mt-3 font-heading text-xl font-bold tracking-tight text-ink">
                                {{ previewTitle }}
                            </h3>
                            <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-ink-soft">
                                {{ previewBody }}
                            </p>
                            <p class="mt-4 text-xs font-medium text-ink-soft">
                                {{ community.name }}
                                <template v-if="previewLocationText"> · {{ previewLocationText }}</template>
                            </p>
                        </article>

                        <p class="mt-4 text-sm leading-6 text-ink-soft">
                            Members in {{ community.name }} will see this in the community. Clear titles get faster, better replies.
                        </p>
                    </div>
                </aside>
            </div>
        </main>
    </AppNav>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

import AppNav from '@/components/AppNav.vue';
import BaseIcon from '@/components/BaseIcon.vue';
import RelatedOffersPanel, { type RelatedOfferCard } from '@/components/home/RelatedOffersPanel.vue';
import NeedDetailsAssist from '@/components/posts/NeedDetailsAssist.vue';
import UiMultiSelect from '@/components/ui/UiMultiSelect.vue';
import UiSelect from '@/components/ui/UiSelect.vue';
import UiTextarea from '@/components/ui/UiTextarea.vue';
import UiTextField from '@/components/ui/UiTextField.vue';

const props = defineProps<{
    community: {
        id: number;
        slug: string;
        name: string;
        city?: string | null;
        location_label: string | null;
    };
    type: 'need' | 'offer';
    categories: Array<{ key: string; label: string; safety_warning: boolean }>;
    urgencies: Array<{ key: string; label: string }>;
    safetyNotice: string;
    defaults: {
        location_label: string | null;
        urgency: string;
        category_key?: string;
    };
    userCity?: string | null;
    hints: {
        title: string;
        body: string;
        place: string;
        topic: string;
        suggested_category_key: string;
    };
    locationSuggestions: string[];
    relatedOffers?: RelatedOfferCard[];
}>();

const defaultCategory = props.defaults.category_key
    ?? props.hints.suggested_category_key
    ?? props.categories[0]?.key
    ?? 'everyday';

const form = useForm({
    type: props.type,
    title: '',
    body: '',
    category_key: defaultCategory,
    category_keys: [defaultCategory] as string[],
    location_label: props.defaults.location_label ?? '',
    location_specific: false,
    location_labels: [] as string[],
    urgency: props.defaults.urgency,
});

const areaOpen = ref(false);
const liveRelatedOffers = ref<RelatedOfferCard[]>([...(props.relatedOffers ?? [])]);
let relatedOffersTimer: ReturnType<typeof setTimeout> | null = null;

const suggestionText = ref('');
const suggestionVisible = ref(false);
const suggestionLoading = ref(false);
const suggestionPending = ref(false);
const suggestionDismissed = ref(false);
const suggestionUsed = ref(false);
let suggestionTimer: ReturnType<typeof setTimeout> | null = null;
let suggestionRequestId = 0;

const isOffer = computed(() => form.type === 'offer');

const categorySelectOptions = computed(() =>
    props.categories.map((category) => ({
        value: category.key,
        label: category.label,
    })),
);

const locationSelectOptions = computed(() => {
    const suggestions = props.locationSuggestions ?? [];
    const selected = form.location_labels;
    const merged = [...selected, ...suggestions];

    return Array.from(new Set(merged.filter((place) => place.trim() !== ''))).map((place) => ({
        value: place,
        label: place,
    }));
});

async function refreshRelatedOffers(): Promise<void> {
    if (form.type !== 'need') {
        liveRelatedOffers.value = [];

        return;
    }

    try {
        const params = new URLSearchParams({
            category_key: form.category_key,
        });

        if (form.location_label.trim() !== '') {
            params.set('location_label', form.location_label.trim());
        }

        const response = await fetch(`/communities/${props.community.slug}/related-offers?${params.toString()}`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (! response.ok) {
            return;
        }

        const payload = await response.json() as { offers?: RelatedOfferCard[] };
        liveRelatedOffers.value = payload.offers ?? [];
    } catch {
        // Keep the last good suggestions if the refresh fails.
    }
}

function scheduleRelatedOffersRefresh(): void {
    if (relatedOffersTimer !== null) {
        clearTimeout(relatedOffersTimer);
    }

    relatedOffersTimer = setTimeout(() => {
        void refreshRelatedOffers();
    }, 280);
}

watch(
    () => [form.type, form.category_key, form.location_label] as const,
    () => {
        scheduleRelatedOffersRefresh();
    },
);

watch(
    () => [form.type, form.title, form.category_key, form.location_label, form.body] as const,
    () => {
        if (form.type === 'offer') {
            clearSuggestionTimer();
            suggestionDismissed.value = false;
            suggestionUsed.value = false;
            resetSuggestionState();

            return;
        }

        if (form.body.trim() !== '') {
            clearSuggestionTimer();
            resetSuggestionState();

            return;
        }

        if (suggestionDismissed.value || suggestionUsed.value) {
            return;
        }

        if (form.title.trim().length < 5 || form.category_key.trim() === '') {
            clearSuggestionTimer();
            resetSuggestionState();

            return;
        }

        scheduleSuggestion();
    },
);

watch(
    () => form.category_keys.slice(),
    (keys) => {
        if (form.type === 'offer' && keys.length > 0) {
            form.category_key = String(keys[0]);
        }
    },
);

const selectedCategory = computed(() => {
    const key = isOffer.value
        ? String(form.category_keys[0] ?? form.category_key)
        : form.category_key;

    return props.categories.find((category) => category.key === key);
});

const selectedCategories = computed(() => {
    if (! isOffer.value) {
        return selectedCategory.value ? [selectedCategory.value] : [];
    }

    return props.categories.filter((category) => form.category_keys.includes(category.key));
});

const previewCategoryLabels = computed(() => {
    const labels = selectedCategories.value.map((category) => category.label);

    return labels.length > 0 ? labels : ['Category'];
});

const previewLocationText = computed(() => {
    if (isOffer.value) {
        if (! form.location_specific) {
            return 'Anywhere';
        }

        return form.location_labels.map((label) => String(label).trim()).filter(Boolean).join(', ') || null;
    }

    return form.location_label.trim() || null;
});

const selectedUrgency = computed(() =>
    props.urgencies.find((urgency) => urgency.key === form.urgency),
);

const placeLabel = computed(() => {
    const typed = form.location_label.trim();
    if (typed !== '') {
        return typed;
    }

    return props.hints.place || props.userCity || props.community.city || 'your area';
});

const titlePlaceholder = computed(() => buildTitlePlaceholder(
    form.type,
    form.category_key,
    selectedCategory.value?.label ?? 'help',
    props.hints.topic,
    placeLabel.value,
));

const titleHint = computed(() => {
    if (isOffer.value) {
        return 'Name the skill or help you can give — and who it is for.';
    }

    return `Be specific — e.g. include ${placeLabel.value} if location matters.`;
});

const detailsPlaceholder = computed(() => {
    if (isOffer.value) {
        if (! form.location_specific) {
            return 'What you can do, how people should start, and any limits — even if it is not tied to one place.';
        }

        return `What you can do, how people should start, and any limits — especially around ${placeLabel.value}.`;
    }

    if (form.category_key === 'jobs') {
        return `Role, experience level, and what a helpful reply would include — keep it around ${placeLabel.value}.`;
    }

    if (form.category_key === 'housing') {
        return 'Budget, move-in timing, and area preferences. Never share your exact address here.';
    }

    return `What you need, when you need it, and what a good reply looks like around ${placeLabel.value}.`;
});

const detailTips = computed(() => {
    if (isOffer.value) {
        return [
            'Say what you can help with in one sentence.',
            'Mention availability (evenings, weekends, 30 minutes).',
            'Add any limits so replies stay realistic.',
        ];
    }

    return [
        `Mention ${placeLabel.value} if it helps people reply.`,
        'Say what you’ve already tried, briefly.',
        'Describe what a helpful reply would include.',
    ];
});

const areaPlaceholder = computed(() => {
    return props.userCity
        ?? props.community.city
        ?? props.community.location_label
        ?? 'e.g. neighbourhood or city';
});

const locationHint = computed(() => {
    if (props.userCity) {
        return `Pre-filled from your profile (${props.userCity}). Start typing for suggestions — keep it coarse.`;
    }

    return 'Start typing for suggestions. Keep it coarse — neighbourhood or city is enough.';
});

const filteredLocations = computed(() => {
    const query = form.location_label.trim().toLowerCase();
    const options = props.locationSuggestions ?? [];

    if (query === '') {
        return options.slice(0, 8);
    }

    return options
        .filter((place) => place.toLowerCase().includes(query) && place.toLowerCase() !== query)
        .slice(0, 8);
});

function singularize(value: string): string {
    if (value.endsWith('ies') && value.length > 3) {
        return `${value.slice(0, -3)}y`;
    }

    if (value.endsWith('ses') || value.endsWith('xes') || value.endsWith('zes')) {
        return value.slice(0, -2);
    }

    if (value.endsWith('s') && ! value.endsWith('ss')) {
        return value.slice(0, -1);
    }

    return value;
}

function readableTopic(topic: string, categoryKey: string, categoryLabel: string): string {
    const cleaned = topic.trim().toLowerCase();

    if (cleaned === '' || cleaned === 'local help') {
        if (categoryKey === 'jobs') {
            return 'job';
        }

        return categoryLabel.toLowerCase();
    }

    if (categoryKey === 'jobs') {
        const base = singularize(cleaned);
        return base.includes('job') ? base : `${base} job`;
    }

    if (categoryKey === 'healthcare') {
        const base = singularize(cleaned);
        if (base.includes('dentist')) {
            return 'a dentist';
        }

        return `${base} help`;
    }

    return cleaned;
}

function buildTitlePlaceholder(
    type: 'need' | 'offer',
    categoryKey: string,
    categoryLabel: string,
    topic: string,
    place: string,
): string {
    const topicPhrase = readableTopic(topic, categoryKey, categoryLabel);

    if (type === 'offer') {
        if (! form.location_specific) {
            if (categoryKey === 'immigration') {
                return 'e.g. I can help with visa and immigration questions';
            }

            if (categoryKey === 'mentoring') {
                return 'e.g. I can mentor someone starting out in my field';
            }

            if (categoryKey === 'training') {
                return 'e.g. I can help with skills training or practice interviews';
            }

            if (categoryKey === 'career') {
                return 'e.g. I can help with CV reviews and career planning';
            }

            if (categoryKey === 'language') {
                return 'e.g. I can help with English conversation practice';
            }

            if (categoryKey === 'food_culture') {
                return 'e.g. I can share recipes or help find African groceries';
            }

            if (categoryKey === 'business') {
                return 'e.g. I can advise on starting a small side project';
            }

            if (categoryKey === 'travel_shipping') {
                return 'e.g. I can help with shipping or sending things home';
            }

            return `e.g. I can help with ${topicPhrase}`;
        }

        if (categoryKey === 'housing') {
            return `e.g. I can help with housing tips in ${place}`;
        }

        return `e.g. I can help with ${topicPhrase} around ${place}`;
    }

    switch (categoryKey) {
        case 'jobs':
            return `e.g. Looking for a ${topicPhrase} around ${place}`;
        case 'housing':
            return `e.g. Looking for a flat or room in ${place}`;
        case 'immigration':
            return `e.g. Need help with a visa question in ${place}`;
        case 'healthcare':
            return `e.g. Looking for ${topicPhrase} around ${place}`;
        case 'education':
            return `e.g. Looking for school or course advice in ${place}`;
        case 'mentoring':
            return 'e.g. Looking for a mentor in my field';
        case 'training':
            return 'e.g. Looking for training or skills practice';
        case 'career':
            return 'e.g. Need career guidance or a CV review';
        case 'language':
            return 'e.g. Looking for English conversation practice';
        case 'food_culture':
            return 'e.g. Looking for a recipe or where to buy ingredients';
        case 'business':
            return 'e.g. Need advice on a small business or side project';
        case 'travel_shipping':
            return 'e.g. Need help shipping or sending something home';
        case 'childcare':
            return `e.g. Looking for childcare tips in ${place}`;
        case 'transport':
            return `e.g. Need a lift or travel tip around ${place}`;
        case 'legal':
            return `e.g. Need help with paperwork in ${place}`;
        case 'money':
            return `e.g. Need banking or money advice in ${place}`;
        default:
            return `e.g. Looking for ${topicPhrase} around ${place}`;
    }
}

function onAreaInput(): void {
    areaOpen.value = true;
}

function csrfToken(): string {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

const showDetailsAssistPanel = computed(() => {
    if (suggestionDismissed.value || suggestionUsed.value || form.body.trim() !== '') {
        return false;
    }

    return suggestionLoading.value
        || suggestionPending.value
        || suggestionVisible.value;
});

function canSuggestDetails(): boolean {
    return form.type === 'need'
        && ! suggestionDismissed.value
        && ! suggestionUsed.value
        && form.body.trim() === ''
        && form.title.trim().length >= 5
        && form.category_key.trim() !== '';
}

function clearSuggestionTimer(): void {
    if (suggestionTimer !== null) {
        clearTimeout(suggestionTimer);
        suggestionTimer = null;
    }

    suggestionPending.value = false;
}

function resetSuggestionState(): void {
    suggestionVisible.value = false;
    suggestionText.value = '';
    suggestionLoading.value = false;
    suggestionPending.value = false;
}

async function fetchNeedDetailsSuggestion(): Promise<void> {
    if (! canSuggestDetails()) {
        suggestionPending.value = false;

        return;
    }

    const requestId = ++suggestionRequestId;
    suggestionPending.value = false;
    suggestionLoading.value = true;
    suggestionVisible.value = false;

    try {
        const response = await fetch(`/communities/${props.community.slug}/posts/need-details-suggestion`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                title: form.title.trim(),
                category_key: form.category_key,
                location_label: form.location_label.trim() || null,
            }),
        });

        if (! response.ok || requestId !== suggestionRequestId) {
            return;
        }

        const payload = await response.json() as { suggestion?: string };
        const text = (payload.suggestion ?? '').trim();

        if (text === '' || ! canSuggestDetails() || requestId !== suggestionRequestId) {
            return;
        }

        suggestionText.value = text;
        suggestionVisible.value = true;
    } catch {
        // Keep posting frictionless if the suggestion fails.
    } finally {
        if (requestId === suggestionRequestId) {
            suggestionLoading.value = false;
            suggestionPending.value = false;
        }
    }
}

function scheduleSuggestion(): void {
    if (suggestionTimer !== null) {
        clearTimeout(suggestionTimer);
    }

    suggestionPending.value = true;
    suggestionVisible.value = false;

    suggestionTimer = setTimeout(() => {
        suggestionTimer = null;
        void fetchNeedDetailsSuggestion();
    }, 450);
}

function useSuggestion(): void {
    if (suggestionText.value.trim() === '') {
        return;
    }

    form.body = suggestionText.value;
    suggestionUsed.value = true;
    clearSuggestionTimer();
    resetSuggestionState();
}

function dismissSuggestion(): void {
    suggestionDismissed.value = true;
    clearSuggestionTimer();
    resetSuggestionState();
}

function pickArea(place: string): void {
    form.location_label = place;
    areaOpen.value = false;
}

function onDocumentClick(event: MouseEvent): void {
    const target = event.target as HTMLElement | null;
    if (! target?.closest?.('[name="location_label"]') && ! target?.closest?.('[role="listbox"]')) {
        areaOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', onDocumentClick);
});

onUnmounted(() => {
    document.removeEventListener('click', onDocumentClick);

    if (relatedOffersTimer !== null) {
        clearTimeout(relatedOffersTimer);
        relatedOffersTimer = null;
    }

    clearSuggestionTimer();
});

const previewTitle = computed(() => {
    const title = form.title.trim();

    if (title !== '') {
        return title;
    }

    return isOffer.value ? 'Your offer title will show here' : 'Your Need title will show here';
});

const previewBody = computed(() => {
    const body = form.body.trim();

    if (body !== '') {
        return body;
    }

    return isOffer.value
        ? 'Add a few details so members know how to take you up on the offer.'
        : 'Add enough detail for someone nearby to understand and reply with something useful.';
});

const readiness = computed(() => {
    const titleOk = form.title.trim().length >= 8;
    const bodyOk = form.body.trim().length >= 20;

    if (titleOk && bodyOk) {
        return {
            complete: true,
            message: isOffer.value
                ? 'Looking good — this Offer is clear enough to share.'
                : 'Looking good — this Need is clear enough to share.',
        };
    }

    if (! titleOk && ! bodyOk) {
        return {
            complete: false,
            message: 'Start with a specific title, then add a short detail paragraph.',
        };
    }

    if (! titleOk) {
        return {
            complete: false,
            message: 'Make the title a bit more specific so the right people notice it.',
        };
    }

    return {
        complete: false,
        message: 'Add a little more detail so replies can be useful on the first try.',
    };
});

function setType(type: 'need' | 'offer'): void {
    form.type = type;

    if (type === 'offer' && form.category_keys.length === 0) {
        form.category_keys = [form.category_key];
    }

    if (type === 'need' && form.category_keys.length > 0) {
        form.category_key = String(form.category_keys[0]);
    }

    const url = new URL(window.location.href);
    url.searchParams.set('type', type);
    window.history.replaceState(window.history.state, '', url.toString());
}

function submit(): void {
    if (form.type === 'offer') {
        form.category_key = String(form.category_keys[0] ?? form.category_key);
        form.urgency = 'normal';

        if (! form.location_specific) {
            form.location_labels = [];
            form.location_label = '';
        } else {
            form.location_label = String(form.location_labels[0] ?? '');
        }
    } else {
        form.category_keys = [form.category_key];
        form.location_specific = false;
        form.location_labels = form.location_label.trim() !== '' ? [form.location_label.trim()] : [];
    }

    form.location_specific = form.type === 'offer' ? Boolean(form.location_specific) : false;
    form.post(`/communities/${props.community.slug}/posts`);
}
</script>
