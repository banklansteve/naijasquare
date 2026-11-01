<template>
    <Head title="Create a community" />

    <AuthFrame :kicker="current.kicker" :title="current.title" :step="step + 1" :total="steps.length">
        <form class="space-y-5" @submit.prevent="next">
            <button
                v-if="step > 0"
                type="button"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft transition-colors duration-500 ease-out hover:text-lagoon-800"
                @click="back"
            >
                <svg class="size-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M12.5 4.5 7 10l5.5 5.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Previous
            </button>

            <div v-if="step === 0" class="space-y-5">
                <UiTextField
                    v-model="form.name"
                    label="Community name"
                    name="name"
                    :error="form.errors.name"
                />
                <UiTextarea
                    v-model="form.description"
                    label="Description"
                    name="description"
                    :rows="4"
                    placeholder="Who is this for, and what happens here?"
                    :error="form.errors.description"
                />
                <UiFileField
                    label="Cover image"
                    name="cover"
                    optional
                    :error="form.errors.cover"
                    @change="onCover"
                >
                    <p v-if="coverPreview" class="mt-3 overflow-hidden rounded-2xl border border-line">
                        <img :src="coverPreview" alt="" class="h-36 w-full object-cover">
                    </p>
                </UiFileField>

                <fieldset>
                    <legend class="text-sm font-semibold text-ink">Location focus</legend>
                    <div class="mt-3 space-y-2">
                        <UiRadio
                            v-for="(label, key) in locationTypes"
                            :key="key"
                            v-model="form.location_type"
                            :value="key"
                            :label="label"
                            name="location_type"
                            card
                        />
                    </div>
                </fieldset>

                <UiTextField
                    v-if="form.location_type !== 'uk_wide'"
                    v-model="form.location_label"
                    :label="form.location_type === 'region' ? 'Region' : 'City'"
                    name="location_label"
                    :error="form.errors.location_label"
                />

                <fieldset>
                    <legend class="text-sm font-semibold text-ink">
                        Tags
                        <span class="font-medium text-ink-soft">Optional</span>
                    </legend>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button
                            v-for="(label, key) in tags"
                            :key="key"
                            type="button"
                            class="rounded-full border px-3 py-1.5 text-sm font-medium transition duration-300"
                            :class="form.tag_keys.includes(key)
                                ? 'border-lagoon-700 bg-lagoon-50 text-lagoon-800'
                                : 'border-line bg-white text-ink-soft hover:border-lagoon-300'"
                            @click="toggleTag(key)"
                        >
                            {{ label }}
                        </button>
                    </div>
                    <p v-if="form.errors.tag_keys" class="mt-2 text-sm text-clay-800">{{ form.errors.tag_keys }}</p>
                </fieldset>
            </div>

            <div v-else-if="step === 1" class="space-y-4">
                <p class="text-sm leading-relaxed text-ink-soft">
                    Choose how people join. You can change this later in settings.
                </p>
                <UiRadio
                    v-for="(mode, key) in accessModes"
                    :key="key"
                    v-model="form.access_mode"
                    :value="key"
                    :label="mode.label"
                    :description="mode.description"
                    name="access_mode"
                    card
                />
                <p v-if="form.errors.access_mode" class="text-sm text-clay-800">{{ form.errors.access_mode }}</p>

                <fieldset class="pt-2">
                    <legend class="text-sm font-semibold text-ink">Who can post</legend>
                    <p class="mt-1 text-sm leading-relaxed text-ink-soft">
                        Members can always reply. This controls who can start a Need or Offer.
                    </p>
                    <div class="mt-3 space-y-2">
                        <UiRadio
                            v-for="(label, key) in whoCanPost"
                            :key="key"
                            v-model="form.who_can_post"
                            :value="key"
                            :label="label"
                            name="who_can_post"
                            card
                        />
                    </div>
                    <p v-if="form.errors.who_can_post" class="mt-2 text-sm text-clay-800">{{ form.errors.who_can_post }}</p>
                </fieldset>
            </div>

            <div v-else-if="step === 2" class="space-y-5">
                <p class="text-sm leading-relaxed text-ink-soft">
                    Guidelines help set the tone. You can skip this and add them later.
                </p>
                <UiTextarea
                    v-model="form.guidelines"
                    label="Guidelines"
                    name="guidelines"
                    :rows="7"
                    placeholder="What should people keep in mind here?"
                    :error="form.errors.guidelines"
                />
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="template in guidelineTemplates"
                        :key="template.label"
                        type="button"
                        class="rounded-full border border-line bg-white px-3 py-1.5 text-sm font-medium text-ink-soft transition hover:border-lagoon-300 hover:text-lagoon-800"
                        @click="insertTemplate(template.body)"
                    >
                        {{ template.label }}
                    </button>
                </div>
                <UiCheckbox
                    v-model="form.require_guidelines_accept"
                    card
                    label="Require members to accept guidelines before posting"
                />
            </div>

            <div v-else class="space-y-5">
                <p class="text-sm leading-relaxed text-ink-soft">
                    Invite admins to help. They get an email and an in-app invitation. Optional.
                </p>
                <div class="relative">
                    <UiTextField
                        v-model="adminQuery"
                        label="Search by name or email"
                        name="admin_search"
                        optional
                        @update:model-value="onAdminSearch"
                    />
                    <ul
                        v-if="searchResults.length"
                        class="absolute z-10 mt-1 w-full overflow-hidden rounded-2xl border border-line bg-white shadow-lg"
                    >
                        <li v-for="user in searchResults" :key="user.id">
                            <button
                                type="button"
                                class="flex w-full flex-col px-4 py-3 text-left hover:bg-lagoon-50"
                                @click="addAdminEmail(user.email)"
                            >
                                <span class="text-sm font-semibold text-ink">{{ user.name }}</span>
                                <span class="text-xs text-ink-soft">{{ user.email }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <UiTextField
                    v-model="manualEmail"
                    label="Or type an email"
                    name="manual_email"
                    type="email"
                    optional
                />
                <button
                    type="button"
                    class="text-sm font-semibold text-lagoon-800"
                    @click="addAdminEmail(manualEmail)"
                >
                    Add email
                </button>
                <ul v-if="form.admin_emails.length" class="space-y-2">
                    <li
                        v-for="email in form.admin_emails"
                        :key="email"
                        class="flex items-center justify-between rounded-2xl border border-line bg-white px-4 py-3 text-sm"
                    >
                        <span>{{ email }}</span>
                        <button type="button" class="font-medium text-clay-800" @click="removeAdminEmail(email)">
                            Remove
                        </button>
                    </li>
                </ul>
                <p v-if="form.errors.admin_emails" class="text-sm text-clay-800">{{ form.errors.admin_emails }}</p>
            </div>

            <button
                type="submit"
                class="inline-flex h-12 w-full items-center justify-center rounded-full bg-lagoon-800 font-heading text-sm font-semibold text-white transition duration-500 ease-out hover:bg-lagoon-900 disabled:opacity-60"
                :disabled="form.processing"
            >
                {{ step < steps.length - 1 ? (step === 2 ? 'Continue' : 'Continue') : 'Create community' }}
            </button>
            <button
                v-if="step === 2"
                type="button"
                class="inline-flex h-11 w-full items-center justify-center text-sm font-semibold text-ink-soft"
                @click="skipGuidelines"
            >
                Skip for now
            </button>
        </form>
    </AuthFrame>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import AuthFrame from '@/components/auth/AuthFrame.vue';
import UiCheckbox from '@/components/ui/UiCheckbox.vue';
import UiFileField from '@/components/ui/UiFileField.vue';
import UiRadio from '@/components/ui/UiRadio.vue';
import UiTextarea from '@/components/ui/UiTextarea.vue';
import UiTextField from '@/components/ui/UiTextField.vue';

const props = defineProps<{
    defaults: {
        location_type: string;
        location_label: string | null;
        city: string | null;
        access_mode: string;
        who_can_post: string;
    };
    tags: Record<string, string>;
    accessModes: Record<string, { label: string; description: string }>;
    whoCanPost: Record<string, string>;
    locationTypes: Record<string, string>;
    guidelineTemplates: Array<{ label: string; body: string }>;
}>();

const steps = [
    { kicker: '', title: 'Name your community' },
    { kicker: '', title: 'Who can join?' },
    { kicker: '', title: 'Set the tone' },
    { kicker: '', title: 'Invite help' },
];

const step = ref(0);
const current = computed(() => steps[step.value] ?? steps[0]);
const adminQuery = ref('');
const manualEmail = ref('');
const coverPreview = ref<string | null>(null);
const searchResults = ref<Array<{ id: number; name: string; email: string }>>([]);
let searchTimer: ReturnType<typeof setTimeout> | null = null;

const form = useForm({
    name: '',
    description: '',
    location_type: props.defaults.location_type,
    location_label: props.defaults.location_label ?? '',
    city: props.defaults.city ?? '',
    tag_keys: [] as string[],
    access_mode: props.defaults.access_mode,
    who_can_post: props.defaults.who_can_post,
    guidelines: '',
    require_guidelines_accept: false,
    cover: null as File | null,
    admin_emails: [] as string[],
});

function onCover(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    form.cover = file;

    if (coverPreview.value) {
        URL.revokeObjectURL(coverPreview.value);
        coverPreview.value = null;
    }

    if (file) {
        coverPreview.value = URL.createObjectURL(file);
    }
}

function toggleTag(key: string): void {
    if (form.tag_keys.includes(key)) {
        form.tag_keys = form.tag_keys.filter((item) => item !== key);
    } else if (form.tag_keys.length < 8) {
        form.tag_keys = [...form.tag_keys, key];
    }
}

function insertTemplate(body: string): void {
    form.guidelines = form.guidelines
        ? `${form.guidelines.trim()}\n\n${body}`
        : body;
}

function skipGuidelines(): void {
    form.guidelines = '';
    form.require_guidelines_accept = false;
    step.value = 3;
}

function addAdminEmail(email: string): void {
    const normalized = email.trim().toLowerCase();
    if (!normalized || !normalized.includes('@')) {
        return;
    }
    if (!form.admin_emails.includes(normalized)) {
        form.admin_emails = [...form.admin_emails, normalized];
    }
    manualEmail.value = '';
    adminQuery.value = '';
    searchResults.value = [];
}

function removeAdminEmail(email: string): void {
    form.admin_emails = form.admin_emails.filter((item) => item !== email);
}

function onAdminSearch(value: string): void {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }
    searchTimer = setTimeout(async () => {
        if (value.trim().length < 2) {
            searchResults.value = [];
            return;
        }
        const response = await fetch(`/api/users/search?q=${encodeURIComponent(value.trim())}`, {
            headers: { Accept: 'application/json' },
        });
        if (!response.ok) {
            searchResults.value = [];
            return;
        }
        const data = await response.json() as { users: Array<{ id: number; name: string; email: string }> };
        searchResults.value = data.users;
    }, 250);
}

function back(): void {
    if (step.value > 0) {
        step.value -= 1;
    }
}

function next(): void {
    if (step.value === 0) {
        if (!form.name.trim() || form.name.trim().length < 3) {
            form.setError('name', 'Give your community a name (at least 3 characters).');
            return;
        }
        if (!form.description.trim() || form.description.trim().length < 10) {
            form.setError('description', 'Add a short description (at least 10 characters).');
            return;
        }
        form.clearErrors();
        if (form.location_type === 'city') {
            form.city = form.location_label;
        }
        step.value = 1;
        return;
    }

    if (step.value === 1) {
        step.value = 2;
        return;
    }

    if (step.value === 2) {
        step.value = 3;
        return;
    }

    form.post('/communities', { forceFormData: true });
}
</script>
