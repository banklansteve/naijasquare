<template>
    <Head :title="`${community.name} settings`" />

    <AppNav>
        <main class="mx-auto w-full max-w-5xl px-5 pt-8 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-12">
            <p v-if="flash?.status" class="mb-6 rounded-2xl border border-lagoon-200 bg-lagoon-50 px-4 py-3 text-sm text-lagoon-900">
                {{ flash.status }}
            </p>

            <div class="mb-6">
                <Link :href="`/communities/${community.slug}`" class="text-sm font-medium text-lagoon-800">
                    ← Back to community
                </Link>
                <h1 class="mt-3 font-heading text-3xl font-bold tracking-tight text-gray-800">Settings</h1>
                <p class="mt-2 text-sm text-gray-600">{{ community.name }}</p>
            </div>

            <div class="lg:grid lg:grid-cols-[14rem_minmax(0,1fr)] lg:gap-10">
                <nav class="mb-8 flex gap-2 overflow-x-auto pb-2 lg:mb-0 lg:flex-col lg:overflow-visible" aria-label="Settings sections">
                    <button
                        v-for="item in nav"
                        :key="item.key"
                        type="button"
                        class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold whitespace-nowrap transition-colors duration-300 ease-out lg:rounded-2xl"
                        :class="activeSection === item.key ? 'bg-lagoon-800 text-white' : 'border border-line bg-white text-gray-700 hover:bg-gray-50'"
                        @click="selectSection(item.key)"
                    >
                        {{ item.label }}
                    </button>
                </nav>

                <div class="min-w-0">
                    <form v-if="activeSection === 'general'" class="space-y-5" @submit.prevent="saveGeneralPut">
                        <UiTextField v-model="general.name" label="Name" name="name" :error="general.errors.name" />
                        <UiTextarea
                            v-model="general.description"
                            label="Description"
                            name="description"
                            :rows="4"
                            :error="general.errors.description"
                        />
                        <fieldset>
                            <legend class="text-sm font-semibold text-ink">Location focus</legend>
                            <div class="mt-3 space-y-2">
                                <UiRadio
                                    v-for="(label, key) in config.locationTypes"
                                    :key="key"
                                    v-model="general.location_type"
                                    :value="key"
                                    :label="label"
                                    name="location_type"
                                    card
                                />
                            </div>
                        </fieldset>
                        <UiTextField
                            v-if="general.location_type !== 'uk_wide'"
                            v-model="general.location_label"
                            :label="general.location_type === 'region' ? 'Region' : 'City'"
                            name="location_label"
                        />
                        <fieldset>
                            <legend class="text-sm font-semibold text-ink">Tags</legend>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <button
                                    v-for="(label, key) in config.tags"
                                    :key="key"
                                    type="button"
                                    class="rounded-full border px-3 py-1.5 text-sm font-medium"
                                    :class="general.tag_keys.includes(key) ? 'border-lagoon-700 bg-lagoon-50 text-lagoon-800' : 'border-line bg-white text-ink-soft'"
                                    @click="toggleTag(key)"
                                >
                                    {{ label }}
                                </button>
                            </div>
                        </fieldset>
                        <UiFileField
                            label="Cover image"
                            name="cover"
                            optional
                            :error="general.errors.cover"
                            @change="onCover"
                        >
                            <p v-if="coverPreview || community.cover_url" class="mt-3 overflow-hidden rounded-2xl border border-line">
                                <img :src="coverPreview ?? community.cover_url ?? undefined" alt="" class="h-36 w-full object-cover">
                            </p>
                        </UiFileField>
                        <UiCheckbox
                            v-model="general.admin_digest_enabled"
                            card
                            label="Weekly digests open with notifications. Toggle saved now; delivery comes later."
                        />
                        <button type="submit" class="inline-flex h-11 items-center rounded-full bg-lagoon-800 px-6 text-sm font-semibold text-white" :disabled="general.processing">
                            Save general
                        </button>
                    </form>

                    <form v-else-if="activeSection === 'access'" class="space-y-5" @submit.prevent="saveAccess">
                        <fieldset>
                            <legend class="text-sm font-semibold text-ink">Access mode</legend>
                            <div class="mt-3 space-y-2">
                                <UiRadio
                                    v-for="(mode, key) in config.accessModes"
                                    :key="key"
                                    v-model="access.access_mode"
                                    :value="key"
                                    :label="mode.label"
                                    :description="mode.description"
                                    name="access_mode"
                                    card
                                />
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend class="text-sm font-semibold text-ink">Who can post</legend>
                            <p class="mt-1 text-xs text-ink-soft">Enforced when posts exist.</p>
                            <div class="mt-3 space-y-2">
                                <UiRadio
                                    v-for="(label, key) in config.whoCanPost"
                                    :key="key"
                                    v-model="access.who_can_post"
                                    :value="key"
                                    :label="label"
                                    name="who_can_post"
                                    card
                                />
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend class="text-sm font-semibold text-ink">Member list visibility</legend>
                            <div class="mt-3 space-y-2">
                                <UiRadio
                                    v-for="(label, key) in config.memberListVisibility"
                                    :key="key"
                                    v-model="access.member_list_visibility"
                                    :value="key"
                                    :label="label"
                                    name="member_list_visibility"
                                    card
                                />
                            </div>
                        </fieldset>
                        <button type="submit" class="inline-flex h-11 items-center rounded-full bg-lagoon-800 px-6 text-sm font-semibold text-white" :disabled="access.processing">
                            Save access
                        </button>
                    </form>

                    <form v-else-if="activeSection === 'guidelines'" class="space-y-5" @submit.prevent="saveGuidelines">
                        <UiTextarea
                            v-model="guidelines.guidelines"
                            label="Guidelines"
                            name="guidelines"
                            :rows="8"
                        />
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="template in config.guidelineTemplates"
                                :key="template.label"
                                type="button"
                                class="rounded-full border border-line bg-white px-3 py-1.5 text-sm text-ink-soft"
                                @click="insertGuideline(template.body)"
                            >
                                {{ template.label }}
                            </button>
                        </div>
                        <UiCheckbox
                            v-model="guidelines.require_guidelines_accept"
                            card
                            label="Require members to accept guidelines"
                        />
                        <button type="submit" class="inline-flex h-11 items-center rounded-full bg-lagoon-800 px-6 text-sm font-semibold text-white" :disabled="guidelines.processing">
                            Save guidelines
                        </button>
                    </form>

                    <div v-else-if="activeSection === 'admins'" class="space-y-8">
                        <div>
                            <h2 class="font-heading text-lg font-bold text-gray-800">Admins & roles</h2>
                            <ul class="mt-4 space-y-2">
                                <li v-for="admin in admins" :key="admin.id" class="flex items-center justify-between rounded-2xl border border-line bg-white px-4 py-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">{{ admin.name }}</p>
                                        <p class="text-xs text-ink-soft">{{ admin.email }} · {{ admin.role_label }}</p>
                                    </div>
                                    <button
                                        v-if="admin.role === 'moderator' && permissions.can_remove_admin"
                                        type="button"
                                        class="text-sm font-semibold text-clay-800"
                                        @click="removeAdmin(admin.id)"
                                    >
                                        Remove
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <form v-if="permissions.can_invite_admin" class="space-y-3" @submit.prevent="sendInvite">
                            <UiTextField v-model="invite.email" label="Invite an admin by email" name="email" type="email" :error="invite.errors.email" />
                            <button type="submit" class="inline-flex h-11 items-center rounded-full bg-lagoon-800 px-6 text-sm font-semibold text-white" :disabled="invite.processing">
                                Send invitation
                            </button>
                        </form>

                        <div v-if="pendingInvites.length">
                            <h3 class="text-sm font-semibold text-gray-700">Pending invitations</h3>
                            <ul class="mt-3 space-y-2">
                                <li v-for="row in pendingInvites" :key="row.id" class="flex items-center justify-between rounded-2xl border border-line bg-white px-4 py-3 text-sm">
                                    <span>{{ row.email }}</span>
                                    <button type="button" class="font-semibold text-clay-800" @click="revokeInvite(row.id)">Revoke</button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div v-else-if="activeSection === 'moderation'" class="space-y-8">
                        <div>
                            <h2 class="font-heading text-lg font-bold text-gray-800">Join requests</h2>
                            <ul v-if="joinRequests.length" class="mt-4 space-y-3">
                                <li v-for="row in joinRequests" :key="row.id" class="rounded-2xl border border-line bg-white px-4 py-4">
                                    <p class="text-sm font-semibold text-gray-800">{{ row.user.name }}</p>
                                    <p class="text-xs text-ink-soft">{{ row.user.email }}</p>
                                    <p v-if="row.note" class="mt-2 text-sm text-gray-600">{{ row.note }}</p>
                                    <div v-if="permissions.can_approve" class="mt-3 flex gap-3">
                                        <button type="button" class="text-sm font-semibold text-lagoon-800" @click="approveRequest(row.id)">Approve</button>
                                        <button type="button" class="text-sm font-semibold text-clay-800" @click="declineRequest(row.id)">Decline</button>
                                    </div>
                                </li>
                            </ul>
                            <p v-else class="mt-3 text-sm text-gray-500">No pending join requests.</p>
                        </div>

                        <div>
                            <h2 class="font-heading text-lg font-bold text-gray-800">Reported content</h2>
                            <div class="mt-3 rounded-[1.35rem] border border-dashed border-gray-200 bg-white/70 px-5 py-8 text-center">
                                <p class="text-sm text-gray-500">The full report queue opens with moderation in the next part of the app.</p>
                            </div>
                        </div>

                        <div>
                            <h2 class="font-heading text-lg font-bold text-gray-800">Pins & mutes</h2>
                            <div class="mt-3 rounded-[1.35rem] border border-dashed border-gray-200 bg-white/70 px-5 py-8 text-center">
                                <p class="text-sm text-gray-500">Pin and mute controls open with posts.</p>
                            </div>
                        </div>

                        <div>
                            <h2 class="font-heading text-lg font-bold text-gray-800">Members</h2>
                            <ul v-if="members.length" class="mt-4 space-y-2">
                                <li v-for="member in members" :key="member.id" class="flex items-center justify-between rounded-2xl border border-line bg-white px-4 py-3">
                                    <span class="text-sm font-medium">{{ member.name }}</span>
                                    <button
                                        v-if="permissions.can_remove_member"
                                        type="button"
                                        class="text-sm font-semibold text-clay-800"
                                        @click="removeMember(member.id)"
                                    >
                                        Remove
                                    </button>
                                </li>
                            </ul>
                            <p v-else class="mt-3 text-sm text-gray-500">No regular members yet.</p>
                        </div>
                    </div>

                    <div v-else-if="activeSection === 'danger'" class="space-y-10">
                        <div v-if="permissions.can_transfer">
                            <h2 class="font-heading text-lg font-bold text-gray-800">Transfer ownership</h2>
                            <p class="mt-2 text-sm text-gray-600">The new creator must already be a member. You become an admin.</p>
                            <form class="mt-4 space-y-4" @submit.prevent="transfer">
                                <UiSelect
                                    v-model="transferForm.user_id"
                                    label="New creator"
                                    placeholder="Select a member"
                                    :options="transferOptions"
                                    :error="transferForm.errors.user_id"
                                />
                                <UiCheckbox
                                    v-model="transferForm.confirm"
                                    label="I understand I will no longer be the creator."
                                />
                                <button type="submit" class="inline-flex h-11 items-center rounded-full border border-line bg-white px-6 text-sm font-semibold text-gray-800" :disabled="transferForm.processing">
                                    Transfer ownership
                                </button>
                            </form>
                        </div>

                        <div v-if="permissions.can_delete">
                            <h2 class="font-heading text-lg font-bold text-clay-800">Delete community</h2>
                            <p class="mt-2 text-sm text-gray-600">This cannot be undone. Type the community name to confirm.</p>
                            <form class="mt-4 space-y-4" @submit.prevent="destroy">
                                <UiTextField v-model="deleteForm.confirm_name" label="Community name" name="confirm_name" :error="deleteForm.errors.confirm_name" />
                                <button type="submit" class="inline-flex h-11 items-center rounded-full bg-clay-800 px-6 text-sm font-semibold text-white" :disabled="deleteForm.processing">
                                    Delete community
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </AppNav>
</template>

