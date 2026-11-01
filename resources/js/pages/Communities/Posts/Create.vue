<template>
    <Head :title="type === 'offer' ? 'Offer help' : 'Ask for help'" />

    <AppNav>
        <main class="mx-auto w-full max-w-2xl px-5 pt-8 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-12">
            <p class="text-sm font-medium text-ink-soft">
                <Link :href="`/communities/${community.slug}`" class="text-lagoon-800 hover:underline">
                    {{ community.name }}
                </Link>
                <span class="mx-1.5 text-gray-300">/</span>
                {{ type === 'offer' ? 'Offer' : 'Need' }}
            </p>

            <header class="mt-4 pb-2">
                <p class="text-xs font-semibold tracking-[0.14em] text-lagoon-700 uppercase">
                    {{ type === 'offer' ? 'Offer help' : 'Ask for help' }}
                </p>
                <h1 class="mt-2 font-heading text-3xl font-bold tracking-tight text-gray-800">
                    {{ type === 'offer' ? 'What can you help with?' : 'What do you need?' }}
                </h1>
                <p class="mt-2 max-w-xl text-base leading-relaxed text-gray-600">
                    Keep it clear and local. Replies stay on this post — no private messages yet.
                </p>
            </header>

            <form
                class="mt-8 space-y-5 rounded-[1.5rem] border border-gray-200/80 bg-white p-5 shadow-[0_18px_40px_-28px_rgb(12_44_49/0.35)] sm:p-7"
                @submit.prevent="submit"
            >
                <UiTextField
                    v-model="form.title"
                    label="Title"
                    name="title"
                    :error="form.errors.title"
                    :placeholder="type === 'offer' ? 'e.g. I can help with GP registration' : 'e.g. Looking for a room near campus'"
                />

                <UiTextarea
                    v-model="form.body"
                    label="Details"
                    name="body"
                    :rows="6"
                    :error="form.errors.body"
                    placeholder="Enough detail for someone nearby to understand and reply usefully."
                />

                <div class="grid gap-4 sm:grid-cols-2">
                    <UiSelect
                        v-model="form.category_key"
                        label="Category"
                        :options="categoryOptions"
                        :error="form.errors.category_key"
                    />
                    <UiSelect
                        v-model="form.urgency"
                        label="Urgency"
                        :options="urgencyOptions"
                        :error="form.errors.urgency"
                    />
                </div>

                <div
                    v-if="selectedCategory?.safety_warning"
                    class="rounded-2xl border border-clay-200 bg-clay-50 px-4 py-3 text-sm leading-relaxed text-clay-900"
                    role="note"
                >
                    <p class="font-semibold">Stay safe</p>
                    <p class="mt-1">{{ safetyNotice }}</p>
                </div>

                <UiTextField
                    v-model="form.location_label"
                    label="Area"
                    name="location_label"
                    optional
                    hint="Keep it coarse — neighbourhood or city is enough."
                    :error="form.errors.location_label"
                />

                <p v-if="form.errors.post" class="text-sm text-clay-800">{{ form.errors.post }}</p>

                <div class="flex flex-wrap gap-3 border-t border-gray-100 pt-5">
                    <button
                        type="submit"
                        class="inline-flex h-12 flex-1 items-center justify-center rounded-full bg-lagoon-800 px-6 font-heading text-sm font-semibold text-white transition hover:bg-lagoon-900 disabled:opacity-60 sm:flex-none"
                        :disabled="form.processing"
                    >
                        {{ type === 'offer' ? 'Post offer' : 'Post need' }}
                    </button>
                    <Link
                        :href="`/communities/${community.slug}`"
                        class="inline-flex h-12 items-center justify-center rounded-full border border-line bg-white px-5 font-heading text-sm font-semibold text-gray-800"
                    >
                        Cancel
                    </Link>
                </div>
            </form>
        </main>
    </AppNav>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppNav from '@/components/AppNav.vue';
import UiSelect from '@/components/ui/UiSelect.vue';
import UiTextarea from '@/components/ui/UiTextarea.vue';
import UiTextField from '@/components/ui/UiTextField.vue';

const props = defineProps<{
    community: {
        id: number;
        slug: string;
        name: string;
        location_label: string | null;
    };
    type: 'need' | 'offer';
    categories: Array<{ key: string; label: string; safety_warning: boolean }>;
    urgencies: Array<{ key: string; label: string }>;
    safetyNotice: string;
    defaults: {
        location_label: string | null;
        urgency: string;
    };
}>();

const form = useForm({
    type: props.type,
    title: '',
    body: '',
    category_key: props.categories[0]?.key ?? 'everyday',
    location_label: props.defaults.location_label ?? '',
    urgency: props.defaults.urgency,
});

const categoryOptions = computed(() =>
    props.categories.map((category) => ({ value: category.key, label: category.label })),
);

const urgencyOptions = computed(() =>
    props.urgencies.map((urgency) => ({ value: urgency.key, label: urgency.label })),
);

const selectedCategory = computed(() =>
    props.categories.find((category) => category.key === form.category_key),
);

function submit(): void {
    form.post(`/communities/${props.community.slug}/posts`);
}
</script>
