<template>
    <Head title="Create your account" />

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
                <AuthField
                    v-model="form.first_name"
                    label="First name"
                    name="first_name"
                    autocomplete="given-name"
                    :error="form.errors.first_name"
                />
                <AuthField
                    v-model="form.last_name"
                    label="Last name"
                    name="last_name"
                    autocomplete="family-name"
                    :error="form.errors.last_name"
                />
                <AuthField
                    v-model="form.email"
                    label="Email address"
                    name="email"
                    type="email"
                    inputmode="email"
                    autocomplete="email"
                    :hint="inviteNote"
                    :error="form.errors.email"
                />
                <AuthField
                    v-model="form.password"
                    label="Password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    :error="form.errors.password"
                />
                <AuthField
                    v-model="form.password_confirmation"
                    label="Confirm password"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    :error="localError"
                />
            </div>

            <div v-else-if="step === 1" class="space-y-5">
                <AuthField
                    v-model="form.phone"
                    label="Phone number"
                    name="phone"
                    type="tel"
                    inputmode="tel"
                    autocomplete="tel"
                    optional
                    :error="form.errors.phone || phoneError"
                    @update:model-value="onPhone"
                />
                <AuthField
                    v-model="form.house_number"
                    label="House number"
                    name="house_number"
                    autocomplete="address-line1"
                    :error="form.errors.house_number"
                    @update:model-value="clearAddress"
                />
                <AuthField
                    v-model="form.postcode"
                    label="Postcode"
                    name="postcode"
                    autocomplete="postal-code"
                    :error="form.errors.postcode"
                    @update:model-value="clearAddress"
                />
                <p v-if="addressError" class="text-sm text-clay-800">{{ addressError }}</p>
                <Transition
                    enter-active-class="transition duration-500 ease-out motion-reduce:transition-none"
                    enter-from-class="translate-y-1 opacity-0 motion-reduce:translate-y-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-300 ease-out motion-reduce:transition-none"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-if="address" class="rounded-2xl bg-lagoon-50 px-4 py-4 ring-1 ring-lagoon-100">
                        <p class="text-sm font-semibold text-lagoon-800">Your address</p>
                        <ul class="mt-2 flex w-full flex-col">
                            <li
                                v-for="(line, index) in addressLines"
                                :key="index"
                                class="block w-full text-base font-medium leading-6 text-ink"
                            >
                                {{ line }}
                            </li>
                        </ul>
                        <p class="mt-3 text-sm leading-relaxed text-ink-soft">
                            {{ privacyNote }} People will see {{ address.place }}.
                        </p>
                    </div>
                </Transition>
                <p v-if="!address" class="text-sm leading-relaxed text-ink-soft">{{ privacyNote }}</p>
            </div>

            <div v-else-if="step === 2" class="space-y-3">
                <button
                    v-for="(label, key) in lifeStages"
                    :key="key"
                    type="button"
                    class="flex min-h-14 w-full items-center rounded-2xl border px-4 text-left text-base font-semibold transition-[background-color,border-color,color] duration-500 ease-out"
                    :class="form.life_stage === key ? 'border-lagoon-700 bg-lagoon-700 text-white' : 'border-line bg-white text-ink hover:border-lagoon-300'"
                    :aria-pressed="form.life_stage === key"
                    @click="form.life_stage = key"
                >
                    {{ label }}
                </button>
                <p v-if="form.errors.life_stage || localError" class="text-sm text-clay-800">
                    {{ form.errors.life_stage || localError }}
                </p>
            </div>

            <div v-else-if="step === 3" class="space-y-5">
                <p class="text-base leading-relaxed text-ink-soft">A photo helps people recognise you. You can skip this.</p>
                <label class="flex min-h-36 cursor-pointer flex-col items-center justify-center rounded-[1.5rem] border border-dashed border-lagoon-300 bg-lagoon-50/60 px-4 py-6 text-center">
                    <img v-if="preview" :src="preview" alt="" class="size-20 rounded-full object-cover">
                    <span class="mt-3 text-sm font-semibold text-lagoon-800">{{ preview ? 'Change photo' : 'Choose a photo' }}</span>
                    <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="onPhoto">
                </label>
                <p v-if="form.errors.photo" class="text-sm text-clay-800">{{ form.errors.photo }}</p>
            </div>

            <div v-else-if="step === 4" class="space-y-3">
                <p class="text-base leading-relaxed text-ink-soft">Choose every one that fits. There is no limit.</p>
                <button
                    v-for="(label, key) in interests"
                    :key="key"
                    type="button"
                    class="flex min-h-14 w-full items-center rounded-2xl border px-4 text-left text-base font-semibold transition-[background-color,border-color,color] duration-500 ease-out"
                    :class="form.interests.includes(key) ? 'border-lagoon-700 bg-lagoon-700 text-white' : 'border-line bg-white text-ink hover:border-lagoon-300'"
                    :aria-pressed="form.interests.includes(key)"
                    @click="toggleInterest(key)"
                >
                    {{ label }}
                </button>
                <p v-if="form.errors.interests || localError" class="text-sm text-clay-800">
                    {{ form.errors.interests || localError }}
                </p>
            </div>

            <div v-else class="space-y-5">
                <ul class="overflow-hidden rounded-2xl bg-white ring-1 ring-line">
                    <li v-for="item in review" :key="item.step" class="flex items-start gap-3 border-b border-line px-4 py-3.5 last:border-b-0">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-ink">{{ item.label }}</p>
                            <div class="mt-1 flex items-start gap-3">
                                <img v-if="item.image" :src="item.image" alt="" class="size-10 shrink-0 rounded-full object-cover">
                                <div class="min-w-0 flex-1">
                                    <ul class="flex flex-col">
                                        <li
                                            v-for="(line, index) in item.lines"
                                            :key="`${item.step}-${index}`"
                                            class="block w-full text-sm leading-6 text-ink-soft"
                                        >
                                            {{ line }}
                                        </li>
                                    </ul>
                                    <p v-if="item.note" class="mt-2 text-xs leading-5 text-ink-soft/65">{{ item.note }}</p>
                                </div>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="shrink-0 text-sm font-semibold text-lagoon-800 underline decoration-lagoon-200 underline-offset-4 transition-colors duration-500 ease-out hover:text-lagoon-900"
                            @click="goTo(item.step)"
                        >
                            Edit
                        </button>
                    </li>
                </ul>
                <UiCheckbox
                    v-model="form.terms"
                    @update:model-value="localError = ''"
                >
                    I'm 18 or over, and I agree to the
                    <Link href="/guidelines" class="font-semibold text-lagoon-800 underline decoration-lagoon-200 underline-offset-4">Community Guidelines</Link>
                    and
                    <Link href="/privacy" class="font-semibold text-lagoon-800 underline decoration-lagoon-200 underline-offset-4">Privacy notice</Link>.
                </UiCheckbox>
                <button
                    type="submit"
                    class="inline-flex h-12 w-full items-center justify-center rounded-full bg-lagoon-700 px-6 text-base font-bold text-white transition-[background-color] duration-500 ease-out hover:bg-lagoon-800 disabled:opacity-60"
                    :disabled="!form.terms || form.processing"
                >
                    {{ form.processing ? 'Creating your account…' : 'Create account' }}
                </button>
                <p v-if="form.errors.terms || localError" class="text-sm text-clay-800">
                    {{ form.errors.terms || localError }}
                </p>
            </div>

            <Transition
                v-if="step === 1"
                mode="out-in"
                enter-active-class="transition duration-500 ease-out motion-reduce:transition-none"
                enter-from-class="translate-y-1 opacity-0 motion-reduce:translate-y-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-300 ease-out motion-reduce:transition-none"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="address" key="confirmed" class="grid grid-cols-2 items-stretch gap-3">
                    <button
                        type="button"
                        class="inline-flex min-h-12 items-center justify-center rounded-full border border-line bg-white px-3 text-center text-sm font-semibold leading-tight text-ink transition-[background-color,border-color,color] duration-500 ease-out hover:border-lagoon-300 hover:bg-lagoon-50"
                        @click="rejectAddress"
                    >
                        Not my address, change
                    </button>
                    <button
                        type="submit"
                        class="inline-flex min-h-12 items-center justify-center rounded-full bg-lagoon-700 px-3 text-center text-sm font-bold text-white transition-[background-color] duration-500 ease-out hover:bg-lagoon-800 disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        Continue
                    </button>
                </div>
                <button
                    v-else
                    key="validate"
                    type="button"
                    class="inline-flex h-12 w-full items-center justify-center rounded-full bg-lagoon-700 px-6 text-base font-bold text-white transition-[background-color] duration-500 ease-out hover:bg-lagoon-800 disabled:opacity-60"
                    :disabled="lookingUp"
                    @click="findAddress"
                >
                    {{ lookingUp ? 'Checking address…' : 'Validate address' }}
                </button>
            </Transition>
            <button
                v-else-if="step < steps.length - 1"
                type="submit"
                class="inline-flex h-12 w-full items-center justify-center rounded-full bg-lagoon-700 px-6 text-base font-bold text-white transition-[background-color] duration-500 ease-out hover:bg-lagoon-800 disabled:opacity-60"
                :disabled="form.processing"
            >
                {{ submitLabel }}
            </button>
            <p v-if="step === 0" class="text-center text-sm text-ink-soft">
                Already have an account?
                <Link href="/sign-in" class="ml-1 font-semibold text-lagoon-800 underline decoration-lagoon-200 underline-offset-4">Sign in</Link>
            </p>
        </form>
    </AuthFrame>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import AuthField from '@/components/auth/AuthField.vue';
