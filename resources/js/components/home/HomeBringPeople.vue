<template>
    <section
        class="relative w-full max-w-full overflow-hidden rounded-[1.35rem] border border-lagoon-200 bg-lagoon-100/70"
        aria-labelledby="bring-people"
    >
        <div class="p-5 sm:p-6">
            <div class="flex items-start gap-4">
                <div class="min-w-0 flex-1">
                    <h2 id="bring-people" class="font-heading text-lg font-bold tracking-tight text-lagoon-950">
                        Bring your people
                    </h2>
                    <p class="mt-1.5 text-sm leading-6 text-lagoon-900/80">
                        Communities grow when friends invite friends. Share your personal link.
                    </p>
                </div>
                <HomeBringArt class="hidden size-16 shrink-0 text-lagoon-700 sm:block" />
            </div>

            <div class="mt-5 flex flex-col gap-2.5">
                <a
                    :href="invite.whatsapp_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-lagoon-800 px-5 font-heading text-sm font-semibold text-white transition hover:bg-lagoon-900"
                    @click="pingShare"
                >
                    <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12.04 2C6.58 2 2.15 6.3 2.15 11.6c0 2.1.62 4.05 1.7 5.7L2 22l4.9-1.58a10.3 10.3 0 0 0 5.14 1.36h.01c5.46 0 9.89-4.3 9.89-9.6C21.94 6.3 17.5 2 12.04 2Zm0 17.5c-1.55 0-3.07-.4-4.4-1.16l-.32-.18-3.26 1.05 1.07-3.1-.2-.33a7.8 7.8 0 0 1-1.25-4.18c0-4.37 3.67-7.92 8.36-7.92 4.45 0 8.1 3.4 8.1 7.58 0 4.37-3.65 8.24-8.1 8.24Zm4.57-5.9c-.25-.12-1.48-.72-1.7-.8-.23-.08-.4-.12-.57.12-.17.25-.65.8-.8.97-.15.17-.3.19-.55.06-.25-.12-1.05-.38-2-1.2-.74-.65-1.24-1.45-1.39-1.7-.14-.25-.02-.38.11-.5.11-.11.25-.3.37-.45.12-.15.17-.25.25-.42.08-.17.04-.32-.02-.45-.06-.12-.57-1.35-.78-1.85-.2-.48-.42-.41-.57-.42h-.49c-.17 0-.45.06-.68.32-.23.25-.9.87-.9 2.12s.92 2.45 1.05 2.62c.12.17 1.8 2.7 4.36 3.79.61.26 1.09.41 1.46.53.61.2 1.17.17 1.61.1.49-.07 1.48-.6 1.69-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.17-.48-.3Z" />
                    </svg>
                    Share on WhatsApp
                </a>
                <button
                    type="button"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-full border border-lagoon-300/80 bg-white px-5 font-heading text-sm font-semibold text-lagoon-900 transition hover:bg-lagoon-50"
                    @click="copyLink"
                >
                    <BaseIcon name="link" class="size-4" />
                    {{ copied ? 'Link copied' : 'Copy link' }}
                </button>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref } from 'vue';

import BaseIcon from '@/components/BaseIcon.vue';
import HomeBringArt from '@/components/home/HomeBringArt.vue';

const props = defineProps<{
    invite: {
        url: string;
        message: string;
        whatsapp_url: string;
    };
}>();

const copied = ref(false);

async function pingShare(): Promise<void> {
    try {
        await fetch('/invite/share', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
    } catch {
        // Share still works from the preloaded invite payload.
    }
}

async function copyLink(): Promise<void> {
    await pingShare();

    try {
        await navigator.clipboard.writeText(props.invite.url);
        copied.value = true;
        window.setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch {
        window.prompt('Copy this invite link', props.invite.url);
    }
}
</script>