<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import AppNav from '@/components/AppNav.vue';
import UiCheckbox from '@/components/ui/UiCheckbox.vue';
import UiFileField from '@/components/ui/UiFileField.vue';
import UiRadio from '@/components/ui/UiRadio.vue';
import UiSelect from '@/components/ui/UiSelect.vue';
import UiTextarea from '@/components/ui/UiTextarea.vue';
import UiTextField from '@/components/ui/UiTextField.vue';

const props = defineProps<{
    section: string;
    community: {
        id: number;
        slug: string;
        name: string;
        description: string;
        access_mode: string;
        location_type: string;
        location_label: string | null;
        city: string | null;
        tag_keys: string[];
        guidelines: string | null;
        require_guidelines_accept: boolean;
        who_can_post: string;
        member_list_visibility: string;
        admin_digest_enabled: boolean;
        cover_url: string | null;
    };
    config: {
        tags: Record<string, string>;
        accessModes: Record<string, { label: string; description: string }>;
        locationTypes: Record<string, string>;
        guidelineTemplates: Array<{ label: string; body: string }>;
        whoCanPost: Record<string, string>;
        memberListVisibility: Record<string, string>;
    };
    permissions: {
        can_delete: boolean;
        can_transfer: boolean;
        can_invite_admin: boolean;
        can_remove_admin: boolean;
        can_approve: boolean;
        can_remove_member: boolean;
    };
    admins: Array<{ id: number; name: string; email: string; role: string; role_label: string }>;
    pendingInvites: Array<{ id: number; email: string; created_at: string | null }>;
    joinRequests: Array<{
        id: number;
        note: string | null;
        created_at: string | null;
        user: { id: number; name: string; email: string };
    }>;
    members: Array<{ id: number; name: string }>;
    transferCandidates: Array<{ id: number; name: string; role: string }>;
}>();

