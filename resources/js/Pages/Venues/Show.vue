<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import PadelCourtBoard from '@/Components/PadelCourtBoard.vue';
import { ArrowLeft, ChevronRight, Clock, MapPin, Sparkles, Star, Users } from '@lucide/vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    venue: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        address: string;
        city: string;
        rating: string;
        settings: Record<string, unknown> | null;
        courts: Array<{
            id: number;
            name: string;
            type: string;
            price_per_hour: string;
            peak_price_per_hour: string | null;
            status: string;
            maintenance_from: string | null;
            maintenance_to: string | null;
            venue: {
                id: number;
                name: string;
                city?: string;
                slug?: string;
                rating?: string;
            };
        }>;
    };
    bookings?: Array<{
        court_id: number;
        start_time: string;
        end_time: string;
        status: string;
    }>;
    selectedDate?: string;
    todayDate?: string;
}>();

const fmt = (n: string | null) => (n === null ? '—' : `Rp ${Number(n).toLocaleString('id-ID')}`);
const typeLabel = (t: string) => (t === 'indoor' ? 'Indoor' : 'Outdoor');
const maintenance = (c: (typeof props.venue.courts)[number]) =>
    c.maintenance_from ? `${c.maintenance_from.slice(0, 5)}–${c.maintenance_to?.slice(0, 5)}` : null;
const courtStatus = (c: (typeof props.venue.courts)[number]) =>
    c.status === 'maintenance' ? 'maintenance' : 'ready';
</script>

<template>
    <Head :title="`${venue.name} — PadelHub Indonesia`" />

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

        <main class="relative z-10 mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
            <!-- Back to Explore Link -->
            <Link
                :href="route('venues.explore')"
                class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 transition hover:text-white"
            >
                <ArrowLeft class="h-4 w-4 text-emerald-400" />
                <span>Kembali ke Semua Venue</span>
            </Link>

            <!-- Venue Info Hero Card -->
            <div class="mt-6 rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-400">
                            <Sparkles class="h-3.5 w-3.5" />
                            <span>VENUE TERVERIFIKASI</span>
                        </div>
                        <h1 class="mt-3 text-3xl font-black uppercase tracking-tight text-white sm:text-4xl">
                            {{ venue.name }}
                        </h1>
                        <p class="mt-2 flex items-center gap-1.5 text-sm text-slate-400">
                            <MapPin class="h-4 w-4 text-emerald-400 shrink-0" />
                            <span>{{ venue.address }}, {{ venue.city }}</span>
                        </p>
                        <div v-if="Number(venue.rating) > 0" class="mt-3 inline-flex items-center gap-1 rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-bold text-amber-400">
                            <Star class="h-3.5 w-3.5 fill-current" />
                            <span>Rating {{ Number(venue.rating).toFixed(1) }} / 5.0</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 rounded-2xl border border-slate-800 bg-slate-950/80 px-4 py-3 text-xs font-bold text-slate-300">
                        <Users class="h-4 w-4 text-emerald-400" />
                        <span>{{ venue.courts.length }} Lapangan Siap Main</span>
                    </div>
                </div>

                <p v-if="venue.description" class="mt-6 text-sm leading-relaxed text-slate-300">
                    {{ venue.description }}
                </p>
            </div>

            <!-- THE UNIFIED PADEL COURT BOARD (MODIFIED ACROSS ALL PAGES) -->
            <div v-if="venue.courts.length" class="mt-12 space-y-4">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-400">
                        <Sparkles class="h-3.5 w-3.5" />
                        <span>PETA & SIMULASI LAPANGAN</span>
                    </div>
                    <h2 class="mt-2 text-2xl font-black uppercase tracking-tight text-white sm:text-3xl">
                        Ketersediaan Slot Lapangan Real-Time
                    </h2>
                    <p class="mt-1 text-xs text-slate-400 sm:text-sm">
                        Pilih lapangan dan tanggal. Klik kotak jam yang berwarna hijau untuk langsung reservasi slot di {{ venue.name }}.
                    </p>
                </div>

                <!-- Reusable Official Court Board -->
                <PadelCourtBoard
                    :courts="venue.courts"
                    :bookings="bookings ?? []"
                    :initial-date="selectedDate ?? todayDate"
                />
            </div>

            <!-- Court List Details -->
            <div class="mt-12">
                <h3 class="text-xl font-black uppercase tracking-tight text-white">Daftar Lapangan & Tarif</h3>
                <div class="mt-4 space-y-3">
                    <div
                        v-for="c in venue.courts"
                        :key="c.id"
                        class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-lg backdrop-blur-md transition hover:border-slate-700"
                    >
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-base font-black text-white">{{ c.name }}</p>
                                <span class="rounded-lg border border-slate-800 bg-slate-950/60 px-2.5 py-0.5 text-xs font-semibold text-slate-300">
                                    {{ typeLabel(c.type) }}
                                </span>
                            </div>
                            <p v-if="maintenance(c)" class="mt-2 flex items-center gap-1.5 text-xs text-amber-400">
                                <Clock class="h-3.5 w-3.5" />
                                <span>Maintenance {{ maintenance(c) }} WIB</span>
                            </p>
                        </div>

                        <div class="flex items-center gap-5">
                            <div class="text-right">
                                <p class="text-xl font-black text-emerald-400">{{ fmt(c.price_per_hour) }}</p>
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">per jam</p>
                            </div>
                            <Link
                                :href="route('bookings.create', { court_id: c.id })"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-5 py-2.5 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition hover:scale-105"
                                :class="courtStatus(c) === 'maintenance' ? 'pointer-events-none opacity-40' : ''"
                            >
                                <span>Booking</span>
                                <ChevronRight class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>

                    <p v-if="!venue.courts.length" class="rounded-2xl border border-dashed border-slate-800 bg-slate-900/40 py-16 text-center text-sm text-slate-400">
                        Belum ada lapangan yang terdaftar untuk venue ini.
                    </p>
                </div>
            </div>
        </main>
    </div>
</template>
