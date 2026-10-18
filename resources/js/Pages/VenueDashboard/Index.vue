<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import {
    Activity,
    Banknote,
    Building2,
    CalendarCheck,
    CalendarDays,
    CheckCircle2,
    ChevronRight,
    Clock,
    CreditCard,
    Layers,
    Plus,
    QrCode,
    TrendingUp,
} from '@lucide/vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface RevenueData {
    today?: number | string;
    week?: number | string;
    month?: number | string;
}

interface BookingsData {
    today?: number;
    week?: number;
    month?: number;
}

interface TrendDay {
    date: string;
    label: string;
    bookings: number;
    revenue: number;
}

interface PeakHour {
    hour: string;
    total: number | string;
}

interface CourtUsage {
    court: string;
    total: number;
}

interface VenueItem {
    id: number;
    name: string;
    city: string;
    status: string;
    courts_count?: number;
}

interface RecentBooking {
    id: number;
    booking_date: string;
    start_time: string;
    end_time: string;
    status: string;
    payment_status: string;
    total_amount: number | string;
    user?: { name: string; email?: string };
    court?: { name: string; venue?: { name: string } };
}

const props = withDefaults(
    defineProps<{
        revenue?: RevenueData;
        bookings?: BookingsData;
        trendDays?: TrendDay[];
        maxRevenue?: number;
        maxBookings?: number;
        peakHours?: PeakHour[];
        courtUsage?: CourtUsage[];
        venues?: VenueItem[];
        recentBookings?: RecentBooking[];
        stats?: {
            totalRevenue?: string | number;
            totalBookings?: number;
            occupancyRate?: number;
        };
    }>(),
    {
        revenue: () => ({ today: 0, week: 0, month: 0 }),
        bookings: () => ({ today: 0, week: 0, month: 0 }),
        trendDays: () => [],
        maxRevenue: 1,
        maxBookings: 1,
        peakHours: () => [],
        courtUsage: () => [],
        venues: () => [],
        recentBookings: () => [],
    },
);

const fmt = (n: number | string | undefined | null) => {
    if (n === undefined || n === null || isNaN(Number(n))) return '0';
    return Number(n).toLocaleString('id-ID');
};

const fmtDate = (d: string) => {
    try {
        return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    } catch {
        return d;
    }
};

const metricCards = computed(() => [
    {
        label: 'Omzet Hari Ini',
        value: `Rp ${fmt(props.revenue?.today ?? 0)}`,
        subtitle: 'Pendapatan lunas hari ini',
        icon: Banknote,
        color: 'text-emerald-400',
        bg: 'bg-emerald-500/15 ring-emerald-500/30',
    },
    {
        label: 'Omzet Minggu Ini',
        value: `Rp ${fmt(props.revenue?.week ?? 0)}`,
        subtitle: 'Akumulasi minggu berjalan',
        icon: TrendingUp,
        color: 'text-teal-400',
        bg: 'bg-teal-500/15 ring-teal-500/30',
    },
    {
        label: 'Omzet Bulan Ini',
        value: `Rp ${fmt(props.revenue?.month ?? 0)}`,
        subtitle: 'Total transaksi bulan ini',
        icon: CalendarDays,
        color: 'text-sky-400',
        bg: 'bg-sky-500/15 ring-sky-500/30',
    },
    {
        label: 'Booking Hari Ini',
        value: `${fmt(props.bookings?.today ?? 0)} Sesi`,
        subtitle: 'Pemesanan aktif hari ini',
        icon: Activity,
        color: 'text-emerald-400',
        bg: 'bg-emerald-500/15 ring-emerald-500/30',
    },
    {
        label: 'Booking Minggu Ini',
        value: `${fmt(props.bookings?.week ?? 0)} Sesi`,
        subtitle: 'Total sesi minggu berjalan',
        icon: CalendarCheck,
        color: 'text-teal-400',
        bg: 'bg-teal-500/15 ring-teal-500/30',
    },
    {
        label: 'Booking Bulan Ini',
        value: `${fmt(props.bookings?.month ?? 0)} Sesi`,
        subtitle: 'Total sesi bulan ini',
        icon: Layers,
        color: 'text-sky-400',
        bg: 'bg-sky-500/15 ring-sky-500/30',
    },
]);

const safeTrendDays = computed(() => props.trendDays ?? []);

const effectiveMaxRev = computed(() => {
    const values = safeTrendDays.value.map((d) => Number(d.revenue) || 0);
    const maxVal = Math.max(...values, Number(props.maxRevenue) || 1, 1);
    return maxVal > 0 ? maxVal : 1;
});

