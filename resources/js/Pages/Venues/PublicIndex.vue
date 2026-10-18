<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Badge from '@/Components/ui/Badge.vue';
import { ChevronRight, MapPin, Search, Sparkles, Star, Users } from '@lucide/vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

const props = defineProps<{
    venues: Array<{
        id: number;
        slug: string;
        name: string;
        description: string | null;
        address: string;
        city: string;
        rating: string;
        court_count: number;
        min_price: string | null;
        court_types: string[];
    }>;
    filters: { search: string; city: string; type: string; max_price: string };
    cities: string[];
}>();

const form = reactive({ ...props.filters });

const hasActiveFilter = computed(() => !!(form.search || form.city || form.type || form.max_price));

const apply = () => {
    router.get(
        route('venues.explore'),
        {
            search: form.search || undefined,
            city: form.city || undefined,
            type: form.type || undefined,
            max_price: form.max_price || undefined,
        },
        { preserveState: true, replace: true },
    );
};

const reset = () => {
    form.search = '';
    form.city = '';
    form.type = '';
    form.max_price = '';
    apply();
};

const fmtPrice = (n: string | null) => (n === null ? '—' : `Rp ${Number(n).toLocaleString('id-ID')}`);
const typeLabel = (t: string) => (t === 'indoor' ? 'Indoor' : 'Outdoor');
</script>

