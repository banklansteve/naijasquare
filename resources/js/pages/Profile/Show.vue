<template>
    <Head title="Profile & settings" />

    <AppNav>
        <main class="mx-auto w-full max-w-5xl overflow-x-clip px-5 pt-7 pb-[max(3.5rem,env(safe-area-inset-bottom))] sm:px-8 sm:pt-10 lg:pt-12">
            <header class="mb-6 sm:mb-8 lg:mb-10">
                <p class="text-sm font-medium text-ink-soft">Your account</p>
                <h1 class="mt-1 font-heading text-3xl font-bold tracking-tight text-ink sm:text-[2.1rem]">
                    Profile & settings
                </h1>
                <p class="mt-2 max-w-xl text-sm leading-6 text-ink-soft">
                    How you appear, what you share, and how you sign in.
                </p>
            </header>

            <div class="lg:grid lg:grid-cols-[13.5rem_minmax(0,1fr)] lg:items-start lg:gap-10 xl:gap-12">
                <div class="mb-6 lg:hidden" role="tablist" aria-label="Profile sections">
                    <div class="grid grid-cols-3 gap-1 rounded-2xl border border-line bg-white p-1 shadow-card">
                        <button
                            v-for="tab in tabs"
                            :id="`tab-${tab.key}`"
                            :key="tab.key"
                            type="button"
                            role="tab"
                            class="min-w-0 rounded-[0.9rem] px-1.5 py-2.5 text-center text-[13px] font-semibold transition-[color,background-color,box-shadow] duration-300 ease-out sm:text-sm"
                            :class="activeTab === tab.key
                                ? 'bg-lagoon-800 text-white shadow-sm'
                                : 'text-ink-soft hover:bg-lagoon-50 hover:text-ink'"
                            :aria-selected="activeTab === tab.key"
                            :tabindex="activeTab === tab.key ? 0 : -1"
                            @click="switchTab(tab.key)"
                        >
                            {{ tab.label }}
                        </button>
                    </div>
                </div>

                <nav class="mb-8 hidden lg:sticky lg:top-24 lg:mb-0 lg:flex lg:flex-col lg:gap-2" aria-label="Profile sections">
                    <button
                        v-for="tab in tabs"
                        :key="`desk-${tab.key}`"
                        type="button"
                        class="rounded-2xl px-4 py-2.5 text-left text-sm font-semibold transition-colors duration-300 ease-out"
                        :class="activeTab === tab.key
                            ? 'bg-lagoon-800 text-white shadow-sm'
                            : 'border border-line bg-white text-ink-soft hover:border-lagoon-300 hover:text-ink'"
                        @click="switchTab(tab.key)"
                    >
                        {{ tab.label }}
                    </button>
                </nav>

                <div class="min-w-0 min-h-[28rem] [overflow-anchor:none]">
                    <Transition
                        mode="out-in"
                        enter-active-class="transition duration-200 ease-out motion-reduce:transition-none"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="transition duration-150 ease-in motion-reduce:transition-none"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <!-- PROFILE -->
                        <div
                            v-if="activeTab === 'profile'"
                            key="profile"
                            class="space-y-6 sm:space-y-8"
                            role="tabpanel"
                        >
                            <section class="rounded-[1.5rem] border border-line/80 bg-white p-5 shadow-card sm:p-7">
                                <h2 class="font-heading text-xl font-bold tracking-tight text-ink">Profile photo</h2>
                                <div class="mt-5 flex flex-wrap items-center gap-4">
                                    <span class="grid size-20 place-items-center overflow-hidden rounded-[1.35rem] bg-lagoon-100 text-lagoon-800 ring-1 ring-line">
                                        <img
                                            v-if="avatarPreview || profile.avatar_url"
                                            :src="avatarPreview ?? profile.avatar_url ?? undefined"
                                            :alt="profile.name"
                                            class="size-full object-cover"
                                        >
                                        <BaseIcon v-else name="user" class="size-8" />
                                    </span>
                                    <div class="flex flex-wrap gap-2">
                                        <label class="inline-flex h-10 cursor-pointer items-center rounded-full border border-line bg-white px-4 text-sm font-semibold text-ink transition hover:border-lagoon-300">
                                            {{ profile.avatar_url || avatarPreview ? 'Replace' : 'Add photo' }}
                                            <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="onPhotoChange">
                                        </label>
                                        <button
                                            v-if="profile.avatar_url"
                                            type="button"
                                            class="inline-flex h-10 items-center rounded-full border border-line bg-white px-4 text-sm font-semibold text-ink-soft hover:text-clay-800"
                                            @click="removePhoto"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                                <p v-if="photoForm.errors.photo" class="mt-2 text-sm text-clay-800">{{ photoForm.errors.photo }}</p>
                            </section>

                            <section class="overflow-hidden rounded-[1.5rem] border border-line/80 bg-white shadow-card">
                                <div class="flex items-start justify-between gap-3 px-5 pt-5 sm:px-7 sm:pt-6">
                                    <div class="min-w-0">
                                        <h2 class="font-heading text-xl font-bold tracking-tight text-ink">About you</h2>
                                        <p class="mt-1 text-sm leading-6 text-ink-soft">
                                            How you introduce yourself in your communities.
                                        </p>
                                    </div>
                                    <button
                                        v-if="! editingProfile"
                                        type="button"
                                        class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-full border border-line bg-white px-3.5 text-sm font-semibold text-ink transition hover:border-lagoon-300 hover:bg-lagoon-50/60"
                                        @click="editingProfile = true"
                                    >
                                        <BaseIcon name="pencil" class="size-3.5" />
                                        Edit
                                    </button>
                                </div>

                                <div v-if="! editingProfile" class="px-5 pt-5 pb-5 sm:px-7 sm:pb-7">
                                    <div class="rounded-[1.35rem] bg-gradient-to-br from-lagoon-50 via-paper to-honey-50/60 p-5 sm:p-6">
                                        <p class="font-heading text-2xl font-bold tracking-tight text-balance text-ink sm:text-[1.75rem]">
                                            {{ profile.name }}
                                        </p>
                                        <p
                                            v-if="profile.member_since"
                                            class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-white/80 px-2.5 py-1 text-xs font-semibold text-lagoon-800 ring-1 ring-lagoon-100"
                                        >
                                            <BaseIcon name="sparkles" class="size-3.5" />
                                            Member since {{ profile.member_since }}
                                        </p>
                                        <div class="mt-5 rounded-2xl border border-white/80 bg-white/75 px-4 py-4 shadow-card sm:px-5">
                                            <p class="text-xs font-semibold tracking-[0.14em] text-lagoon-700 uppercase">Bio</p>
                                            <p v-if="profile.bio" class="mt-2 text-base leading-7 text-ink">{{ profile.bio }}</p>
                                            <p v-else class="mt-2 text-sm leading-6 text-ink-soft">
                                                A few lines about you help people know who they are connecting with.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                        <div class="rounded-2xl border border-line/80 bg-paper/70 p-4">
                                            <div class="flex items-center gap-2.5">
                                                <span class="grid size-9 place-items-center rounded-xl bg-lagoon-100 text-lagoon-800">
                                                    <BaseIcon name="phone" class="size-4" />
                                                </span>
                                                <p class="text-xs font-semibold tracking-wide text-ink-soft uppercase">Phone</p>
                                            </div>
                                            <p class="mt-3 text-sm font-semibold text-ink">{{ profile.phone || 'Not added' }}</p>
                                        </div>
                                        <div class="rounded-2xl border border-line/80 bg-paper/70 p-4">
                                            <div class="flex items-center gap-2.5">
                                                <span class="grid size-9 place-items-center rounded-xl bg-lagoon-100 text-lagoon-800">
                                                    <BaseIcon name="pin" class="size-4" />
                                                </span>
                                                <p class="text-xs font-semibold tracking-wide text-ink-soft uppercase">Area</p>
                                            </div>
                                            <p class="mt-3 text-sm font-semibold leading-6 text-ink">
                                                {{ profile.display_location || profile.place_label || profile.city || 'Not set' }}
                                            </p>
                                            <p class="mt-0.5 text-sm text-ink-soft">
                                                {{ profile.postcode || 'No postcode yet' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <Transition
                                    enter-active-class="transition duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none"
                                    enter-from-class="-translate-y-1 opacity-0"
                                    enter-to-class="translate-y-0 opacity-100"
                                    leave-active-class="transition duration-200 ease-out motion-reduce:transition-none"
                                    leave-from-class="opacity-100"
                                    leave-to-class="opacity-0"
                                >
                                    <form
                                        v-if="editingProfile"
                                        class="space-y-5 border-t border-line/70 px-5 pt-5 pb-5 sm:px-7 sm:pb-7"
                                        @submit.prevent="saveProfile"
                                    >
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <UiTextField v-model="profileForm.first_name" label="First name" name="first_name" :error="profileForm.errors.first_name" />
                                            <UiTextField v-model="profileForm.last_name" label="Last name" name="last_name" :error="profileForm.errors.last_name" />
                                        </div>
                                        <UiTextarea
                                            v-model="profileForm.bio"
                                            label="Bio / about"
                                            name="bio"
                                            optional
                                            :rows="3"
                                            autoresize
                                            :error="profileForm.errors.bio"
                                            placeholder="A short line about you"
                                        />
                                        <UiTextField
                                            v-model="profileForm.phone"
                                            label="Phone number"
                                            name="phone"
                                            optional
                                            autocomplete="tel"
                                            :error="profileForm.errors.phone"
                                            hint="Optional UK mobile."
                                            placeholder="07…"
                                        />
                                        <UiTextField
                                            v-model="profileForm.postcode"
                                            label="Postcode"
                                            name="postcode"
                                            :error="profileForm.errors.postcode"
                                            hint="Changing postcode updates your area from your saved house number. City and area are filled automatically — not typed by hand."
                                            placeholder="e.g. SR5 3AA"
                                        />
                                        <div class="flex flex-wrap gap-2">
                                            <button
                                                type="submit"
                                                class="inline-flex h-11 items-center rounded-full bg-lagoon-800 px-6 text-sm font-semibold text-white disabled:opacity-60"
                                                :disabled="profileForm.processing"
                                            >
                                                Save
                                            </button>
                                            <button
                                                type="button"
                                                class="inline-flex h-11 items-center rounded-full border border-line bg-white px-5 text-sm font-semibold text-ink"
                                                @click="cancelProfileEdit"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </Transition>
                            </section>

                            <section class="rounded-[1.5rem] border border-line/80 bg-white p-5 shadow-card sm:p-7">
                                <h2 class="font-heading text-xl font-bold tracking-tight text-ink">How your area is shown</h2>
                                <p class="mt-1.5 text-sm leading-6 text-ink-soft">
                                    House number and full address stay private. Choose what others see.
                                </p>
                                <form class="mt-5 space-y-3" @submit.prevent="saveLocationDisplay">
                                    <UiRadio v-model="locationDisplayForm.location_display" value="place" name="location_display" label="Exact area" :description="profile.place_label || 'Neighbourhood and city when set'" card />
                                    <UiRadio v-model="locationDisplayForm.location_display" value="city" name="location_display" label="City only" :description="profile.city || 'City name only'" card />
                                    <UiRadio v-model="locationDisplayForm.location_display" value="hidden" name="location_display" label="Hidden" description="Don’t show a location" card />
                                    <button
                                        type="submit"
                                        class="mt-2 inline-flex h-11 items-center rounded-full bg-lagoon-800 px-6 text-sm font-semibold text-white disabled:opacity-60"
                                        :disabled="locationDisplayForm.processing"
                                    >
                                        Save visibility
                                    </button>
                                </form>
                            </section>

                            <section class="rounded-[1.5rem] border border-line/80 bg-white p-5 shadow-card sm:p-7">
                                <h2 class="font-heading text-xl font-bold tracking-tight text-ink">What I can help with</h2>
                                <p class="mt-1.5 text-sm leading-6 text-ink-soft">Skills and offers used to match you with relevant Needs.</p>
                                <form class="mt-6 space-y-5" @submit.prevent="saveHelpWith">
                                    <div class="flex flex-wrap gap-2">
                                        <label
                                            v-for="option in visibleHelpOptions"
                                            :key="option.key"
                                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-full border px-3.5 py-2 text-sm transition"
                                            :class="helpSelected.includes(option.key)
                                                ? 'border-lagoon-700 bg-lagoon-100 font-semibold text-lagoon-900'
                                                : 'border-line bg-paper text-ink-soft hover:border-lagoon-300'"
                                        >
                                            <input v-model="helpSelected" type="checkbox" class="sr-only" :value="option.key">
                                            {{ option.label }}
                                        </label>
                                        <button
                                            v-if="hasMoreHelp"
                                            type="button"
                                            class="rounded-full border border-dashed border-lagoon-300 px-3.5 py-2 text-sm font-semibold text-lagoon-800"
                                            @click="showAllHelp = !showAllHelp"
                                        >
                                            {{ showAllHelp ? 'Show fewer' : `More (${hiddenHelpCount})` }}
                                        </button>
                                    </div>
                                    <p v-if="helpForm.errors.help_category_keys" class="text-sm text-clay-800">{{ helpForm.errors.help_category_keys }}</p>
                                    <UiTextField v-model="helpForm.help_note" label="Short note" name="help_note" optional :error="helpForm.errors.help_note" placeholder="e.g. CV reviews, housing in Leeds" />
                                    <button type="submit" class="inline-flex h-11 items-center rounded-full bg-lagoon-800 px-6 text-sm font-semibold text-white" :disabled="helpForm.processing || helpSelected.length === 0">
                                        Save help preferences
                                    </button>
                                </form>
                            </section>
                        </div>

                        <!-- PRIVACY -->
                        <div
                            v-else-if="activeTab === 'privacy'"
                            key="privacy"
                            class="space-y-6 sm:space-y-8"
                            role="tabpanel"
                        >
                            <form class="space-y-6 sm:space-y-8" @submit.prevent="savePrivacy">
                                <section class="rounded-[1.5rem] border border-line/80 bg-white p-5 shadow-card sm:p-7 space-y-5">
                                    <div>
                                        <h2 class="font-heading text-xl font-bold tracking-tight text-ink">Privacy</h2>
                                        <p class="mt-1.5 text-sm leading-6 text-ink-soft">
                                            Controls what community members can see of you.
                                        </p>
                                    </div>
                                    <fieldset>
                                        <legend class="text-sm font-semibold text-ink">Who can see your profile</legend>
                                        <div class="mt-3 space-y-2">
                                            <UiRadio v-model="privacyForm.profile_visibility" value="communities" name="profile_visibility" label="Communities I belong to" description="Bio and about details can appear in community context" card />
                                            <UiRadio v-model="privacyForm.profile_visibility" value="private" name="profile_visibility" label="Only me" description="Keep bio private; your name still shows on posts you write" card />
                                        </div>
                                    </fieldset>
                                    <fieldset>
                                        <legend class="text-sm font-semibold text-ink">Who can see your location / area</legend>
                                        <div class="mt-3 space-y-2">
                                            <UiRadio v-model="privacyForm.location_display" value="place" name="privacy_location_display" label="Exact area" description="Neighbourhood and city" card />
                                            <UiRadio v-model="privacyForm.location_display" value="city" name="privacy_location_display" label="City only" description="City name only" card />
                                            <UiRadio v-model="privacyForm.location_display" value="hidden" name="privacy_location_display" label="Hidden" description="Don’t show location" card />
                                        </div>
                                    </fieldset>
                                    <fieldset>
                                        <legend class="text-sm font-semibold text-ink">Skills / offers visibility</legend>
                                        <div class="mt-3 space-y-2">
                                            <UiRadio v-model="privacyForm.help_visibility" value="communities" name="help_visibility" label="Visible in my communities" description="Members can see what you can help with when relevant" card />
                                            <UiRadio v-model="privacyForm.help_visibility" value="private" name="help_visibility" label="Matching only" description="Still used to surface Needs for you — not listed for others" card />
                                        </div>
                                    </fieldset>
                                </section>

                                <section class="rounded-[1.5rem] border border-line/80 bg-white p-5 shadow-card sm:p-7 space-y-4">
                                    <div>
                                        <h2 class="font-heading text-xl font-bold tracking-tight text-ink">Notifications</h2>
                                        <p class="mt-1.5 text-sm leading-6 text-ink-soft">Choose which in-app alerts you want.</p>
                                    </div>
                                    <UiCheckbox v-model="privacyForm.notify_needs_replies" card label="Needs & replies — related Needs you can help with, and activity on your posts" />
                                    <UiCheckbox v-model="privacyForm.notify_community" card label="Community activity — invites and community updates" />
                                    <UiCheckbox v-model="privacyForm.notify_admin" card label="Admin / system messages — admin invites and important account notices" />
                                </section>

                                <section class="rounded-[1.5rem] border border-line/80 bg-white p-5 shadow-card sm:p-7 space-y-4">
                                    <div>
                                        <h2 class="font-heading text-xl font-bold tracking-tight text-ink">Data preferences</h2>
                                        <p class="mt-1.5 text-sm leading-6 text-ink-soft">Optional. You can change this any time.</p>
                                    </div>
                                    <UiCheckbox v-model="privacyForm.prefer_product_emails" card label="Occasional product updates by email (never sold, never spammy)" />
                                </section>

                                <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-full bg-lagoon-800 px-6 text-sm font-semibold text-white sm:w-auto" :disabled="privacyForm.processing">
                                    Save privacy & notifications
                                </button>
                            </form>
                        </div>

                        <!-- ACCOUNT (last) -->
                        <div
                            v-else
                            key="account"
                            class="space-y-6 sm:space-y-8"
                            role="tabpanel"
                        >
                            <section class="rounded-[1.5rem] border border-line/80 bg-white p-5 shadow-card sm:p-7">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h2 class="font-heading text-xl font-bold tracking-tight text-ink">Email address</h2>
                                        <p class="mt-1 break-all text-sm font-medium text-ink">{{ profile.email }}</p>
                                    </div>
                                    <button
                                        v-if="! editingEmail"
                                        type="button"
                                        class="inline-flex h-9 shrink-0 items-center rounded-full border border-line bg-white px-3.5 text-sm font-semibold"
                                        @click="editingEmail = true"
                                    >
                                        Edit
                                    </button>
                                </div>
                                <Transition
                                    enter-active-class="transition duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none"
                                    enter-from-class="-translate-y-1 opacity-0"
                                    enter-to-class="translate-y-0 opacity-100"
                                >
                                    <form v-if="editingEmail" class="mt-5 space-y-4 border-t border-line/70 pt-5" @submit.prevent="saveEmail">
                                        <UiTextField v-model="emailForm.email" label="Email address" name="email" type="email" :error="emailForm.errors.email" />
                                        <UiTextField v-model="emailForm.current_password" label="Confirm with password" name="current_password" type="password" :error="emailForm.errors.current_password" />
                                        <p class="text-sm leading-6 text-ink-soft">We’ll send a verification code to the new address.</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="submit" class="inline-flex h-10 items-center rounded-full bg-lagoon-800 px-5 text-sm font-semibold text-white" :disabled="emailForm.processing">Update email</button>
                                            <button type="button" class="inline-flex h-10 items-center rounded-full border border-line bg-white px-4 text-sm font-semibold" @click="cancelEmailEdit">Cancel</button>
                                        </div>
                                    </form>
                                </Transition>
                            </section>

                            <section class="rounded-[1.5rem] border border-line/80 bg-white p-5 shadow-card sm:p-7">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h2 class="font-heading text-xl font-bold tracking-tight text-ink">Password</h2>
                                        <p
                                            v-if="! passwordOpen"
                                            class="mt-1.5 select-none text-sm font-medium tracking-[0.28em] text-ink"
                                            aria-hidden="true"
                                        >
                                            ••••••••
                                        </p>
                                        <p v-if="! passwordOpen" class="sr-only">Password is set.</p>
                                    </div>
                                    <button
                                        v-if="! passwordOpen"
                                        type="button"
                                        class="inline-flex h-9 shrink-0 items-center rounded-full border border-line bg-white px-3.5 text-sm font-semibold"
                                        @click="passwordOpen = true"
                                    >
                                        Change password
                                    </button>
                                </div>
                                <Transition
                                    enter-active-class="transition duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none"
                                    enter-from-class="-translate-y-1 opacity-0"
                                    enter-to-class="translate-y-0 opacity-100"
                                >
                                    <form v-if="passwordOpen" class="mt-5 space-y-4 border-t border-line/70 pt-5" @submit.prevent="savePassword">
                                        <UiTextField
                                            v-model="passwordForm.current_password"
                                            label="Current password"
                                            name="current_password"
                                            type="password"
                                            autocomplete="current-password"
                                            :error="currentPasswordError || passwordForm.errors.current_password"
                                            @focusout="verifyCurrentPassword"
                                        />
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <UiTextField
                                                v-model="passwordForm.password"
                                                label="New password"
                                                name="password"
                                                type="password"
                                                autocomplete="new-password"
                                                :error="passwordLengthError || passwordForm.errors.password"
                                                hint="At least 8 characters."
                                            />
                                            <UiTextField
                                                v-model="passwordForm.password_confirmation"
                                                label="Confirm new password"
                                                name="password_confirmation"
                                                type="password"
                                                autocomplete="new-password"
                                                :error="passwordConfirmError || passwordForm.errors.password_confirmation"
                                            />
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <button
                                                type="submit"
                                                class="inline-flex h-10 items-center rounded-full bg-lagoon-800 px-5 text-sm font-semibold text-white disabled:opacity-60"
                                                :disabled="! passwordCanSubmit"
                                            >
                                                Update password
                                            </button>
                                            <button type="button" class="inline-flex h-10 items-center rounded-full border border-line bg-white px-4 text-sm font-semibold" @click="cancelPasswordChange">Cancel</button>
                                        </div>
                                    </form>
                                </Transition>
                            </section>

                            <section class="rounded-[1.5rem] border border-clay-200/80 bg-clay-50/40 p-5 sm:p-7">
                                <h2 class="font-heading text-xl font-bold tracking-tight text-clay-800">Delete account</h2>
                                <div class="mt-2 space-y-2 text-sm leading-6 text-ink-soft">
                                    <p>This permanently deletes your account and signs you out.</p>
                                    <p>The action is destructive and cannot be undone. You will lose access to your profile, settings, and memberships.</p>
                                    <p>Only continue if you are sure you want to leave for good.</p>
                                </div>
                                <form v-if="deleteOpen" class="mt-6 space-y-4 rounded-2xl border border-clay-200 bg-white p-4" @submit.prevent="deleteAccount">
                                    <UiTextField v-model="deleteForm.current_password" label="Password" name="current_password" type="password" :error="deleteForm.errors.current_password" />
                                    <UiTextField v-model="deleteForm.confirmation" label="Type DELETE to confirm" name="confirmation" :error="deleteForm.errors.confirmation" />
                                    <div class="flex flex-wrap gap-2">
                                        <button type="submit" class="inline-flex h-11 items-center rounded-full bg-clay-800 px-6 text-sm font-semibold text-white" :disabled="deleteForm.processing">Delete my account</button>
                                        <button type="button" class="inline-flex h-11 items-center rounded-full border border-line bg-white px-5 text-sm font-semibold" @click="deleteOpen = false">Cancel</button>
                                    </div>
                                </form>
                                <button
                                    v-else
                                    type="button"
                                    class="mt-6 inline-flex h-11 items-center rounded-full border border-clay-200 bg-white px-5 text-sm font-semibold text-clay-800"
                                    @click="deleteOpen = true"
                                >
                                    Continue to delete account
                                </button>
                            </section>

                        </div>
                    </Transition>
                </div>
            </div>
        </main>

        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none"
                enter-from-class="translate-y-3 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-200 ease-in motion-reduce:transition-none"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="translate-y-2 opacity-0"
            >
                <div
                    v-if="toastMessage"
                    class="pointer-events-none fixed inset-x-0 bottom-[max(1.25rem,env(safe-area-inset-bottom))] z-[80] flex justify-center px-4"
                    role="status"
                    aria-live="polite"
                >
                    <div class="pointer-events-auto flex max-w-md items-center gap-3 rounded-2xl border border-lagoon-200 bg-lagoon-900 px-4 py-3 text-sm font-medium text-white shadow-lift">
                        <BaseIcon name="check" class="size-4 shrink-0 text-lagoon-200" />
                        <span>{{ toastMessage }}</span>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppNav>
</template>

<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onUnmounted, ref, watch } from 'vue';

import AppNav from '@/components/AppNav.vue';
import BaseIcon from '@/components/BaseIcon.vue';
import UiCheckbox from '@/components/ui/UiCheckbox.vue';
import UiRadio from '@/components/ui/UiRadio.vue';
import UiTextarea from '@/components/ui/UiTextarea.vue';
import UiTextField from '@/components/ui/UiTextField.vue';
import { primaryHelpOptions } from '@/lib/helpCategories';

type HelpOption = { key: string; label: string };
type TabKey = 'profile' | 'privacy' | 'account';

const props = defineProps<{
    profile: {
        name: string;
        first_name: string;
        last_name: string;
        email: string;
        phone: string | null;
        bio: string | null;
        city: string | null;
        place_label: string | null;
        postcode: string | null;
        location_display: 'place' | 'city' | 'hidden';
        display_location: string | null;
        profile_visibility: 'communities' | 'private';
        help_visibility: 'communities' | 'private';
        avatar_url: string | null;
        email_verified: boolean;
        help_category_keys: string[];
        help_note: string | null;
        notify_needs_replies: boolean;
        notify_community: boolean;
        notify_admin: boolean;
        prefer_product_emails: boolean;
        member_since: string | null;
    };
    helpCategories: HelpOption[];
}>();

const page = usePage();
const toastMessage = ref<string | null>(null);
let toastTimer: ReturnType<typeof setTimeout> | null = null;

const tabs: Array<{ key: TabKey; label: string }> = [
    { key: 'profile', label: 'Profile' },
    { key: 'privacy', label: 'Privacy' },
    { key: 'account', label: 'Account' },
];

const activeTab = ref<TabKey>('profile');
const editingProfile = ref(false);
const editingEmail = ref(false);
const passwordOpen = ref(false);
const deleteOpen = ref(false);
const avatarPreview = ref<string | null>(null);
const showAllHelp = ref(false);
const helpSelected = ref<string[]>([...props.profile.help_category_keys]);
const currentPasswordError = ref<string | null>(null);
const verifyingCurrentPassword = ref(false);

const curatedHelp = computed(() => primaryHelpOptions(props.helpCategories, helpSelected.value));
const hiddenHelpCount = computed(() => props.helpCategories.length - curatedHelp.value.length);
const hasMoreHelp = computed(() => hiddenHelpCount.value > 0);
const visibleHelpOptions = computed(() => (showAllHelp.value ? props.helpCategories : curatedHelp.value));

const profileForm = useForm({
    first_name: props.profile.first_name ?? '',
    last_name: props.profile.last_name ?? '',
    bio: props.profile.bio ?? '',
    phone: props.profile.phone ?? '',
    postcode: props.profile.postcode ?? '',
});

const locationDisplayForm = useForm({
    location_display: props.profile.location_display ?? 'place',
});

const helpForm = useForm({
    help_category_keys: [...props.profile.help_category_keys] as string[],
    help_note: props.profile.help_note ?? '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const passwordLengthError = computed(() => {
    const next = passwordForm.password;

    if (next === '' || next.length >= 8) {
        return null;
    }

    return 'Use at least 8 characters for your new password.';
});

const passwordConfirmError = computed(() => {
    const next = passwordForm.password;
    const confirm = passwordForm.password_confirmation;

    if (confirm === '' || next === '') {
        return null;
    }

    if (next !== confirm) {
        return 'New password and confirmation do not match.';
    }

    return null;
});

const passwordCanSubmit = computed(() => {
    const current = passwordForm.current_password.trim();
    const next = passwordForm.password;
    const confirm = passwordForm.password_confirmation;

    if (passwordForm.processing || verifyingCurrentPassword.value) {
        return false;
    }

    if (current === '' || next === '' || confirm === '') {
        return false;
    }

    if (next.length < 8 || next !== confirm) {
        return false;
    }

    if (currentPasswordError.value !== null) {
        return false;
    }

    return true;
});

const emailForm = useForm({
    email: props.profile.email,
    current_password: '',
});

const photoForm = useForm<{ photo: File | null }>({ photo: null });

const deleteForm = useForm({
    current_password: '',
    confirmation: '',
});

const privacyForm = useForm({
    profile_visibility: props.profile.profile_visibility ?? 'communities',
    help_visibility: props.profile.help_visibility ?? 'communities',
    location_display: props.profile.location_display ?? 'place',
    notify_needs_replies: props.profile.notify_needs_replies,
    notify_community: props.profile.notify_community,
    notify_admin: props.profile.notify_admin,
    prefer_product_emails: props.profile.prefer_product_emails,
});

watch(helpSelected, (keys) => {
    helpForm.help_category_keys = [...keys];
});

watch(
    () => passwordForm.current_password,
    () => {
        currentPasswordError.value = null;
    },
);

watch(
    () => locationDisplayForm.location_display,
    (value) => {
        privacyForm.location_display = value;
    },
);

watch(
    () => privacyForm.location_display,
    (value) => {
        locationDisplayForm.location_display = value;
    },
);

watch(
    () => (page.props.flash as { status?: string } | undefined)?.status,
    (status) => {
        if (typeof status === 'string' && status.trim() !== '') {
            showToast(status);
        }
    },
    { immediate: true },
);

function showToast(message: string): void {
    toastMessage.value = message;
    if (toastTimer !== null) {
        clearTimeout(toastTimer);
    }
    toastTimer = setTimeout(() => {
        toastMessage.value = null;
        toastTimer = null;
    }, 3200);
}

function switchTab(key: TabKey): void {
    if (activeTab.value === key) {
        return;
    }
    const y = window.scrollY;
    activeTab.value = key;
    nextTick(() => {
        window.scrollTo(0, y);
        requestAnimationFrame(() => window.scrollTo(0, y));
    });
}

function saveProfile(): void {
    profileForm.put('/profile', {
        preserveScroll: true,
        onSuccess: () => {
            editingProfile.value = false;
        },
    });
}

function cancelProfileEdit(): void {
    editingProfile.value = false;
    profileForm.first_name = props.profile.first_name ?? '';
    profileForm.last_name = props.profile.last_name ?? '';
    profileForm.bio = props.profile.bio ?? '';
    profileForm.phone = props.profile.phone ?? '';
    profileForm.postcode = props.profile.postcode ?? '';
    profileForm.clearErrors();
}

function saveLocationDisplay(): void {
    locationDisplayForm.put('/profile/location-display', { preserveScroll: true });
}

function saveHelpWith(): void {
    helpForm.help_category_keys = [...helpSelected.value];
    helpForm.put('/profile/help-with', { preserveScroll: true });
}

function csrfToken(): string {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

async function verifyCurrentPassword(): Promise<void> {
    const value = passwordForm.current_password.trim();

    if (value === '' || verifyingCurrentPassword.value) {
        return;
    }

    verifyingCurrentPassword.value = true;
    currentPasswordError.value = null;
    passwordForm.clearErrors('current_password');

    try {
        const response = await fetch('/profile/password/confirm', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ current_password: value }),
        });

        if (response.ok) {
            currentPasswordError.value = null;

            return;
        }

        if (response.status === 422) {
            const payload = await response.json() as { errors?: { current_password?: string[] } };
            currentPasswordError.value = payload.errors?.current_password?.[0]
                ?? 'That password does not match our records.';

            return;
        }

        currentPasswordError.value = 'We could not check that password just now.';
    } catch {
        currentPasswordError.value = 'We could not check that password just now.';
    } finally {
        verifyingCurrentPassword.value = false;
    }
}

