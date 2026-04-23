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
const page = ref(1);
const perPage = 10;
const totalPages = ref(0);
const loading = ref(false);
const error = ref('');
const emptyMessage = 'No words match this length. Try another number.';

const prettyCount = computed(() => count.value.toLocaleString());

const canPrev = computed(() => page.value > 1 && !loading.value);
const canNext = computed(
    () => totalPages.value > 0 && page.value < totalPages.value && !loading.value,
);

async function fetchWords() {
    loading.value = true;
    error.value = '';
    try {
        const params = new URLSearchParams({
            length: String(length.value),
            page: String(page.value),
            per_page: String(perPage),
        });
        const res = await fetch(`/api/words?${params}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await res.json();

        if (!res.ok) {
            error.value = data.message ?? 'Could not load words.';
            count.value = 0;
            first.value = '';
            last.value = '';
            words.value = [];
            totalPages.value = 0;

            return;
        }

        count.value = data.count ?? 0;
        first.value = data.first ?? '';
        last.value = data.last ?? '';
        words.value = Array.isArray(data.words) ? data.words : [];
        totalPages.value = Number.isFinite(data.total_pages) ? data.total_pages : 0;
    } catch {
        error.value = 'Network error. Check your connection and try again.';
        count.value = 0;
        first.value = '';
        last.value = '';
        words.value = [];
        totalPages.value = 0;
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
    if (page.value !== 1) {
        page.value = 1;
    } else {
        fetchWords();
    }
});

watch(page, () => {
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
                class="flex min-h-[16rem] flex-1 flex-col gap-3 rounded-2xl border border-neutral-200 bg-neutral-100 p-4 shadow-inner lg:min-h-[28rem]"
            >
                <p v-if="loading" class="text-neutral-600">Loading…</p>
                <p v-else-if="error" class="text-red-600">{{ error }}</p>
                <p v-else-if="words.length === 0" class="text-neutral-600">
                    {{ emptyMessage }}
                </p>
                <ul v-else class="flex flex-col gap-1" role="list">
                    <li
                        v-for="word in words"
                        :key="`${page}-${word}`"
                        class="rounded-md bg-white/80 px-3 py-1.5 font-medium shadow-sm"
                        role="listitem"
                    >
                        {{ word }}
                    </li>
                </ul>
                <div
                    v-if="!loading && !error && count > 0"
                    class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-neutral-200/80 pt-3 text-sm text-neutral-700"
                >
                    <span class="tabular-nums">
                        Page {{ page }} of {{ totalPages || 1 }}
                    </span>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="rounded-lg border border-neutral-300 bg-white px-3 py-1.5 font-medium text-neutral-800 shadow-sm transition hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="!canPrev"
                            aria-label="Previous page"
                            @click="page -= 1"
                        >
                            Previous
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border border-neutral-300 bg-white px-3 py-1.5 font-medium text-neutral-800 shadow-sm transition hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="!canNext"
                            aria-label="Next page"
                            @click="page += 1"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
