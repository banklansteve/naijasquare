<template>
    <div class="min-h-dvh bg-white text-ink">
        <header
            class="sticky top-0 z-40 border-b border-line/80 bg-white/95 backdrop-blur-md transition-shadow duration-500 ease-out"
            :class="scrolled ? 'shadow-[0_10px_28px_-18px_rgb(12_44_49/0.35)]' : 'shadow-none'"
        >
            <div class="flex h-16 w-full items-center gap-4 px-5 pt-[env(safe-area-inset-top)] sm:gap-6 sm:px-8 lg:px-10">
                <Link href="/home" class="inline-flex shrink-0 items-center gap-2.5 rounded-full py-1 pr-1" aria-label="Home">
                    <AppMark class="size-9" />
                    <span class="font-heading text-base font-bold tracking-tight text-gray-800">{{ appName }}</span>
                </Link>

                <div class="ml-auto flex items-center gap-1 sm:gap-2">
                    <nav class="hidden items-center gap-1 md:flex lg:gap-2" aria-label="Primary">
                        <Link
                            v-for="item in primaryLinks"
                            :key="item.href"
                            :href="item.href"
                            class="rounded-full px-3.5 py-2 text-sm font-semibold transition-colors duration-300 ease-out lg:px-4"
                            :class="isActive(item.href)
                                ? 'bg-lagoon-50 text-lagoon-800'
                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                        >
                            {{ item.label }}
                        </Link>
                    </nav>

                    <div ref="profileWrap" class="relative hidden md:block">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2.5 rounded-full py-1 pr-2.5 pl-1 transition-colors duration-300 ease-out hover:bg-gray-50"
                            :class="profileOpen || isActive('/profile') ? 'bg-lagoon-50' : ''"
                            :aria-expanded="profileOpen"
                            aria-haspopup="menu"
                            @click="toggleProfile"
                        >
                            <span class="grid size-9 place-items-center overflow-hidden rounded-full bg-lagoon-100 text-lagoon-800 ring-1 ring-line">
                                <img
                                    v-if="user?.avatar_url"
                                    :src="user.avatar_url"
                                    :alt="user.name"
                                    class="size-full object-cover"
                                >
                                <BaseIcon v-else name="user" class="size-5" />
                            </span>
                            <span class="text-sm font-semibold text-gray-800">Profile</span>
                            <svg
                                class="size-4 shrink-0 text-ink-soft transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
                                :class="profileOpen ? 'rotate-180' : ''"
                                viewBox="0 0 20 20"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path d="M5 7.5 10 12.5 15 7.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <Transition
                            enter-active-class="transition duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
                            enter-from-class="translate-y-1 scale-95 opacity-0"
                            enter-to-class="translate-y-0 scale-100 opacity-100"
                            leave-active-class="transition duration-200 ease-in"
                            leave-from-class="translate-y-0 scale-100 opacity-100"
                            leave-to-class="translate-y-1 scale-95 opacity-0"
                        >
                            <div
                                v-if="profileOpen"
                                class="absolute top-full right-0 z-50 mt-2 w-56 origin-top-right overflow-hidden rounded-2xl border border-line bg-white py-2 shadow-[0_18px_40px_-24px_rgb(12_44_49/0.45)]"
                                role="menu"
                            >
                                <Link
                                    v-for="item in profileLinks"
                                    :key="item.href"
                                    :href="item.href"
                                    class="block px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors duration-200 hover:bg-lagoon-50 hover:text-lagoon-800"
                                    role="menuitem"
                                    @click="closeProfile"
                                >
                                    {{ item.label }}
                                </Link>
                                <div class="my-2 border-t border-line/80" />
                                <button
                                    type="button"
                                    class="block w-full px-4 py-2.5 text-left text-sm font-semibold text-clay-800 transition-colors duration-200 hover:bg-clay-50"
                                    role="menuitem"
                                    @click="signOut"
                                >
                                    Sign out
                                </button>
                            </div>
                        </Transition>
                    </div>

                    <button
                        type="button"
                        class="inline-flex size-11 items-center justify-center rounded-full border border-line bg-white text-gray-800 transition duration-300 ease-out hover:bg-gray-50 md:hidden"
                        :aria-expanded="menuOpen"
                        aria-controls="mobile-nav"
                        @click="openMenu"
                    >
                        <span class="sr-only">Open menu</span>
                        <BaseIcon name="menu" class="size-5" />
                    </button>
                </div>
            </div>
        </header>

        <Teleport to="body">
            <div
                class="fixed inset-0 z-50 md:hidden"
                :class="menuOpen ? 'pointer-events-auto' : 'pointer-events-none'"
            >
                <div
                    class="absolute inset-0 bg-ink/35 transition-opacity duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
                    :class="menuOpen ? 'opacity-100' : 'opacity-0'"
                    @click="closeMenu"
                />

                <aside
                    id="mobile-nav"
                    class="absolute inset-y-0 right-0 flex w-[min(20.5rem,88vw)] flex-col bg-white shadow-[-18px_0_48px_-28px_rgb(12_44_49/0.45)] transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
                    :class="menuOpen ? 'translate-x-0' : 'translate-x-full'"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Menu"
                >
                    <div class="flex h-16 items-center justify-between border-b border-line/80 px-5 pt-[env(safe-area-inset-top)]">
                        <p class="font-heading text-sm font-bold text-gray-800">Menu</p>
                        <button
                            type="button"
                            class="inline-flex size-10 items-center justify-center rounded-full text-gray-700 transition hover:bg-gray-50"
                            @click="closeMenu"
                        >
                            <span class="sr-only">Close menu</span>
                            <BaseIcon name="close" class="size-5" />
                        </button>
                    </div>

                    <div class="flex items-center gap-3 border-b border-line/80 px-5 py-5">
                        <span class="grid size-12 place-items-center overflow-hidden rounded-full bg-lagoon-100 text-lagoon-800 ring-1 ring-line">
                            <img
                                v-if="user?.avatar_url"
                                :src="user.avatar_url"
                                :alt="user?.name ?? 'Profile'"
                                class="size-full object-cover"
                            >
                            <BaseIcon v-else name="user" class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate font-heading text-sm font-bold text-gray-800">{{ user?.name }}</p>
                            <p class="truncate text-xs text-ink-soft">{{ user?.email }}</p>
                        </div>
                    </div>

                    <nav class="flex flex-1 flex-col gap-1 overflow-y-auto px-3 py-4" aria-label="Mobile">
                        <Link
                            v-for="item in mobileLinks"
                            :key="item.href"
                            :href="item.href"
                            class="rounded-2xl px-4 py-3 text-sm font-semibold transition-colors duration-300 ease-out"
                            :class="isActive(item.href)
                                ? 'bg-lagoon-50 text-lagoon-800'
                                : 'text-gray-700 hover:bg-gray-50'"
                            @click="closeMenu"
                        >
                            {{ item.label }}
                        </Link>
                    </nav>

                    <div class="border-t border-line/80 px-3 py-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
                        <button
                            type="button"
                            class="w-full rounded-2xl px-4 py-3 text-left text-sm font-semibold text-clay-800 transition-colors duration-300 ease-out hover:bg-clay-50"
                            @click="signOut"
                        >
                            Sign out
                        </button>
                    </div>
                </aside>
            </div>
        </Teleport>

        <slot />
    </div>