const page = usePage();
const flash = computed(() => (page.props.flash as { status?: string } | undefined));

const nav = [
    { key: 'general', label: 'General' },
    { key: 'access', label: 'Access & Privacy' },
    { key: 'guidelines', label: 'Guidelines' },
    { key: 'admins', label: 'Admins & Roles' },
    { key: 'moderation', label: 'Moderation' },
    { key: 'danger', label: 'Danger Zone' },
] as const;

type SectionKey = typeof nav[number]['key'];

const activeSection = ref<SectionKey>((props.section as SectionKey) || 'general');

function selectSection(key: SectionKey): void {
    if (activeSection.value === key) {
        return;
    }

    activeSection.value = key;
    window.history.replaceState(window.history.state, '', `/communities/${props.community.slug}/settings/${key}`);
}

const general = useForm({
    name: props.community.name,
    description: props.community.description,
    location_type: props.community.location_type,
    location_label: props.community.location_label ?? '',
    city: props.community.city ?? '',
    tag_keys: [...props.community.tag_keys],
    admin_digest_enabled: props.community.admin_digest_enabled,
    cover: null as File | null,
});

const access = useForm({
    access_mode: props.community.access_mode,
    who_can_post: props.community.who_can_post,
    member_list_visibility: props.community.member_list_visibility,
});