const effectiveMaxBookings = computed(() => {
    const values = safeTrendDays.value.map((d) => Number(d.bookings) || 0);
    const maxVal = Math.max(...values, Number(props.maxBookings) || 1, 1);
    return maxVal > 0 ? maxVal : 1;
});

const revenueHeight = (r: number | string) => {
    const val = Number(r) || 0;
    return `${Math.max(Math.round((val / effectiveMaxRev.value) * 100), 8)}%`;
};

const bookingHeight = (b: number | string) => {
    const val = Number(b) || 0;
    return `${Math.max(Math.round((val / effectiveMaxBookings.value) * 100), 8)}%`;
};

const safePeakHours = computed(() => props.peakHours ?? []);
const safeCourtUsage = computed(() => props.courtUsage ?? []);

const maxCourt = computed(() => {
    const list = safeCourtUsage.value;
    if (!list.length) return 1;
    return Math.max(...list.map((c) => Number(c.total) || 0), 1);
});

const bookingVariant = (status: string) => {
    if (status === 'confirmed') return 'success';
    if (status === 'checked_in') return 'info';
    if (status === 'pending') return 'warning';
    if (status === 'finished') return 'secondary';
    return 'outline';
};

const bookingLabel = (status: string) => {
    if (status === 'confirmed') return 'Terkonfirmasi';
    if (status === 'checked_in') return 'Check-In';
    if (status === 'pending') return 'Menunggu';
    if (status === 'finished') return 'Selesai';
    if (status === 'cancelled') return 'Batal';
    return status;
};
</script>

