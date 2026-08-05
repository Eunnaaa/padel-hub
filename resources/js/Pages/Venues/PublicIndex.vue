<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Badge from '@/Components/ui/Badge.vue';
import Input from '@/Components/ui/Input.vue';
import { ChevronRight, MapPin, Search, Star, Users } from '@lucide/vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

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

const fmtPrice = (n: string | null) => (n === null ? '—' : `Rp ${Number(n).toLocaleString('id-ID')}`);
const typeLabel = (t: string) => (t === 'indoor' ? 'Indoor' : 'Outdoor');
const selectClass =
    'h-10 rounded-lg border border-white/10 bg-white/5 px-3 text-sm text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent [&>option]:text-black';
</script>

<template>
    <Head title="Cari venue — PadelHub" />

    <div class="min-h-screen bg-brand text-white">
        <div
            class="pointer-events-none fixed inset-0 opacity-[0.07]"
            style="background-image: radial-gradient(#22C55E 1px, transparent 1px); background-size: 28px 28px"
        />

        <header class="relative z-20 border-b border-white/5">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
                <Link href="/" class="inline-flex items-center gap-2.5">
                    <ApplicationLogo class="h-8 w-8 text-white" />
                    <span class="text-lg font-bold tracking-tight">PadelHub</span>
                </Link>

                <div class="flex items-center gap-2">
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="route('dashboard')"
                            class="inline-flex items-center gap-1.5 rounded-full bg-brand-accent px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-500"
                        >
                            Dashboard
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="hidden rounded-full px-4 py-2 text-sm font-medium text-white/70 transition hover:text-white sm:inline-flex"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="inline-flex items-center gap-1.5 rounded-full bg-brand-accent px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-500"
                        >
                            Daftar gratis
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <main class="relative z-10 mx-auto max-w-6xl px-6 py-12">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold uppercase tracking-tight">Cari venue</h1>
                    <p class="mt-2 text-sm text-white/55">Temukan lapangan padel terdekat, cek harga, langsung booking.</p>
                </div>
            </div>

            <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 p-5">
                <form class="grid gap-3 md:grid-cols-2 lg:grid-cols-4" @submit.prevent="apply">
                    <div class="relative lg:col-span-1">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-white/40" />
                        <input
                            v-model="form.search"
                            type="text"
                            placeholder="Cari venue atau kota…"
                            class="h-10 w-full rounded-lg border border-white/10 bg-white/5 pl-9 pr-3 text-sm text-white placeholder:text-white/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent"
                        />
                    </div>
                    <select v-model="form.city" class="h-10 rounded-lg border border-white/10 bg-white/5 px-3 text-sm text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent [&>option]:text-black">
                        <option value="">Semua kota</option>
                        <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                    </select>
                    <select v-model="form.type" class="h-10 rounded-lg border border-white/10 bg-white/5 px-3 text-sm text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent [&>option]:text-black">
                        <option value="">Semua tipe</option>
                        <option value="indoor">Indoor</option>
                        <option value="outdoor">Outdoor</option>
                    </select>
                    <input
                        v-model="form.max_price"
                        type="number"
                        min="0"
                        step="10000"
                        placeholder="Max harga / jam (Rp)"
                        class="h-10 rounded-lg border border-white/10 bg-white/5 px-3 text-sm text-white placeholder:text-white/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent"
                    />
                    <button
                        type="submit"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-accent px-6 text-sm font-semibold text-white transition hover:bg-green-500"
                    >
                        <Search class="h-4 w-4" />
                        Terapkan filter
                    </button>
                </form>
            </div>

            <div v-if="!venues.length" class="mt-12 rounded-2xl border border-dashed border-white/15 py-20 text-center">
                <p class="font-semibold text-white/80">Tidak ada venue ditemukan</p>
                <p class="mt-1 text-sm text-white/40">Coba ubah filter pencarian.</p>
            </div>

            <div v-else class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="v in venues"
                    :key="v.id"
                    :href="route('venues.show', v.slug)"
                    class="group rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:border-brand-accent/50 hover:bg-white/10"
                >
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-lg font-bold tracking-tight">{{ v.name }}</p>
                        <span v-if="Number(v.rating) > 0" class="inline-flex items-center gap-1 rounded-full bg-brand-accent/15 px-2.5 py-1 text-xs font-semibold text-brand-accent">
                            <Star class="h-3 w-3 fill-current" />
                            {{ Number(v.rating).toFixed(1) }}
                        </span>
                    </div>
                    <p class="mt-1 flex items-center gap-1 text-sm text-white/50">
                        <MapPin class="h-3.5 w-3.5" />
                        {{ v.address }}, {{ v.city }}
                    </p>
                    <p class="mt-3 line-clamp-2 text-sm text-white/55">{{ v.description ?? '—' }}</p>

                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        <Badge v-for="t in v.court_types" :key="t" variant="outline" class="border-white/15 text-white/60">{{ typeLabel(t) }}</Badge>
                        <Badge variant="outline" class="border-white/15 text-white/60">
                            <Users class="mr-1 h-3 w-3" />
                            {{ v.court_count }} court
                        </Badge>
                    </div>

                    <div class="mt-5 flex items-center justify-between border-t border-white/10 pt-4">
                        <p class="text-sm text-white/50">
                            Mulai dari <span class="font-bold text-brand-accent">{{ fmtPrice(v.min_price) }}</span>/jam
                        </p>
                        <span class="inline-flex items-center gap-1 text-sm font-semibold text-brand-accent transition group-hover:gap-2">
                            Lihat
                            <ChevronRight class="h-4 w-4" />
                        </span>
                    </div>
                </Link>
            </div>
        </main>
    </div>
</template>
