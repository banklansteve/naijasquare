<template>
    <div class="px-1 py-2">
        <article
            class="relative rounded-sm bg-[#fffdf8] px-3.5 pt-4 pb-3.5 shadow-card ring-1 ring-[#e6dfd2] sm:px-4 sm:pt-5 sm:pb-4"
            :class="tilt"
            aria-hidden="true"
        >
            <span
                class="absolute -top-1.5 left-1/2 size-3 -translate-x-1/2 rounded-full bg-lagoon-800 shadow-[0_2px_0_rgb(20_66_72_/_0.25)] ring-4 ring-[#fffdf8]"
            />

            <template v-if="screen.type === 'profile'">
                <p class="text-xs font-semibold tracking-[0.14em] text-lagoon-800 uppercase">
                    {{ screen.label }}
                </p>
                <div class="mt-3 flex items-center justify-between gap-3 rounded-xl bg-white px-3 py-2.5 ring-1 ring-[#ebe4d6]">
                    <p class="text-xs text-ink-soft">{{ screen.nameLabel }}</p>
                    <p class="text-sm font-semibold text-ink">{{ screen.nameValue }}</p>
                </div>
                <ul class="mt-3 flex flex-wrap gap-1.5">
                    <li
                        v-for="interest in screen.interests"
                        :key="interest.name"
                        class="rounded-full px-2.5 py-1 text-xs font-semibold"
                        :class="interest.selected ? 'bg-lagoon-50 text-lagoon-900' : 'bg-white text-ink-soft ring-1 ring-[#ebe4d6]'"
                    >
                        {{ interest.name }}
                    </li>
                </ul>
                <div class="mt-3 flex items-center justify-between gap-3 border-t border-[#ebe4d6] pt-3">
                    <p class="text-xs text-ink-soft">{{ screen.privacy }}</p>
                    <p class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-lagoon-800 ring-1 ring-lagoon-200">
                        {{ screen.detail }}
                    </p>
                </div>
            </template>

            <template v-else-if="screen.type === 'communities'">
                <p v-if="screen.query" class="flex items-center gap-2 rounded-xl bg-white px-3 py-2.5 text-sm text-ink-soft ring-1 ring-[#ebe4d6]">
                    <svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="1.8" />
                        <path d="M16 16.5 20 20.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                    {{ screen.query }}
                </p>
                <ul class="mt-3 space-y-3">
                    <li v-for="group in screen.groups" :key="group.name" class="min-w-0">
                        <p class="text-sm font-semibold text-ink">{{ group.name }}</p>
                        <p class="mt-1.5">
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="group.open ? 'bg-lagoon-50 text-lagoon-800' : 'bg-white text-ink-soft ring-1 ring-[#e6dfd2]'"
                            >
                                {{ group.access }}
                            </span>
                        </p>
                    </li>
                </ul>
            </template>

            <template v-else-if="screen.type === 'thread'">
                <span class="rounded-full bg-lagoon-50 px-2 py-0.5 text-[11px] font-semibold tracking-[0.12em] text-lagoon-800 uppercase">
                    {{ screen.tag }}
                </span>
                <p class="mt-2.5 text-[15px] font-normal leading-snug text-ink">
                    “{{ screen.line }}”
                </p>
                <div class="mt-3 border-l-2 border-lagoon-200 pl-3">
                    <p class="text-[11px] font-semibold tracking-[0.12em] text-ink-soft uppercase">
                        {{ screen.replyLabel }}
                    </p>
                    <p class="mt-1 text-sm leading-snug text-ink">{{ screen.reply }}</p>
                </div>
                <p class="mt-3 flex justify-end">
                    <span class="-rotate-2 rounded-full bg-moss-50 px-2.5 py-1 text-xs font-semibold text-moss-700 ring-1 ring-moss-200">
                        {{ screen.status }}
                    </span>
                </p>
            </template>
        </article>
    </div>
</template>

<script setup lang="ts">
import type { HowScreen } from '@/types/landing';

defineProps<{
    screen: HowScreen;
    tilt: string;
}>();
</script>
