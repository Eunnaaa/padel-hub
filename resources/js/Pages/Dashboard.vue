<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import {
    Activity,
    CalendarCheck,
    CalendarDays,
    Check,
    CheckCircle2,
    ChevronRight,
    Clock,
    Copy,
    Flame,
    MapPin,
    Plus,
    QrCode,
    Search,
    Shield,
    Sparkles,
    Tag,
    Trophy,
    Zap,
} from '@lucide/vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import PadelCourtBoard from '@/Components/PadelCourtBoard.vue';

const props = defineProps<{
    stats: { total: number; upcoming: number; completed: number; cancelled: number };
    upcoming: Array<{
        id: number;
        booking_date: string;
        start_time: string;
        end_time: string;
        status: string;
        payment_status: string;
        total_amount: string;
        court: {
            id: number;
            name: string;
            type?: string;
            venue: { id: number; name: string; city: string; slug?: string };
        };
    }>;
    promos: Array<{
        id: number;
        code: string;
        title: string;
        description: string | null;
        discount_type: string;
        discount_value: string;
        ends_at: string;
    }>;
    notifications: Array<{
        id: string;
        data: { title: string; body: string; url?: string };
        read_at: string | null;
        created_at: string;
    }>;
    unreadNotifications: number;
    featuredVenues?: Array<{
        id: number;
        name: string;
        slug: string;
        city: string;
        description: string | null;
        rating: string;
        courts_count?: number;
        settings?: { amenities?: string[] } | null;
    }>;
    selectedDate?: string;
    todayDate?: string;
    courtBookings?: Array<{
        court_id: number;
        start_time: string;
        end_time: string;
        status: string;
    }>;
    featuredCourts?: Array<{
        id: number;
        venue_id: number;
        name: string;
        type: string;
        price_per_hour: string;
        status: string;
        maintenance_from: string | null;
        maintenance_to: string | null;
        venue: {
            id: number;
            name: string;
            city: string;
            slug: string;
            rating: string;
        };
    }>;
}>();

// --- Date Helpers ---
import { formatLocalDate } from '@/lib/utils';
const todayStr = props.todayDate ?? formatLocalDate();
const tomorrowStr = formatLocalDate(new Date(Date.now() + 86400000));

// --- General Dashboard Helpers ---
const copiedCode = ref<string | null>(null);

const copyPromo = async (code: string) => {
    try {
        await navigator.clipboard.writeText(code);
        copiedCode.value = code;
        toast.success(`Kode promo ${code} berhasil disalin!`);
        setTimeout(() => {
            if (copiedCode.value === code) copiedCode.value = null;
        }, 2500);
    } catch {
        toast.error('Gagal menyalin kode promo.');
    }
};