<template>
    <Head title="Cari Venue Padel — PadelHub Indonesia" />

    <div class="relative min-h-screen bg-slate-950 font-sans text-slate-100 selection:bg-emerald-500/25 selection:text-emerald-300">
        <!-- Ambient Sports Lighting Gradients & Watermark Grid -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute -top-40 -left-40 h-[500px] w-[500px] rounded-full bg-emerald-500/15 blur-[120px]" />
            <div class="absolute top-1/2 -right-40 h-[600px] w-[600px] rounded-full bg-green-600/10 blur-[150px]" />
            <div class="absolute -bottom-40 left-1/3 h-[450px] w-[450px] rounded-full bg-teal-500/10 blur-[130px]" />
            <!-- Dot Grid Overlay -->
            <div
                class="absolute inset-0 opacity-[0.06]"
                style="background-image: radial-gradient(#22c55e 1px, transparent 1px); background-size: 28px 28px;"
            />
        </div>

        <!-- Sticky Header Navigation -->
        <header class="sticky top-0 z-30 border-b border-slate-800/80 bg-slate-950/80 backdrop-blur-xl">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" class="group inline-flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-green-600 p-2 shadow-lg shadow-emerald-500/20 transition-transform group-hover:scale-105">
                        <ApplicationLogo class="h-full w-full text-slate-950" />
                    </div>
                    <div>
                        <span class="text-xl font-extrabold tracking-tight text-white">Padel<span class="text-emerald-400">Hub</span></span>
                        <span class="ms-2 hidden rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold tracking-wider text-emerald-400 uppercase sm:inline-block">Indonesia</span>
                    </div>
                </Link>

                <div class="flex items-center gap-2.5">
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="route('dashboard')"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-4 py-2 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition hover:scale-105"
                        >
                            <span>Dashboard User</span>
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-xl border border-slate-800 bg-slate-900/60 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:bg-slate-800 hover:text-white"
                        >
                            Masuk
                        </Link>
                        <Link
                            :href="route('register')"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-4 py-2 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition hover:scale-105"
                        >
                            <span>Daftar Gratis</span>
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <main class="relative z-10 mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <!-- Page Title & Eyebrow -->
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-emerald-400">
                    <Sparkles class="h-3.5 w-3.5" />
                    <span>DIREKTORI VENUE PADEL RESMI</span>
                </div>
                <h1 class="text-3xl font-black uppercase tracking-tight text-white sm:text-4xl">
                    Temukan & Booking Venue Padel
                </h1>
                <p class="text-sm text-slate-400 sm:text-base">
                    Pilih venue terdekat di kota kamu, cek fasilitas dan tarif lapangan, lalu booking slot secara real-time.
                </p>
            </div>

            <!-- Filter Card -->
            <div class="mt-8 rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md">
                <form class="space-y-4" @submit.prevent="apply">
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="relative">
                            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                            <input
                                v-model="form.search"
                                type="text"
                                placeholder="Cari nama venue, jalan, atau kota…"
                                class="h-10 w-full rounded-xl border border-slate-800 bg-slate-950/80 pl-9 pr-3 text-xs text-white placeholder-slate-500 shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            />
                        </div>
                        <select
                            v-model="form.city"
                            class="h-10 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 [&>option]:bg-slate-900 [&>option]:text-white"
                        >
                            <option value="">Semua Kota</option>
                            <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                        </select>
                        <select
                            v-model="form.type"
                            class="h-10 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 [&>option]:bg-slate-900 [&>option]:text-white"
                        >
                            <option value="">Semua Tipe Lapangan</option>
                            <option value="indoor">Indoor</option>
                            <option value="outdoor">Outdoor</option>
                        </select>
                        <input
                            v-model="form.max_price"
                            type="number"
                            min="0"
                            step="10000"
                            placeholder="Maks tarif / jam (Rp)"
                            class="h-10 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3 text-xs text-white placeholder-slate-500 shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        />
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-800/80 pt-4">
                        <span class="text-xs font-semibold text-slate-400">
                            Menampilkan <strong class="text-white">{{ venues.length }}</strong> venue padel terdaftar
                        </span>
                        <div class="flex items-center gap-3">
                            <button
                                v-if="hasActiveFilter"
                                type="button"
                                class="text-xs font-semibold text-slate-400 transition hover:text-white"
                                @click="reset"
                            >
                                Reset Filter
                            </button>
                            <button
                                type="submit"
                                class="inline-flex h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-5 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition hover:scale-105"
                            >
                                <Search class="h-3.5 w-3.5" />
                                <span>Terapkan Filter</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Empty State -->
            <div v-if="!venues.length" class="mt-12 rounded-3xl border border-dashed border-slate-800 bg-slate-900/40 py-20 text-center">
                <p class="text-lg font-bold text-white">Tidak ada venue ditemukan</p>
                <p class="mt-1 text-sm text-slate-400">Coba ubah kata kunci atau bersihkan filter pencarian.</p>
                <button
                    v-if="hasActiveFilter"
                    type="button"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-semibold text-white transition hover:border-emerald-500/50 hover:bg-slate-750"
                    @click="reset"
                >
                    Reset Filter
                </button>
            </div>

            <!-- Venue Cards Grid -->
            <div v-else class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="v in venues"
                    :key="v.id"
                    :href="route('venues.show', v.slug)"
                    class="group flex flex-col justify-between rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-xl backdrop-blur-md transition duration-300 hover:-translate-y-1 hover:border-emerald-500/40 hover:bg-slate-900/90 hover:shadow-2xl hover:shadow-emerald-950/20"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-lg font-black tracking-tight text-white transition group-hover:text-emerald-400">{{ v.name }}</h3>
                            <span v-if="Number(v.rating) > 0" class="inline-flex shrink-0 items-center gap-1 rounded-full border border-amber-500/30 bg-amber-500/10 px-2.5 py-0.5 text-xs font-bold text-amber-400">
                                <Star class="h-3 w-3 fill-current" />
                                {{ Number(v.rating).toFixed(1) }}
                            </span>
                        </div>
                        <p class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-400">
                            <MapPin class="h-3.5 w-3.5 text-emerald-400 shrink-0" />
                            <span class="truncate">{{ v.address }}, {{ v.city }}</span>
                        </p>
                        <p class="mt-3 line-clamp-2 text-xs leading-relaxed text-slate-400">{{ v.description ?? 'Venue padel modern dengan fasilitas lengkap dan booking online mudah.' }}</p>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span v-for="t in v.court_types" :key="t" class="rounded-lg border border-slate-800 bg-slate-950/60 px-2.5 py-1 text-[11px] font-semibold text-slate-300">
                                {{ typeLabel(t) }}
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-lg border border-slate-800 bg-slate-950/60 px-2.5 py-1 text-[11px] font-semibold text-slate-300">
                                <Users class="h-3 w-3 text-emerald-400" />
                                {{ v.court_count }} Lapangan
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between border-t border-slate-800/80 pt-4">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Mulai dari</p>
                            <p class="text-sm font-black text-emerald-400">
                                {{ fmtPrice(v.min_price) }}<span class="text-xs font-normal text-slate-400">/jam</span>
                            </p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-xl bg-slate-800 px-3.5 py-1.5 text-xs font-bold text-slate-200 transition group-hover:bg-gradient-to-r group-hover:from-emerald-500 group-hover:to-green-600 group-hover:text-slate-950">
                            <span>Detail</span>
                            <ChevronRight class="h-3.5 w-3.5" />
                        </span>
                    </div>
                </Link>
            </div>
        </main>
    </div>
</template>