import AuthFrame from '@/components/auth/AuthFrame.vue';
import UiCheckbox from '@/components/ui/UiCheckbox.vue';

const props = defineProps<{
    lifeStages: Record<string, string>;
    interests: Record<string, string>;
    privacyNote: string;
    inviteNote: string;
}>();

const steps = [
    { kicker: '', title: 'Create Your Account' },
    { kicker: '', title: 'Where are you based?' },
    { kicker: '', title: 'What best describes you right now?' },
    { kicker: '', title: 'Add a profile photo' },
    { kicker: '', title: 'What are you most interested in?' },
    { kicker: '', title: 'Check your details' },
];

const step = ref(0);
const localError = ref('');
const phoneError = ref('');
const lookingUp = ref(false);
const addressError = ref('');
const address = ref<{
    address: string;
    place: string;
    house_number: string;
    street: string | null;
    postcode: string;
    city: string;
} | null>(null);
const preview = ref<string | null>(null);

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    house_number: '',
    postcode: '',
    life_stage: '',
    interests: [] as string[],
    photo: null as File | null,
    terms: false,
});

const current = computed(() => steps[step.value]);

const addressLines = computed(() => {
    if (address.value === null) {
        return [] as string[];
    }

    return [
        address.value.house_number,
        address.value.street ?? '',
        address.value.postcode,
        address.value.city,
    ].filter((line) => line !== '');
});