const fmtDate = (d: string | null | undefined) => {
    if (!d) return 'Selamanya';
    const parsed = d.includes('T') || d.includes(' ') ? new Date(d) : new Date(`${d}T00:00:00`);
    if (isNaN(parsed.getTime())) return 'Selamanya';
    return parsed.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const getDayNumber = (d: string) => new Date(`${d}T00:00:00`).getDate();
const getMonthShort = (d: string) =>
    new Date(`${d}T00:00:00`).toLocaleDateString('id-ID', { month: 'short' }).toUpperCase();
const getDayName = (d: string) =>
    new Date(`${d}T00:00:00`).toLocaleDateString('id-ID', { weekday: 'long' });

const isToday = (d: string) => d === todayStr;
const isTomorrow = (d: string) => d === tomorrowStr;

const fmt = (n: string | number) => Number(n).toLocaleString('id-ID');

const fmtPromoBadge = (p: (typeof props.promos)[number]) => {
    if (p.discount_type === 'percent') {
        return {
            value: `${Number(p.discount_value)}%`,
            unit: 'OFF',
        };
    }
    const val = Number(p.discount_value);
    if (val >= 1000) {
        return {
            value: `Rp ${(val / 1000).toLocaleString('id-ID')}rb`,
            unit: 'POTONGAN',
        };
    }
    return {
        value: `Rp ${val.toLocaleString('id-ID')}`,
        unit: 'POTONGAN',
    };
};

const timeAgo = (d: string) => {
    const mins = Math.floor((Date.now() - new Date(d).getTime()) / 60000);
    if (mins < 60) return `${mins} mnt lalu`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return `${hours} jam lalu`;
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
};

const statusBadge = (s: string): 'warning' | 'success' | 'info' | 'muted' =>
    ({ pending: 'warning', confirmed: 'success', checked_in: 'info' } as Record<
        string,
        'warning' | 'success' | 'info' | 'muted'
    >)[s] ?? 'muted';

const statusLabel = (s: string) => {
    if (s === 'confirmed') return 'Terkonfirmasi';
    if (s === 'pending') return 'Menunggu Pembayaran';
    if (s === 'checked_in') return 'Checked In';
    return s;
};
</script>

<template>
    <Head title="Dashboard — PadelHub" />

    <AuthenticatedLayout :dark="true">
        <div class="py-8 sm:py-10 text-white">
            <div class="mx-auto max-w-7xl space-y-10 px-4 sm:px-6 lg:px-8">
                <!-- HERO PLAYER WELCOMING BANNER (DARK THEME WITH RADIAL GLOW) -->
                <div
                    class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-white/[0.08] via-white/[0.04] to-black/50 p-6 shadow-2xl backdrop-blur-xl sm:p-8"
                >
                    <!-- Ambient Glow / Mesh background -->
                    <div
                        class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-brand-accent/20 blur-3xl"
                    />
                    <div
                        class="pointer-events-none absolute -bottom-16 right-24 h-48 w-48 rounded-full bg-emerald-500/15 blur-2xl"
                    />
                    <div
                        class="pointer-events-none absolute inset-0 opacity-[0.04]"
                        style="background-image: radial-gradient(#22C55E 1px, transparent 1px); background-size: 24px 24px"
                    />

                    <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <!-- Profile Greeting -->
                        <div class="flex items-start gap-4 sm:gap-5">
                            <div
                                class="relative flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white/10 ring-2 ring-brand-accent/50 shadow-inner sm:h-16 sm:w-16"
                            >
                                <img
                                    v-if="$page.props.auth.user.avatar_url"
                                    :src="$page.props.auth.user.avatar_url"
                                    alt="Avatar"
                                    class="h-full w-full object-cover"
                                />
                                <span v-else class="text-xl font-extrabold text-white">
                                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                </span>
                                <span
                                    class="absolute bottom-1 right-1 h-3 w-3 rounded-full border-2 border-brand bg-brand-accent"
                                    title="Active player"
                                />
                            </div>

                            <div class="space-y-1.5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full border border-brand-accent/30 bg-brand-accent/15 px-2.5 py-0.5 text-[11px] font-semibold tracking-wide text-brand-accent uppercase"
                                    >
                                        <Sparkles class="h-3 w-3" />
                                        Pemain Padel
                                    </span>
                                    <span class="text-xs text-white/40">
                                        Member ID: #PDL-{{ String($page.props.auth.user.id).padStart(4, '0') }}
                                    </span>
                                </div>

                                <h1 class="flex items-center gap-2 text-2xl font-black uppercase tracking-tight text-white sm:text-3xl">
                                    <span>Halo, {{ $page.props.auth.user.name }}!</span>
                                    <Sparkles class="h-6 w-6 text-brand-accent animate-pulse" />
                                </h1>

                                <p class="max-w-lg text-xs text-white/70 sm:text-sm">
                                    <template v-if="stats.upcoming > 0">
                                        Kamu memiliki <strong class="text-brand-accent">{{ stats.upcoming }} jadwal main</strong> aktif mendatang. Siapkan raket dan outfit terbaikmu!
                                    </template>
                                    <template v-else>
                                        Semua lapangan siap menyambutmu. Pilih slot visual di bawah atau cari venue favoritmu sekarang!
                                    </template>
                                </p>
                            </div>
                        </div>

                        <!-- Quick Action Shortcuts -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0">
                            <Link
                                :href="route('bookings.create')"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-4 text-xs sm:text-sm font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition-all hover:from-emerald-400 hover:to-green-500 hover:scale-[1.02] active:scale-[0.98]"
                            >
                                <Plus class="h-4 w-4 stroke-[2.5]" />
                                <span>Booking Lapangan</span>
                            </Link>

                            <div class="grid grid-cols-2 sm:flex sm:items-center gap-2">
                                <Link
                                    :href="route('venues.explore')"
                                    class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl border border-white/15 bg-white/5 px-3.5 text-xs sm:text-sm font-semibold text-white/90 transition-all hover:border-white/25 hover:bg-white/10 hover:text-white hover:scale-[1.02] active:scale-[0.98]"
                                >
                                    <Search class="h-3.5 w-3.5 text-emerald-400" />
                                    <span>Cari Venue</span>
                                </Link>

                                <Link
                                    :href="route('availability.index')"
                                    class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl border border-white/15 bg-white/5 px-3.5 text-xs sm:text-sm font-semibold text-white/90 transition-all hover:border-white/25 hover:bg-white/10 hover:text-white hover:scale-[1.02] active:scale-[0.98]"
                                >
                                    <Clock class="h-3.5 w-3.5 text-emerald-400" />
                                    <span>Cek Slot</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4 ATHLETIC KPI METRIC TILES (DARK FROSTED GLASS) -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Tile 1: Jadwal Mendatang -->
                    <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur transition duration-300 hover:-translate-y-1 hover:border-brand-accent/50 hover:bg-white/[0.08] hover:shadow-xl hover:shadow-brand-accent/5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-white/50">
                                Jadwal Mendatang
                            </span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400 ring-1 ring-emerald-500/30">
                                <CalendarCheck class="h-5 w-5" />
                            </div>
                        </div>
                        <div class="mt-3 flex items-baseline justify-between">
                            <p class="text-3xl font-black tracking-tight text-white">
                                {{ stats.upcoming }}
                            </p>
                            <span
                                v-if="stats.upcoming > 0"
                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-xs font-bold text-emerald-300"
                            >
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400" />
                                Siap Main
                            </span>
                            <span v-else class="text-xs text-white/40">Belum ada</span>
                        </div>
                        <p class="mt-1 text-xs text-white/50">Sesi terdekat yang dipesan</p>
                    </div>

                    <!-- Tile 2: Total Booking -->
                    <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur transition duration-300 hover:-translate-y-1 hover:border-brand-accent/50 hover:bg-white/[0.08] hover:shadow-xl hover:shadow-brand-accent/5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-white/50">
                                Total Booking
                            </span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30">
                                <Activity class="h-5 w-5" />
                            </div>
                        </div>
                        <div class="mt-3 flex items-baseline justify-between">
                            <p class="text-3xl font-black tracking-tight text-white">
                                {{ stats.total }}
                            </p>
                            <span class="rounded-md bg-blue-500/20 px-2 py-0.5 text-[11px] font-bold text-blue-300">
                                All-time
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-white/50">Seluruh riwayat sesi bermain</p>
                    </div>

                    <!-- Tile 3: Sesi Selesai -->
                    <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur transition duration-300 hover:-translate-y-1 hover:border-brand-accent/50 hover:bg-white/[0.08] hover:shadow-xl hover:shadow-brand-accent/5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-white/50">
                                Selesai Bermain
                            </span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-500/20 text-purple-400 ring-1 ring-purple-500/30">
                                <Trophy class="h-5 w-5" />
                            </div>
                        </div>
                        <div class="mt-3 flex items-baseline justify-between">
                            <p class="text-3xl font-black tracking-tight text-white">
                                {{ stats.completed }}
                            </p>
                            <span class="rounded-md bg-purple-500/20 px-2 py-0.5 text-[11px] font-bold text-purple-300">
                                Match
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-white/50">Jam bermain berhasil dituntaskan</p>
                    </div>

                    <!-- Tile 4: Promo Diskon -->
                    <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur transition duration-300 hover:-translate-y-1 hover:border-brand-accent/50 hover:bg-white/[0.08] hover:shadow-xl hover:shadow-brand-accent/5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-white/50">
                                Voucher Diskon
                            </span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/20 text-amber-400 ring-1 ring-amber-500/30">
                                <Tag class="h-5 w-5" />
                            </div>
                        </div>
                        <div class="mt-3 flex items-baseline justify-between">
                            <p class="text-3xl font-black tracking-tight text-white">
                                {{ promos.length }}
                            </p>
                            <span class="rounded-md bg-amber-500/20 px-2 py-0.5 text-[11px] font-bold text-amber-300">
                                Hemat
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-white/50">Kupon aktif siap digunakan</p>
                    </div>
                </div>

                <!-- SECTION: UPCOMING MATCHES PASSES (DARK MATCH PASS TICKETS) -->
                <div class="space-y-4">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Jadwal Aktif Kamu</p>
                            <h2 class="mt-1 text-2xl font-extrabold uppercase tracking-tight text-white sm:text-3xl">
                                Tiket Pertandingan Terdekat
                            </h2>
                        </div>
                        <Link
                            :href="route('bookings.index')"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-brand-accent hover:underline sm:text-sm"
                        >
                            Lihat semua riwayat booking
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </div>

                    <!-- Upcoming List / Match Passes -->
                    <div v-if="upcoming.length" class="space-y-3.5">
                        <div
                            v-for="b in upcoming"
                            :key="b.id"
                            class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-5 shadow-sm backdrop-blur transition hover:border-brand-accent/40 hover:bg-white/[0.08] sm:p-6"
                        >
                            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                                <!-- Left: Calendar Date Badge & Match Details -->
                                <div class="flex items-start gap-4 sm:gap-5">
                                    <!-- Calendar Tile -->
                                    <div
                                        class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-2xl border border-white/15 bg-white/5 p-2 text-center shadow-inner"
                                    >
                                        <span class="text-[10px] font-black uppercase tracking-wider text-brand-accent">
                                            {{ getMonthShort(b.booking_date) }}
                                        </span>
                                        <span class="text-2xl font-black leading-none text-white">
                                            {{ getDayNumber(b.booking_date) }}
                                        </span>
                                        <span class="text-[9px] font-semibold text-white/50">
                                            {{ getDayName(b.booking_date).slice(0, 3) }}
                                        </span>
                                    </div>

                                    <!-- Match & Court Details -->
                                    <div class="space-y-1.5">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span
                                                v-if="isToday(b.booking_date)"
                                                class="inline-flex items-center gap-1 rounded-md bg-rose-500/20 px-2 py-0.5 text-[10px] font-black uppercase text-rose-300 ring-1 ring-rose-500/40"
                                            >
                                                <Flame class="h-3 w-3" />
                                                HARI INI
                                            </span>
                                            <span
                                                v-else-if="isTomorrow(b.booking_date)"
                                                class="inline-flex items-center rounded-md bg-amber-500/20 px-2 py-0.5 text-[10px] font-black uppercase text-amber-300 ring-1 ring-amber-500/40"
                                            >
                                                BESOK
                                            </span>

                                            <Badge :variant="statusBadge(b.status)">
                                                {{ statusLabel(b.status) }}
                                            </Badge>
                                        </div>

                                        <h3 class="text-lg font-bold tracking-tight text-white transition-colors group-hover:text-brand-accent sm:text-xl">
                                            {{ b.court?.name ?? 'Lapangan' }} — {{ b.court?.venue?.name ?? 'Venue' }}
                                        </h3>

                                        <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-white/60">
                                            <span class="flex items-center gap-1">
                                                <MapPin class="h-3.5 w-3.5 text-brand-accent" />
                                                {{ b.court?.venue?.city ?? 'Lokasi' }}
                                            </span>
                                            <span class="flex items-center gap-1 font-semibold text-white">
                                                <Clock class="h-3.5 w-3.5 text-white/50" />
                                                {{ b.start_time }} – {{ b.end_time }} WIB
                                            </span>
                                            <span class="rounded bg-white/10 px-1.5 py-0.5 text-[10px] font-medium text-white/70">
                                                Durasi 60 mnt
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right: Pricing & Action Buttons -->
                                <div class="flex flex-col gap-3 border-t border-white/10 pt-4 sm:flex-row sm:items-center sm:justify-between lg:border-t-0 lg:pt-0">
                                    <div class="text-left lg:text-right">
                                        <p class="text-[11px] text-white/50">Total Pembayaran</p>
                                        <p class="text-xl font-black text-brand-accent sm:text-2xl">
                                            Rp {{ fmt(b.total_amount) }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <!-- If pending payment: High-visibility Pay Now button -->
                                        <template v-if="b.payment_status !== 'paid' && b.status === 'pending'">
                                            <Button
                                                as="link"
                                                :href="route('payments.create', b.id)"
                                                variant="accent"
                                                size="default"
                                                class="shadow-md shadow-brand-accent/20"
                                            >
                                                <Zap class="h-4 w-4" />
                                                Bayar Sekarang
                                                <ChevronRight class="h-4 w-4" />
                                            </Button>
                                        </template>

                                        <!-- If confirmed or checked_in: QR Ticket Link -->
                                        <template v-else>
                                            <Button
                                                as="link"
                                                :href="route('payments.invoice', b.id)"
                                                variant="outline"
                                                size="default"
                                                class="gap-1.5 border-white/20 text-white hover:bg-white/10"
                                            >
                                                <QrCode class="h-4 w-4 text-brand-accent" />
                                                Tiket & QR
                                            </Button>

                                            <Button
                                                as="link"
                                                :href="route('bookings.index')"
                                                variant="ghost"
                                                size="default"
                                                class="gap-1 text-white/60 hover:bg-white/10 hover:text-white"
                                            >
                                                Detail
                                                <ChevronRight class="h-4 w-4" />
                                            </Button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inspiring Empty State when no upcoming bookings -->
                    <div
                        v-else
                        class="overflow-hidden rounded-3xl border border-dashed border-white/15 bg-white/[0.02] p-8 text-center sm:p-12"
                    >
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-accent/15 text-brand-accent">
                            <CalendarDays class="h-7 w-7" />
                        </div>
                        <h3 class="mt-4 text-lg font-bold uppercase text-white">
                            Belum Ada Jadwal Main Mendatang
                        </h3>
                        <p class="mx-auto mt-1 max-w-sm text-xs text-white/50 sm:text-sm">
                            Lapangan padel favoritmu menunggu. Pilih slot pada denah visual di bawah ini untuk booking instan!
                        </p>
                        <div class="mt-6 flex flex-wrap justify-center gap-3">
                            <Button as="link" :href="route('bookings.create')" variant="accent">
                                <Plus class="h-4 w-4" />
                                Mulai Booking Sekarang
                            </Button>
                            <Button as="link" :href="route('venues.explore')" variant="outline" class="border-white/20 text-white hover:bg-white/10">
                                <Search class="h-4 w-4" />
                                Jelajahi Venue
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- SIGNATURE SECTION FROM MAIN PAGE: INTERACTIVE PADEL COURT VISUALIZER BOARD -->
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Peta Lapangan Interaktif</p>
                        <h2 class="mt-1 text-2xl font-extrabold uppercase tracking-tight text-white sm:text-3xl">
                            Cek Ketersediaan Slot & Booking Langsung
                        </h2>
                        <p class="mt-1 text-xs text-white/55 sm:text-sm">
                            Setiap kotak mewakili slot waktu 1 jam. Warna kotak menunjukkan ketersediaan slot secara real-time. Klik kotak hijau untuk langsung booking.
                        </p>
                    </div>

                    <!-- Visual Padel Court Board -->
                    <PadelCourtBoard
                        :courts="featuredCourts ?? []"
                        :bookings="courtBookings ?? []"
                        :initial-date="selectedDate ?? todayDate"
                        route-target="dashboard"
                    />
                </div>

                <!-- SPOTLIGHT: REKOMENDASI VENUE POPULER -->
                <div v-if="featuredVenues?.length" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Pilihan Venue</p>
                            <h2 class="mt-1 text-2xl font-extrabold uppercase tracking-tight text-white">
                                Rekomendasi Venue Padel
                            </h2>
                        </div>
                        <Link
                            :href="route('venues.explore')"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-brand-accent hover:underline sm:text-sm"
                        >
                            Lihat semua venue
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="v in featuredVenues"
                            :key="v.id"
                            class="group flex flex-col justify-between rounded-2xl border border-white/10 bg-white/5 p-5 shadow-sm backdrop-blur transition duration-300 hover:-translate-y-1 hover:border-brand-accent/40 hover:bg-white/[0.08] hover:shadow-xl hover:shadow-brand-accent/5"
                        >
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-white/60">
                                        <MapPin class="h-3.5 w-3.5 text-brand-accent" />
                                        {{ v.city }}
                                    </span>
                                    <span
                                        v-if="v.courts_count"
                                        class="rounded-full bg-brand-accent/20 px-2.5 py-0.5 text-[11px] font-bold text-brand-accent"
                                    >
                                        {{ v.courts_count }} Lapangan
                                    </span>
                                </div>

                                <h3 class="mt-3 text-lg font-bold tracking-tight text-white transition-colors group-hover:text-brand-accent">
                                    {{ v.name }}
                                </h3>

                                <p class="mt-1 line-clamp-2 text-xs text-white/55">
                                    {{ v.description ?? 'Venue padel berstandar internasional dengan fasilitas lengkap.' }}
                                </p>

                                <!-- Amenities Chips -->
                                <div
                                    v-if="v.settings?.amenities?.length"
                                    class="mt-3 flex flex-wrap gap-1.5"
                                >
                                    <span
                                        v-for="am in v.settings.amenities.slice(0, 3)"
                                        :key="am"
                                        class="rounded-md bg-white/10 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wider text-white/70"
                                    >
                                        {{ am }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-5 border-t border-white/10 pt-4">
                                <Link
                                    :href="route('venues.show', v.slug)"
                                    class="inline-flex w-full items-center justify-center gap-1 rounded-xl border border-white/15 bg-white/5 py-2.5 text-xs font-semibold text-white transition hover:border-brand-accent hover:bg-brand-accent hover:text-white"
                                >
                                    Cek Lapangan & Booking
                                    <ChevronRight class="h-3.5 w-3.5" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DUAL SECTION: PROMOS & NOTIFIKASI -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Promo & Voucher Diskon -->
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                        <div class="flex items-center justify-between pb-3">
                            <div>
                                <h3 class="text-base font-bold uppercase text-white">Voucher & Promo Aktif</h3>
                                <p class="text-xs text-white/50">Gunakan kode saat checkout untuk potongan harga langsung.</p>
                            </div>
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/20 text-amber-400">
                                <Tag class="h-4 w-4" />
                            </div>
                        </div>
                        <div class="mt-4">
                            <div v-if="promos.length" class="space-y-3">
                                <div
                                    v-for="p in promos"
                                    :key="p.id"
                                    class="group relative flex flex-col items-start justify-between gap-3.5 rounded-2xl border border-dashed border-amber-500/40 bg-gradient-to-r from-amber-500/15 via-amber-500/5 to-transparent p-4 transition-all hover:border-amber-400 hover:bg-amber-500/15 sm:flex-row sm:items-center"
                                >
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <!-- Value Badge -->
                                        <div
                                            class="flex h-12 min-w-[4.75rem] shrink-0 flex-col items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 px-2.5 text-white shadow-md shadow-amber-500/20"
                                        >
                                            <span class="text-xs font-black tracking-tight leading-none">
                                                {{ fmtPromoBadge(p).value }}
                                            </span>
                                            <span class="mt-0.5 text-[8.5px] font-extrabold uppercase tracking-widest text-amber-100/90">
                                                {{ fmtPromoBadge(p).unit }}
                                            </span>
                                        </div>

                                        <div class="min-w-0 space-y-0.5">
                                            <div class="flex items-center gap-2">
                                                <p class="font-bold text-white text-sm truncate">{{ p.title }}</p>
                                                <span class="rounded bg-amber-500/20 px-1.5 py-0.5 text-[9px] font-bold text-amber-300 uppercase tracking-wider">
                                                    Aktif
                                                </span>
                                            </div>
                                            <p class="truncate text-xs text-white/60">{{ p.description }}</p>
                                            <p class="text-[11px] font-semibold text-amber-300/90 flex items-center gap-1">
                                                <Clock class="h-3 w-3 inline text-amber-400" />
                                                <span>Berlaku s/d {{ fmtDate(p.ends_at) }}</span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Copy Button / Tag Kode Voucher -->
                                    <button
                                        type="button"
                                        class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-dashed border-amber-400/50 bg-black/50 px-3.5 py-2 font-mono text-xs font-black uppercase tracking-wider text-amber-300 shadow-sm transition-all hover:scale-105 hover:border-amber-300 hover:bg-amber-500 hover:text-slate-950 active:scale-95"
                                        @click="copyPromo(p.code)"
                                        :title="`Klik untuk salin kode ${p.code}`"
                                    >
                                        <span v-if="copiedCode === p.code" class="flex items-center gap-1.5 text-emerald-400 font-bold">
                                            <Check class="h-3.5 w-3.5" />
                                            <span>Tersalin!</span>
                                        </span>
                                        <span v-else class="flex items-center gap-1.5">
                                            <Copy class="h-3.5 w-3.5 opacity-70" />
                                            <span>{{ p.code }}</span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            <p v-else class="py-6 text-center text-xs text-white/40">Belum ada promo aktif saat ini.</p>
                        </div>
                    </div>

                    <!-- Notifikasi & Tips Bermain -->
                    <div class="space-y-6">
                        <!-- Notifications -->
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                            <div class="flex items-center justify-between pb-3">
                                <div>
                                    <h3 class="text-base font-bold uppercase text-white">Notifikasi Akun</h3>
                                    <p class="text-xs text-white/50">Pemberitahuan aktivitas & booking lapanganmu.</p>
                                </div>
                                <button
                                    v-if="unreadNotifications > 0"
                                    type="button"
                                    class="text-xs font-semibold text-brand-accent hover:underline"
                                    @click="router.post(route('notifications.read-all'))"
                                >
                                    Tandai dibaca ({{ unreadNotifications }})
                                </button>
                            </div>
                            <div class="mt-4">
                                <div v-if="notifications.length" class="space-y-2.5">
                                    <div
                                        v-for="n in notifications"
                                        :key="n.id"
                                        class="flex items-start gap-3 rounded-xl border p-3 transition"
                                        :class="n.read_at ? 'border-white/10 bg-white/[0.02]' : 'border-brand-accent/40 bg-brand-accent/10'"
                                    >
                                        <span
                                            class="mt-1 h-2 w-2 shrink-0 rounded-full"
                                            :class="n.read_at ? 'bg-transparent' : 'bg-brand-accent animate-pulse'"
                                        />
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-white">{{ n.data.title }}</p>
                                            <p class="text-xs text-white/60">{{ n.data.body }}</p>
                                            <p class="mt-1 text-[10px] text-white/40">{{ timeAgo(n.created_at) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="py-6 text-center text-xs text-white/40">
                                    Belum ada notifikasi baru.
                                </p>
                            </div>
                        </div>

                        <!-- Padel Match Tips & Guidelines -->
                        <div class="rounded-3xl border border-white/10 bg-gradient-to-r from-white/[0.06] to-white/[0.02] p-6 backdrop-blur">
                            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-brand-accent">
                                <Shield class="h-4 w-4" />
                                Panduan & Etika Lapangan Padel
                            </div>
                            <ul class="mt-3.5 space-y-2.5 text-xs text-white/70">
                                <li class="flex items-start gap-2">
                                    <CheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand-accent" />
                                    <span><strong>Sepatu Lapangan:</strong> Gunakan sepatu non-marking sol khusus padel/tenis untuk keamanan turf rumput sintetis.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <CheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand-accent" />
                                    <span><strong>Hadir Tepat Waktu:</strong> Tiba di venue 10–15 menit sebelum slot dimulai untuk pemanasan.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <CheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand-accent" />
                                    <span><strong>Scan QR Tiket:</strong> Tunjukkan kode QR di smartphone kamu langsung ke meja resepsionis venue saat check-in.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
