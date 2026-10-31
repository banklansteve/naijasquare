<template>
    <Head title="Check your email" />

    <AuthFrame kicker="" title="Check your email">
        <p class="text-base leading-relaxed text-ink-soft">
            We've sent a verification code to your email.
        </p>

        <form class="mt-8" @submit.prevent="submit">
            <div class="grid grid-cols-6 gap-2 sm:gap-3" role="group" aria-label="Verification code">
                <input
                    v-for="(digit, index) in boxes"
                    :key="index"
                    :ref="(element) => setBox(element, index)"
                    :value="digit"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="1"
                    :autocomplete="index === 0 ? 'one-time-code' : 'off'"
                    :aria-label="`Digit ${index + 1}`"
                    class="h-14 w-full rounded-xl border border-line bg-white text-center text-xl font-bold text-lagoon-800 outline-none transition-[border-color,box-shadow] duration-500 ease-out focus:border-lagoon-700 focus:ring-4 focus:ring-lagoon-100 sm:h-16 sm:text-2xl"
                    @input="onDigit(index, $event)"
                    @keydown="onKeydown(index, $event)"
                    @paste="onPaste(index, $event)"
                    @focus="onFocus"
                >
            </div>
            <p v-if="form.errors.code" class="mt-2 text-sm text-clay-800">{{ form.errors.code }}</p>

            <button
                type="submit"
                class="mt-6 inline-flex h-12 w-full items-center justify-center rounded-full bg-lagoon-700 text-base font-bold text-white transition-[background-color] duration-500 ease-out hover:bg-lagoon-800 disabled:opacity-60"
                :disabled="form.processing || code.length !== 6"
            >
                Verify email
            </button>
        </form>

        <p v-if="status" class="mt-4 text-sm font-medium text-lagoon-800">{{ status }}</p>

        <button
            type="button"
            class="mt-4 text-sm font-semibold text-lagoon-800 underline decoration-lagoon-200 underline-offset-4 transition-colors duration-500 ease-out hover:decoration-lagoon-700"
            :disabled="resending"
            @click="resend"
        >
            Send a new code
        </button>
    </AuthFrame>
</template>

<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import AuthFrame from '@/components/auth/AuthFrame.vue';

defineProps<{
    email: string;
}>();

const length = 6;
const page = usePage<{ flash: { status: string | null } }>();
const status = computed(() => page.props.flash?.status ?? null);
const boxes = ref<string[]>(Array.from({ length }, () => ''));
const boxRefs = ref<HTMLInputElement[]>([]);
const resending = ref(false);
const form = useForm({ code: '' });
const code = computed(() => boxes.value.join(''));

function setBox(element: unknown, index: number): void {
    if (element instanceof HTMLInputElement) {
        boxRefs.value[index] = element;
    }
}

function focusBox(index: number): void {
    boxRefs.value[index]?.focus();
}

function applyDigits(value: string, start: number): void {
    const incoming = value.replace(/\D/g, '').slice(0, length - start).split('');

    incoming.forEach((character, offset) => {
        boxes.value[start + offset] = character;
    });

    if (incoming.length > 0) {
        focusBox(Math.min(start + incoming.length, length - 1));
    }
}

function onDigit(index: number, event: Event): void {
    const raw = (event.target as HTMLInputElement).value.replace(/\D/g, '');

    if (raw.length > 1) {
        applyDigits(raw, index);

        return;
    }

    boxes.value[index] = raw.slice(-1);

    if (raw !== '' && index < length - 1) {
        focusBox(index + 1);
    }
}

function onKeydown(index: number, event: KeyboardEvent): void {
    if (event.key === 'Backspace' && boxes.value[index] === '' && index > 0) {
        event.preventDefault();
        boxes.value[index - 1] = '';
        focusBox(index - 1);
    }

    if (event.key === 'ArrowLeft' && index > 0) {
        event.preventDefault();
        focusBox(index - 1);
    }

    if (event.key === 'ArrowRight' && index < length - 1) {
        event.preventDefault();
        focusBox(index + 1);
    }
}

function onPaste(index: number, event: ClipboardEvent): void {
    event.preventDefault();
    applyDigits(event.clipboardData?.getData('text') ?? '', index);
}

function onFocus(event: FocusEvent): void {
    (event.target as HTMLInputElement).select();
}

function submit(): void {
    form.code = code.value;
    form.post('/join/verify', {
        preserveScroll: true,
    });
}

function resend(): void {
    resending.value = true;
    form.post('/join/verify/resend', {
        preserveScroll: true,
        onFinish: () => {
            resending.value = false;
        },
    });
}
</script>