const review = computed(() => [
    {
        step: 0,
        label: 'Your account',
        lines: [`${form.first_name} ${form.last_name}`, form.email],
        note: null as string | null,
        image: null as string | null,
    },
    {
        step: 1,
        label: 'Where you are based',
        lines: [
            ...addressLines.value,
            form.phone.trim() === '' ? 'No phone number' : form.phone,
        ].filter((line) => line !== ''),
        note: address.value ? `People will see ${address.value.place}` : null,
        image: null as string | null,
    },
    {
        step: 2,
        label: 'About you',
        lines: [props.lifeStages[form.life_stage] ?? ''],
        note: null as string | null,
        image: null as string | null,
    },
    {
        step: 3,
        label: 'Photo',
        lines: [preview.value ? 'Photo added' : 'No photo'],
        note: null as string | null,
        image: preview.value,
    },
    {
        step: 4,
        label: 'Interests',
        lines: [form.interests.map((key) => props.interests[key]).filter(Boolean).join(', ')],
        note: null as string | null,
        image: null as string | null,
    },
]);

const submitLabel = computed(() => {
    if (form.processing) {
        return 'Creating your account…';
    }

    if (step.value === 3 && form.photo === null) {
        return 'Skip for now';
    }

    if (step.value === steps.length - 1) {
        return 'Create account';
    }

    return 'Continue';
});

function back(): void {
    localError.value = '';
    step.value = Math.max(0, step.value - 1);
}

function goTo(index: number): void {
    localError.value = '';
    step.value = index;
}

function onPhone(value: string): void {
    form.phone = value;
    phoneError.value = '';
}