const guidelines = useForm({
    guidelines: props.community.guidelines ?? '',
    require_guidelines_accept: props.community.require_guidelines_accept,
});

const invite = useForm({ email: '' });
const transferForm = useForm({ user_id: '', confirm: false });
const deleteForm = useForm({ confirm_name: '' });
const coverPreview = ref<string | null>(null);

const transferOptions = computed(() =>
    props.transferCandidates.map((candidate) => ({
        value: String(candidate.id),
        label: candidate.name,
    })),
);

function toggleTag(key: string): void {
    if (general.tag_keys.includes(key)) {
        general.tag_keys = general.tag_keys.filter((item) => item !== key);
    } else {
        general.tag_keys = [...general.tag_keys, key];
    }
}

function onCover(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    general.cover = file;

    if (coverPreview.value) {
        URL.revokeObjectURL(coverPreview.value);
        coverPreview.value = null;
    }

    if (file) {
        coverPreview.value = URL.createObjectURL(file);
    }
}

function saveGeneralPut(): void {
    if (general.location_type === 'city') {
        general.city = general.location_label;
    }
    general.transform((data) => ({ ...data, _method: 'put' })).post(`/communities/${props.community.slug}/settings/general`, {
        forceFormData: true,
        preserveScroll: true,
    });
}

function saveAccess(): void {
    access.put(`/communities/${props.community.slug}/settings/access`, { preserveScroll: true });
}

function saveGuidelines(): void {
    guidelines.put(`/communities/${props.community.slug}/settings/guidelines`, { preserveScroll: true });
}

function insertGuideline(body: string): void {
    guidelines.guidelines = guidelines.guidelines ? `${guidelines.guidelines.trim()}\n\n${body}` : body;
}

function sendInvite(): void {
    invite.post(`/communities/${props.community.slug}/settings/admins/invite`, {
        preserveScroll: true,
        onSuccess: () => invite.reset('email'),
    });
}

function revokeInvite(id: number): void {
    router.delete(`/communities/${props.community.slug}/settings/admins/invites/${id}`, { preserveScroll: true });
}

function removeAdmin(id: number): void {
    if (confirm('Remove this admin? They will remain a member.')) {
        router.delete(`/communities/${props.community.slug}/settings/admins/${id}`, { preserveScroll: true });
    }
}

function approveRequest(id: number): void {
    router.post(`/communities/${props.community.slug}/settings/join-requests/${id}/approve`, {}, { preserveScroll: true });
}

function declineRequest(id: number): void {
    router.post(`/communities/${props.community.slug}/settings/join-requests/${id}/decline`, {}, { preserveScroll: true });
}

function removeMember(id: number): void {
    if (confirm('Remove this member from the community?')) {
        router.delete(`/communities/${props.community.slug}/settings/members/${id}`, { preserveScroll: true });
    }
}

function transfer(): void {
    transferForm.post(`/communities/${props.community.slug}/settings/transfer`);
}

function destroy(): void {
    deleteForm.delete(`/communities/${props.community.slug}`);
}
</script>