<template>
    <Head title="Owner Dashboard — PadelHub" />

    <AuthenticatedLayout :dark="true">
        <div class="py-8 sm:py-10 text-white">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- HERO SECTION: OWNER DASHBOARD & CONTROL CENTER -->
                <div class="relative overflow-hidden rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md sm:p-8">
                    <!-- Ambient Glow -->
                    <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-emerald-500/15 blur-3xl" />
                    <div class="pointer-events-none absolute -bottom-16 right-24 h-48 w-48 rounded-full bg-teal-500/10 blur-2xl" />

                    <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="space-y-2.5 max-w-2xl">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-emerald-400">
                                    <TrendingUp class="h-3.5 w-3.5" />
                                    Owner Dashboard & Analytics
                                </span>
                                <span class="rounded-full border border-slate-700/80 bg-slate-800/70 px-3 py-0.5 text-xs font-semibold text-slate-300">
                                    Manajemen Finansial & Lapangan
                                </span>
                            </div>

                            <h1 class="text-2xl font-black uppercase tracking-tight text-white sm:text-3xl">
                                Ringkasan Bisnis & Performa Lapangan
                            </h1>

                            <p class="text-xs text-slate-400 sm:text-sm leading-relaxed">
                                Pantau perputaran omzet harian, volume reservasi sesi, jam ramai pengunjung, serta tingkat okupansi seluruh venue Anda secara real-time.
                            </p>
                        </div>

                        <!-- Management Action Toolbar -->
                        <div class="shrink-0 rounded-2xl border border-slate-800/80 bg-slate-950/70 p-2 sm:p-2.5 shadow-xl backdrop-blur-md">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                <Link
                                    :href="route('courts.create')"
                                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-4 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition hover:scale-105 hover:from-emerald-400 hover:to-green-500"
                                >
                                    <Plus class="h-4 w-4 stroke-[2.5]" />
                                    <span>Tambah Lapangan</span>
                                </Link>

                                <Link
                                    :href="route('venues.index')"
                                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-700/80 bg-slate-800/80 px-4 text-xs font-semibold text-slate-200 transition hover:border-emerald-500/40 hover:bg-slate-700 hover:text-white"
                                >
                                    <Building2 class="h-4 w-4 text-emerald-400" />
                                    <span>Venue Saya</span>
                                </Link>

                                <Link
                                    :href="route('checkin.index')"
                                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-700/80 bg-slate-800/80 px-4 text-xs font-semibold text-slate-200 transition hover:border-emerald-500/40 hover:bg-slate-700 hover:text-white"
                                >
                                    <QrCode class="h-4 w-4 text-sky-400" />
                                    <span>Scan Check-in</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6 KPI METRICS GRID -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="m in metricCards"
                        :key="m.label"
                        class="group relative overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-lg backdrop-blur-md transition hover:border-emerald-500/30 hover:bg-slate-800/40"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ m.label }}</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl ring-1" :class="[m.bg, m.color]">
                                <component :is="m.icon" class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-2xl font-black tracking-tight text-white">{{ m.value }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ m.subtitle }}</p>
                    </div>
                </div>

                <!-- CHARTS & TRENDS GRID -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Revenue Trend -->
                    <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md">
                        <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                            <div>
                                <h3 class="text-sm font-black uppercase tracking-wider text-white">Tren Omzet 7 Hari Terakhir</h3>
                                <p class="text-xs text-slate-400">Total pendapatan harian yang sudah lunas</p>
                            </div>
                            <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-400">
                                7 Hari
                            </span>
                        </div>
                        <div class="flex h-48 items-end gap-2.5 pt-6">
                            <div v-for="d in safeTrendDays" :key="d.date" class="flex flex-1 flex-col items-center gap-2">
                                <div
                                    class="w-full rounded-t-lg bg-gradient-to-t from-emerald-600 to-emerald-400 transition-all hover:brightness-125 hover:shadow-lg hover:shadow-emerald-500/20"
                                    :style="{ height: revenueHeight(d.revenue) }"
                                    :title="`${d.date}: Rp ${fmt(d.revenue)}`"
                                />
                                <span class="text-[11px] font-semibold text-slate-400">{{ d.label }}</span>
                            </div>
                            <div v-if="!safeTrendDays.length" class="flex h-full w-full items-center justify-center text-xs text-slate-400">
                                Belum ada data transaksi dalam 7 hari terakhir.
                            </div>
                        </div>
                    </div>

                    <!-- Booking Volume Trend -->
                    <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md">
                        <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                            <div>
                                <h3 class="text-sm font-black uppercase tracking-wider text-white">Tren Booking 7 Hari Terakhir</h3>
                                <p class="text-xs text-slate-400">Frekuensi pemesanan sesi sewa lapangan</p>
                            </div>
                            <span class="rounded-full border border-sky-500/30 bg-sky-500/10 px-3 py-1 text-xs font-bold text-sky-400">
                                Sesi
                            </span>
                        </div>
                        <div class="flex h-48 items-end gap-2.5 pt-6">
                            <div v-for="d in safeTrendDays" :key="d.date" class="flex flex-1 flex-col items-center gap-2">
                                <div
                                    class="w-full rounded-t-lg bg-gradient-to-t from-sky-600 to-sky-400 transition-all hover:brightness-125 hover:shadow-lg hover:shadow-sky-500/20"
                                    :style="{ height: bookingHeight(d.bookings) }"
                                    :title="`${d.date}: ${d.bookings} sesi`"
                                />
                                <span class="text-[11px] font-semibold text-slate-400">{{ d.label }}</span>
                            </div>
                            <div v-if="!safeTrendDays.length" class="flex h-full w-full items-center justify-center text-xs text-slate-400">
                                Belum ada data booking dalam 7 hari terakhir.
                            </div>
                        </div>
                    </div>

                    <!-- Jam Main Terpadat (Peak Hours) -->
                    <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md">
                        <div class="flex items-center gap-2 border-b border-slate-800/80 pb-4">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-500/30">
                                <Clock class="h-4 w-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-black uppercase tracking-wider text-white">Jam Main Terpadat (Peak Hours)</h3>
                                <p class="text-xs text-slate-400">Waktu favorit pemain dalam 30 hari terakhir</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <ul v-if="safePeakHours.length" class="space-y-2.5">
                                <li
                                    v-for="p in safePeakHours"
                                    :key="p.hour"
                                    class="flex items-center justify-between rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-xs transition hover:bg-slate-800/30"
                                >
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-md bg-slate-800 text-[10px] font-bold text-slate-300">
                                            #
                                        </span>
                                        <span class="font-bold text-white text-sm">{{ p.hour }} WIB</span>
                                    </div>
                                    <span class="rounded-lg bg-emerald-500/15 border border-emerald-500/30 px-3 py-1 font-mono font-bold text-emerald-400 text-xs">
                                        {{ p.total }} Sesi Booking
                                    </span>
                                </li>
                            </ul>
                            <div v-else class="rounded-2xl border border-dashed border-slate-800 p-8 text-center text-slate-400 text-xs">
                                Belum ada data reservasi jam padat dalam 30 hari terakhir.
                            </div>
                        </div>
                    </div>

                    <!-- Pemakaian Lapangan -->
                    <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md">
                        <div class="flex items-center gap-2 border-b border-slate-800/80 pb-4">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-500/15 text-teal-400 ring-1 ring-teal-500/30">
                                <Layers class="h-4 w-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-black uppercase tracking-wider text-white">Tingkat Penggunaan Lapangan</h3>
                                <p class="text-xs text-slate-400">Distribusi sesi berdasarkan lapangan mitra</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div v-if="safeCourtUsage.length" class="space-y-4">
                                <div v-for="c in safeCourtUsage" :key="c.court" class="space-y-1.5">
                                    <div class="flex justify-between text-xs">
                                        <span class="font-bold text-white">{{ c.court }}</span>
                                        <span class="font-mono text-emerald-400 font-semibold">{{ c.total }} Sesi</span>
                                    </div>
                                    <div class="h-2.5 overflow-hidden rounded-full bg-slate-950 border border-slate-800">
                                        <div
                                            class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-500"
                                            :style="{ width: `${Math.min(100, Math.round((Number(c.total) / maxCourt) * 100))}%` }"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div v-else class="rounded-2xl border border-dashed border-slate-800 p-8 text-center text-slate-400 text-xs">
                                Belum ada data statistik pemakaian lapangan.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DUAL SECTION: VENUE SAYA & PESANAN TERKINI -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Venue Saya -->
                    <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md">
                        <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-500/30">
                                    <Building2 class="h-4 w-4" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-black uppercase tracking-wider text-white">Venue Terdaftar</h3>
                                    <p class="text-xs text-slate-400">Daftar lokasi venue yang Anda kelola</p>
                                </div>
                            </div>

                            <Link
                                :href="route('venues.index')"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-400 hover:text-emerald-300 hover:underline"
                            >
                                Kelola
                                <ChevronRight class="h-3.5 w-3.5" />
                            </Link>
                        </div>

                        <div class="mt-4">
                            <div v-if="venues && venues.length" class="space-y-3">
                                <div
                                    v-for="v in venues"
                                    :key="v.id"
                                    class="flex items-center justify-between rounded-2xl border border-slate-800/80 bg-slate-950/60 p-4 transition hover:bg-slate-800/30"
                                >
                                    <div>
                                        <p class="font-bold text-white text-sm">{{ v.name }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ v.city }} · {{ v.courts_count ?? 0 }} Lapangan</p>
                                    </div>
                                    <span
                                        class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                        :class="v.status === 'approved' ? 'bg-emerald-500/20 text-emerald-300 ring-1 ring-emerald-500/30' : 'bg-amber-500/20 text-amber-300 ring-1 ring-amber-500/30'"
                                    >
                                        {{ v.status === 'approved' ? 'Aktif' : 'Review' }}
                                    </span>
                                </div>
                            </div>
                            <div v-else class="rounded-2xl border border-dashed border-slate-800 p-8 text-center text-slate-400 text-xs">
                                Belum ada venue terdaftar.
                                <div class="mt-3">
                                    <Link
                                        :href="route('venues.create')"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-3 py-1.5 text-xs font-bold text-slate-950 hover:bg-emerald-400"
                                    >
                                        <Plus class="h-3.5 w-3.5" />
                                        <span>Daftarkan Venue Sekarang</span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pesanan Booking Terkini -->
                    <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md">
                        <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/15 text-sky-400 ring-1 ring-sky-500/30">
                                    <CreditCard class="h-4 w-4" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-black uppercase tracking-wider text-white">Booking Terkini</h3>
                                    <p class="text-xs text-slate-400">Pemesanan terbaru di venue Anda</p>
                                </div>
                            </div>

                            <Link
                                :href="route('bookings.index')"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-400 hover:text-emerald-300 hover:underline"
                            >
                                Seluruh Booking
                                <ChevronRight class="h-3.5 w-3.5" />
                            </Link>
                        </div>

                        <div class="mt-4">
                            <div v-if="recentBookings && recentBookings.length" class="space-y-3">
                                <div
                                    v-for="b in recentBookings"
                                    :key="b.id"
                                    class="flex items-center justify-between rounded-2xl border border-slate-800/80 bg-slate-950/60 p-3.5 transition hover:bg-slate-800/30"
                                >
                                    <div class="min-w-0 pr-3">
                                        <p class="font-bold text-white text-sm truncate">{{ b.user?.name ?? 'Pemain' }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5 truncate">
                                            {{ b.court?.name ?? 'Lapangan' }} · {{ fmtDate(b.booking_date) }} ({{ b.start_time }})
                                        </p>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2.5">
                                        <Badge :variant="bookingVariant(b.status)">
                                            {{ bookingLabel(b.status) }}
                                        </Badge>
                                        <span class="font-mono font-bold text-emerald-400 text-xs">
                                            Rp {{ fmt(b.total_amount) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="rounded-2xl border border-dashed border-slate-800 p-8 text-center text-slate-400 text-xs">
                                Belum ada riwayat pesanan booking terbaru.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
