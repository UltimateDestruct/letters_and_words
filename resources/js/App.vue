<script setup>
import { computed, onMounted, ref, watch } from 'vue';

const maxLength =
    typeof window !== 'undefined' && window.__WORD_MAX_LENGTH__
        ? window.__WORD_MAX_LENGTH__
        : 45;

const length = ref(4);
const count = ref(0);
const first = ref('');
const last = ref('');
const words = ref([]);
const loading = ref(false);
const error = ref('');
const emptyMessage = 'No words match this length. Try another number.';

const prettyCount = computed(() => count.value.toLocaleString());

async function fetchWords() {
    loading.value = true;
    error.value = '';
    try {
        const res = await fetch(`/api/words?length=${length.value}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await res.json();

        if (!res.ok) {
            error.value = data.message ?? 'Could not load words.';
            count.value = 0;
            first.value = '';
            last.value = '';
            words.value = [];

            return;
        }

        count.value = data.count ?? 0;
        first.value = data.first ?? '';
        last.value = data.last ?? '';
        words.value = Array.isArray(data.words) ? data.words : [];
    } catch {
        error.value = 'Network error. Check your connection and try again.';
        count.value = 0;
        first.value = '';
        last.value = '';
        words.value = [];
    } finally {
        loading.value = false;
    }
}

function increment() {
    if (length.value < maxLength) {
        length.value += 1;
    }
}

function decrement() {
    if (length.value > 1) {
        length.value -= 1;
    }
}

onMounted(() => {
    fetchWords();
});

watch(length, () => {
    fetchWords();
});
</script>

<template>
    <div class="mx-auto flex min-h-screen max-w-6xl flex-col gap-8 p-6 lg:flex-row lg:gap-12 lg:p-10">
        <section class="flex w-full flex-col gap-8 lg:w-1/2">
            <h1 class="text-lg font-semibold text-neutral-800">Admin</h1>

            <div class="flex items-stretch gap-3">
                <button
                    type="button"
                    class="flex h-20 w-20 items-center justify-center rounded-2xl border border-neutral-300 bg-neutral-200 text-3xl font-semibold text-neutral-800 shadow-sm transition hover:bg-neutral-300 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="length <= 1 || loading"
                    aria-label="Decrease letter count"
                    @click="decrement"
                >
                    −
                </button>
                <div
                    class="flex min-w-[5rem] flex-1 items-center justify-center rounded-2xl border-2 border-neutral-900 bg-white text-6xl font-semibold tabular-nums"
                >
                    {{ length }}
                </div>
                <button
                    type="button"
                    class="flex h-20 w-20 items-center justify-center rounded-2xl border border-neutral-300 bg-neutral-200 text-3xl font-semibold text-neutral-800 shadow-sm transition hover:bg-neutral-300 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="length >= maxLength || loading"
                    aria-label="Increase letter count"
                    @click="increment"
                >
                    +
                </button>
            </div>

            <div class="space-y-3 text-base leading-relaxed">
                <p>
                    Number of words with
                    <span class="font-semibold text-red-600">{{ length }}</span>
                    letters:
                </p>
                <p class="text-2xl font-semibold tabular-nums">{{ prettyCount }}</p>
                <p class="text-neutral-700">Lowest word in alphabet:</p>
                <p class="font-medium">{{ first || '—' }}</p>
                <p class="text-neutral-700">Highest word in alphabet:</p>
                <p class="font-medium">{{ last || '—' }}</p>
            </div>
        </section>

        <section class="flex w-full flex-1 flex-col gap-4 lg:w-1/2">
            <h2 class="text-lg font-semibold text-neutral-800">Results</h2>
            <div
                class="min-h-[16rem] flex-1 overflow-y-auto rounded-2xl border border-neutral-200 bg-neutral-100 p-4 shadow-inner lg:min-h-[28rem]"
                role="list"
            >
                <p v-if="loading" class="text-neutral-600">Loading…</p>
                <p v-else-if="error" class="text-red-600">{{ error }}</p>
                <p v-else-if="words.length === 0" class="text-neutral-600">
                    {{ emptyMessage }}
                </p>
                <ul v-else class="flex flex-col gap-1">
                    <li
                        v-for="word in words"
                        :key="word"
                        class="rounded-md bg-white/80 px-3 py-1.5 font-medium shadow-sm"
                        role="listitem"
                    >
                        {{ word }}
                    </li>
                </ul>
            </div>
        </section>
    </div>
</template>