</template>

<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

import AppMark from '@/components/AppMark.vue';
import BaseIcon from '@/components/BaseIcon.vue';
import { appName } from '@/lib/app';

type AuthUser = {
    id: number;
    name: string;
    first_name: string;
    email: string;
    avatar_url: string | null;
};

const page = usePage();
const user = computed(() => (page.props.auth as { user: AuthUser | null } | undefined)?.user ?? null);
const scrolled = ref(false);
const menuOpen = ref(false);
const profileOpen = ref(false);
const profileWrap = ref<HTMLElement | null>(null);

const primaryLinks = [
    { href: '/communities', label: 'Communities' },
    { href: '/needs', label: 'Needs' },
    { href: '/resources', label: 'Resources' },
];

const profileLinks = [
    { href: '/profile', label: 'Profile & settings' },
    { href: '/communities', label: 'My communities' },
    { href: '/communities/create', label: 'Create a community' },
];

const mobileLinks = [
    ...primaryLinks,
    ...profileLinks,
];

function isActive(href: string): boolean {
    const url = page.url.split('?')[0] ?? '';

    return url === href || url.startsWith(`${href}/`);
}

function onScroll(): void {
    scrolled.value = window.scrollY > 8;
}

function openMenu(): void {
    profileOpen.value = false;
    menuOpen.value = true;
}

function closeMenu(): void {
    menuOpen.value = false;
}

function toggleProfile(): void {
    profileOpen.value = !profileOpen.value;
}

function closeProfile(): void {
    profileOpen.value = false;
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        closeMenu();
        closeProfile();
    }
}

function onDocumentClick(event: MouseEvent): void {
    if (!profileOpen.value || !profileWrap.value) {
        return;
    }

    if (!profileWrap.value.contains(event.target as Node)) {
        closeProfile();
    }
}

function signOut(): void {
    closeMenu();
    closeProfile();
    router.post('/sign-out');
}

watch(menuOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('keydown', onKeydown);
    document.addEventListener('click', onDocumentClick);
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('keydown', onKeydown);
    document.removeEventListener('click', onDocumentClick);
    document.body.style.overflow = '';
});
</script>