function ukMobile(input: string): boolean {
    let compact = input.replace(/[^\d+]/g, '');

    if (compact.startsWith('0044')) {
        compact = `0${compact.slice(4)}`;
    } else if (compact.startsWith('+44')) {
        compact = `0${compact.slice(3)}`;
    } else if (compact.startsWith('44') && compact.length === 12) {
        compact = `0${compact.slice(2)}`;
    }

    return /^07[1-57-9]\d{8}$/.test(compact);
}

function rejectAddress(): void {
    address.value = null;
    addressError.value = '';
    localError.value = '';
}

function clearAddress(): void {
    address.value = null;
    addressError.value = '';
}

function toggleInterest(key: string): void {
    localError.value = '';
    if (form.interests.includes(key)) {
        form.interests = form.interests.filter((item) => item !== key);

        return;
    }

    form.interests = [...form.interests, key];
}

function onPhoto(event: Event): void {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.photo = file;

    if (preview.value) {
        URL.revokeObjectURL(preview.value);
    }

    preview.value = file ? URL.createObjectURL(file) : null;
}

function xsrf(): string {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);

    return match ? decodeURIComponent(match[1]) : '';
}

async function findAddress(): Promise<void> {
    if (lookingUp.value) {
        return;
    }

    addressError.value = '';
    localError.value = '';

    if (form.house_number.trim() === '' || form.postcode.trim() === '') {
        addressError.value = 'Add your house number and postcode first.';

        return;
    }

    lookingUp.value = true;

    try {
        const response = await fetch('/join/address', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': xsrf(),
            },
            body: JSON.stringify({
                house_number: form.house_number,
                postcode: form.postcode,
            }),
        });
        const body = await response.json() as {
            address?: string;
            place?: string;
            house_number?: string;
            street?: string | null;
            postcode?: string;
            city?: string;
            errors?: { postcode?: string[]; house_number?: string[] };
            message?: string;
        };

        if (!response.ok || !body.address || !body.place) {
            address.value = null;
            addressError.value = body.errors?.house_number?.[0] ?? body.errors?.postcode?.[0] ?? 'We could not find that address. Check it and try again.';

            return;
        }

        const street = body.street?.trim() ?? '';

        address.value = {
            address: body.address,
            place: body.place,
            house_number: body.house_number?.trim() || form.house_number.trim(),
            street: street === '' ? null : street,
            postcode: body.postcode?.trim() ?? '',
            city: body.city?.trim() ?? '',
        };
    } catch {
        addressError.value = 'We could not look that up just now. Try again in a moment.';
    } finally {
        lookingUp.value = false;
    }
}

function next(): void {
    localError.value = '';

    if (step.value === 0) {
        if (form.first_name.trim() === '' || form.last_name.trim() === '' || form.email.trim() === '' || form.password === '') {
            localError.value = 'Add your name, email, and password to continue.';

            return;
        }

        if (form.password !== form.password_confirmation) {
            localError.value = 'Those passwords do not match.';

            return;
        }
    }

    if (step.value === 1) {
        phoneError.value = '';

        if (form.phone.trim() !== '' && !ukMobile(form.phone)) {
            phoneError.value = 'Enter a UK mobile number, or leave this blank.';

            return;
        }

        if (address.value === null) {
            void findAddress();

            return;
        }
    }

    if (step.value === 2 && form.life_stage === '') {
        localError.value = 'Choose the one that fits you best.';

        return;
    }

    if (step.value === 4 && form.interests.length === 0) {
        localError.value = 'Choose at least one interest.';

        return;
    }

    if (step.value < steps.length - 1) {
        step.value += 1;

        return;
    }

    if (!form.terms) {
        localError.value = 'Confirm you are 18 or over and agree to the guidelines and privacy notice.';

        return;
    }

    form.post('/join', {
        forceFormData: true,
        onError: (errors) => {
            step.value = stepFor(errors);
        },
    });
}

function stepFor(errors: Record<string, string>): number {
    if (errors.first_name || errors.last_name || errors.email || errors.password) {
        return 0;
    }

    if (errors.phone || errors.house_number || errors.postcode) {
        return 1;
    }

    if (errors.life_stage) {
        return 2;
    }

    if (errors.photo) {
        return 3;
    }

    if (errors.interests) {
        return 4;
    }

    return 5;
}
</script>