function savePassword(): void {
    if (! passwordCanSubmit.value) {
        return;
    }

    passwordForm.put('/profile/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset('current_password', 'password', 'password_confirmation');
            currentPasswordError.value = null;
            passwordOpen.value = false;
        },
    });
}

function cancelPasswordChange(): void {
    passwordOpen.value = false;
    passwordForm.clearErrors();
    passwordForm.reset('current_password', 'password', 'password_confirmation');
    currentPasswordError.value = null;
}

function saveEmail(): void {
    emailForm.put('/profile/email');
}

function cancelEmailEdit(): void {
    editingEmail.value = false;
    emailForm.email = props.profile.email;
    emailForm.current_password = '';
    emailForm.clearErrors();
}

function savePrivacy(): void {
    privacyForm.put('/profile/privacy', { preserveScroll: true });
}

function onPhotoChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    if (! file) {
        return;
    }
    avatarPreview.value = URL.createObjectURL(file);
    photoForm.photo = file;
    photoForm.post('/profile/photo', {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => {
            photoForm.reset('photo');
            input.value = '';
        },
    });
}

function removePhoto(): void {
    router.delete('/profile/photo', { preserveScroll: true });
    avatarPreview.value = null;
}

function deleteAccount(): void {
    deleteForm.delete('/profile');
}

onUnmounted(() => {
    if (toastTimer !== null) {
        clearTimeout(toastTimer);
    }
});
</script>
