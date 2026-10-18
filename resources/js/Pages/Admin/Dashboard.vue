<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import Button from '@/Components/ui/Button.vue';
import {
    Activity,
    Ban,
    Banknote,
    Building2,
    CalendarDays,
    Check,
    CheckCircle2,
    ChevronRight,
    Clock,
    Eye,
    Plus,
    Search,
    Shield,
    ShieldCheck,
    UserCheck,
    Users,
    Wrench,
    X,
    XCircle,
} from '@lucide/vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    stats: {
        users: number;
        pending_venues: number;
        approved_venues: number;
        total_courts: number;
        available_courts: number;
        maintenance_courts: number;
        bookings: number;
        revenue: string;
        today_bookings: number;
    };
    courts: Array<{
        id: number;
        venue_id: number;
        name: string;
        type: string;
        price_per_hour: string;
        status: string;
        maintenance_from: string | null;
        maintenance_to: string | null;
        today_bookings_count: number;
        venue: { id: number; name: string; city: string; slug: string };
    }>;
    users: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
        is_active: boolean;
        created_at: string;
    }>;
    pendingVenues: Array<{
        id: number;
        name: string;
        city: string;
        status: string;
        address?: string;
    }>;
    recentBookings: Array<{
        id: number;
        booking_date: string;
        start_time: string;
        end_time: string;
        status: string;
        payment_status: string;
        total_amount: string;
        court: { name: string; venue: { name: string } };
        user: { name: string };
    }>;
}>();

const fmt = (n: string | number) => Number(n).toLocaleString('id-ID');
const fmtPrice = (price: string) => `Rp ${Number(price).toLocaleString('id-ID')}`;
const fmtDate = (d: string) =>
    new Date(`${d}T00:00:00`).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });

// Filtering for courts management
const courtSearch = ref('');
const courtFilter = ref<'all' | 'available' | 'maintenance' | 'indoor' | 'outdoor'>('all');

const filteredCourts = computed(() => {
    return (props.courts ?? []).filter((c) => {
        const matchesSearch =
            c.name.toLowerCase().includes(courtSearch.value.toLowerCase()) ||
            c.venue.name.toLowerCase().includes(courtSearch.value.toLowerCase()) ||
            c.venue.city.toLowerCase().includes(courtSearch.value.toLowerCase());

        if (!matchesSearch) return false;

        if (courtFilter.value === 'all') return true;
        if (courtFilter.value === 'available') return c.status === 'available';
        if (courtFilter.value === 'maintenance') return c.status === 'maintenance';
        if (courtFilter.value === 'indoor') return c.type === 'indoor';
        if (courtFilter.value === 'outdoor') return c.type === 'outdoor';
        return true;
    });
});

const toggleCourt = (courtId: number) => {
    router.post(route('admin.courts.toggle-status', courtId), {}, { preserveScroll: true });
};

const bookingVariant = (s: string) =>
    ({
        pending: 'warning',
        confirmed: 'success',
        checked_in: 'info',
        finished: 'muted',
        cancelled: 'destructive',
        expired: 'muted',
    }[s] ?? 'muted') as 'warning' | 'success' | 'info' | 'muted' | 'destructive';

const bookingLabel = (s: string) => {
    if (s === 'confirmed') return 'Dikonfirmasi';
    if (s === 'pending') return 'Pending Bayar';
    if (s === 'checked_in') return 'Checked In';
    if (s === 'finished') return 'Selesai';
    if (s === 'cancelled') return 'Dibatalkan';
    return s;
};
</script>

<template>
    <Head title="Admin Control Center — PadelHub" />

    <AuthenticatedLayout :dark="true">
        <div class="py-8 sm:py-10 text-white">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- SUPER ADMIN HERO CONTROL CENTER -->
                <div
                    class="relative overflow-hidden rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md sm:p-8"
                >
                    <!-- Ambient Glow -->
                    <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-emerald-500/15 blur-3xl" />
                    <div class="pointer-events-none absolute -bottom-16 right-24 h-48 w-48 rounded-full bg-teal-500/10 blur-2xl" />

                    <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="space-y-2.5 max-w-2xl">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-emerald-400">
                                    <Shield class="h-3.5 w-3.5" />
                                    Super Admin Control Center
                                </span>
                                <span class="rounded-full border border-slate-700/80 bg-slate-800/70 px-3 py-0.5 text-xs font-semibold text-slate-300">
                                    Mode Manajemen & Audit
                                </span>
                            </div>

                            <h1 class="text-2xl font-black uppercase tracking-tight text-white sm:text-3xl">
                                Monitoring & Handling Manajemen Lapangan
                            </h1>

                            <p class="text-xs text-slate-400 sm:text-sm leading-relaxed">
                                Pusat kendali seluruh lapangan padel di Indonesia. Pantau status operasional lapangan, atur jadwal pemeliharaan (maintenance), verifikasi venue mitra, dan monitor aktivitas booking secara real-time.
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
                                    :href="route('venues.create')"
                                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-700/80 bg-slate-800/80 px-4 text-xs font-semibold text-slate-200 transition hover:border-emerald-500/40 hover:bg-slate-700 hover:text-white"
                                >
                                    <Building2 class="h-4 w-4 text-emerald-400" />
                                    <span>Tambah Venue</span>
                                </Link>

                                <Link
                                    :href="route('availability.index')"
                                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-700/80 bg-slate-800/80 px-4 text-xs font-semibold text-slate-200 transition hover:border-emerald-500/40 hover:bg-slate-700 hover:text-white"
                                >
                                    <Clock class="h-4 w-4 text-teal-400" />
                                    <span>Monitoring Slot</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4 PLATFORM & COURT MONITORING METRICS -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Metric 1: Lapangan Terdaftar -->
                    <div class="group rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-lg backdrop-blur-md transition hover:border-emerald-500/30 hover:bg-slate-800/40">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Lapangan</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400 ring-1 ring-emerald-500/30">
                                <CalendarDays class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-3xl font-black tracking-tight text-white">{{ stats.total_courts }}</p>
                        <div class="mt-2 flex items-center gap-2 text-xs">
                            <span class="rounded bg-emerald-500/20 px-1.5 py-0.5 font-bold text-emerald-300">
                                {{ stats.available_courts }} Tersedia
                            </span>
                            <span class="rounded bg-amber-500/20 px-1.5 py-0.5 font-bold text-amber-300">
                                {{ stats.maintenance_courts }} Maintenance
                            </span>
                        </div>
                    </div>

                    <!-- Metric 2: Venue Terverifikasi -->
                    <div class="group rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-lg backdrop-blur-md transition hover:border-emerald-500/30 hover:bg-slate-800/40">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Mitra Venue</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30">
                                <ShieldCheck class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-3xl font-black tracking-tight text-white">{{ stats.approved_venues }}</p>
                        <div class="mt-2 flex items-center gap-2 text-xs text-slate-400">
                            <span v-if="stats.pending_venues > 0" class="rounded bg-amber-500/20 px-1.5 py-0.5 font-bold text-amber-300 animate-pulse">
                                {{ stats.pending_venues }} Menunggu Review
                            </span>
                            <span v-else class="text-emerald-400">Semua Terverifikasi</span>
                        </div>
                    </div>

                    <!-- Metric 3: Booking Hari Ini -->
                    <div class="group rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-lg backdrop-blur-md transition hover:border-emerald-500/30 hover:bg-slate-800/40">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Sesi Hari Ini</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-500/20 text-purple-400 ring-1 ring-purple-500/30">
                                <Activity class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-3xl font-black tracking-tight text-white">{{ stats.today_bookings }}</p>
                        <p class="mt-2 text-xs text-slate-400">Total pesanan platform: {{ fmt(stats.bookings) }}</p>
                    </div>

                    <!-- Metric 4: Total Revenue -->
                    <div class="group rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-lg backdrop-blur-md transition hover:border-emerald-500/30 hover:bg-slate-800/40">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pendapatan Platform</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/20 text-amber-400 ring-1 ring-amber-500/30">
                                <Banknote class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-2xl font-black tracking-tight text-emerald-400">Rp {{ fmt(stats.revenue) }}</p>
                        <p class="mt-2 text-xs text-slate-400">Transaksi sukses & lunas</p>
                    </div>
                </div>

                <!-- MAIN SECTION: COURT MONITORING & HANDLING OPERATIONS -->
                <div class="space-y-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400">Operasional Court</p>
                            <h2 class="text-2xl font-black uppercase tracking-tight text-white">
                                Tabel Monitoring & Handling Manajemen Lapangan
                            </h2>
                            <p class="text-xs text-slate-400">
                                Pantau status operasional tiap lapangan, ubah status ke maintenance, atau atur tarif sewa.
                            </p>
                        </div>

                        <Link
                            :href="route('courts.index')"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-400 hover:text-emerald-300 hover:underline"
                        >
                            Buka modul manajemen lapangan penuh
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </div>

                    <!-- Filter & Search Controls -->
                    <div class="flex flex-col gap-3 rounded-2xl border border-slate-800/80 bg-slate-900/60 p-4 shadow-xl backdrop-blur-md sm:flex-row sm:items-center sm:justify-between">
                        <!-- Search Box -->
                        <div class="relative w-full sm:w-80">
                            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                            <input
                                v-model="courtSearch"
                                type="text"
                                placeholder="Cari lapangan, venue, atau kota..."
                                class="h-9 w-full rounded-xl border border-slate-800/90 bg-slate-950/80 pl-9 pr-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50"
                            />
                        </div>

                        <!-- Filter Tabs -->
                        <div class="flex flex-wrap items-center gap-1.5 text-xs">
                            <button
                                type="button"
                                class="rounded-xl px-3 py-1.5 transition font-semibold"
                                :class="courtFilter === 'all' ? 'bg-gradient-to-r from-emerald-500 to-green-600 text-slate-950 font-bold shadow-md shadow-emerald-500/20' : 'border border-slate-700/60 bg-slate-800/60 text-slate-300 hover:text-white hover:bg-slate-700/80'"
                                @click="courtFilter = 'all'"
                            >
                                Semua ({{ courts.length }})
                            </button>
                            <button
                                type="button"
                                class="rounded-xl px-3 py-1.5 transition font-semibold"
                                :class="courtFilter === 'available' ? 'bg-gradient-to-r from-emerald-500 to-green-600 text-slate-950 font-bold shadow-md shadow-emerald-500/20' : 'border border-slate-700/60 bg-slate-800/60 text-slate-300 hover:text-white hover:bg-slate-700/80'"
                                @click="courtFilter = 'available'"
                            >
                                Tersedia ({{ stats.available_courts }})
                            </button>
                            <button
                                type="button"
                                class="rounded-xl px-3 py-1.5 transition font-semibold"
                                :class="courtFilter === 'maintenance' ? 'bg-gradient-to-r from-emerald-500 to-green-600 text-slate-950 font-bold shadow-md shadow-emerald-500/20' : 'border border-slate-700/60 bg-slate-800/60 text-slate-300 hover:text-white hover:bg-slate-700/80'"
                                @click="courtFilter = 'maintenance'"
                            >
                                Maintenance ({{ stats.maintenance_courts }})
                            </button>
                            <button
                                type="button"
                                class="rounded-xl px-3 py-1.5 transition font-semibold"
                                :class="courtFilter === 'indoor' ? 'bg-gradient-to-r from-emerald-500 to-green-600 text-slate-950 font-bold shadow-md shadow-emerald-500/20' : 'border border-slate-700/60 bg-slate-800/60 text-slate-300 hover:text-white hover:bg-slate-700/80'"
                                @click="courtFilter = 'indoor'"
                            >
                                Indoor
                            </button>
                            <button
                                type="button"
                                class="rounded-xl px-3 py-1.5 transition font-semibold"
                                :class="courtFilter === 'outdoor' ? 'bg-gradient-to-r from-emerald-500 to-green-600 text-slate-950 font-bold shadow-md shadow-emerald-500/20' : 'border border-slate-700/60 bg-slate-800/60 text-slate-300 hover:text-white hover:bg-slate-700/80'"
                                @click="courtFilter = 'outdoor'"
                            >
                                Outdoor
                            </button>
                        </div>
                    </div>

                    <!-- Courts Management Table -->
                    <div class="overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 shadow-xl backdrop-blur-md">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="border-b border-slate-800/80 bg-slate-950/70 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    <tr>
                                        <th class="px-5 py-4">Nama Lapangan</th>
                                        <th class="px-5 py-4">Venue & Lokasi</th>
                                        <th class="px-5 py-4">Tipe</th>
                                        <th class="px-5 py-4">Tarif Sewa</th>
                                        <th class="px-5 py-4">Status Operasional</th>
                                        <th class="px-5 py-4">Booking Hari Ini</th>
                                        <th class="px-5 py-4 text-right">Tindakan Handling</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60">
                                    <tr
                                        v-for="c in filteredCourts"
                                        :key="c.id"
                                        class="transition hover:bg-slate-800/30"
                                    >
                                        <!-- Court Name -->
                                        <td class="px-5 py-4 font-bold text-white">
                                            <div class="flex items-center gap-2">
                                                <span class="h-2 w-2 rounded-full" :class="c.status === 'available' ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'" />
                                                <span class="text-sm">{{ c.name }}</span>
                                            </div>
                                        </td>

                                        <!-- Venue -->
                                        <td class="px-5 py-4">
                                            <p class="font-semibold text-white/90">{{ c.venue.name }}</p>
                                            <p class="text-[11px] text-slate-400">{{ c.venue.city }}</p>
                                        </td>

                                        <!-- Type -->
                                        <td class="px-5 py-4">
                                            <span class="rounded-md border border-slate-700/60 bg-slate-800/60 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-300">
                                                {{ c.type }}
                                            </span>
                                        </td>

                                        <!-- Price -->
                                        <td class="px-5 py-4 font-bold text-emerald-400">
                                            {{ fmtPrice(c.price_per_hour) }}<span class="text-[10px] text-slate-400">/jam</span>
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="px-5 py-4">
                                            <span
                                                v-if="c.status === 'available'"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 px-2.5 py-1 text-[11px] font-bold text-emerald-300 ring-1 ring-emerald-500/30"
                                            >
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" />
                                                Tersedia
                                            </span>
                                            <span
                                                v-else-if="c.status === 'maintenance'"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/20 px-2.5 py-1 text-[11px] font-bold text-amber-300 ring-1 ring-amber-500/30"
                                            >
                                                <Wrench class="h-3 w-3" />
                                                Maintenance
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex items-center rounded-full bg-slate-800/80 px-2.5 py-1 text-[11px] font-medium text-slate-400"
                                            >
                                                Nonaktif
                                            </span>
                                        </td>

                                        <!-- Bookings Today -->
                                        <td class="px-5 py-4 font-semibold text-white">
                                            <span class="rounded-lg border border-slate-800 bg-slate-950/60 px-2.5 py-1 text-xs">
                                                {{ c.today_bookings_count }} sesi
                                            </span>
                                        </td>

                                        <!-- Actions (Handling) -->
                                        <td class="px-5 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <!-- Quick Toggle Status Button -->
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition shadow-sm"
                                                    :class="c.status === 'available'
                                                        ? 'bg-amber-500/15 text-amber-300 hover:bg-amber-500/25 border border-amber-500/30'
                                                        : 'bg-emerald-500/15 text-emerald-300 hover:bg-emerald-500/25 border border-emerald-500/30'"
                                                    @click="toggleCourt(c.id)"
                                                    :title="c.status === 'available' ? 'Ubah menjadi Maintenance' : 'Aktifkan Lapangan'"
                                                >
                                                    <Wrench v-if="c.status === 'available'" class="h-3.5 w-3.5" />
                                                    <Check v-else class="h-3.5 w-3.5" />
                                                    <span>{{ c.status === 'available' ? 'Set Maintenance' : 'Set Tersedia' }}</span>
                                                </button>

                                                <!-- Edit Court Link -->
                                                <Link
                                                    :href="route('courts.edit', c.id)"
                                                    class="rounded-xl border border-slate-700/80 bg-slate-800/80 px-3 py-1.5 text-xs font-semibold text-slate-200 hover:border-emerald-500/40 hover:bg-slate-700 hover:text-white transition"
                                                >
                                                    Edit
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr v-if="!filteredCourts.length">
                                        <td colspan="7" class="py-8 text-center text-xs text-slate-400">
                                            Tidak ada lapangan yang sesuai filter pencarian.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- DUAL SECTION: VENUE APPROVALS & USER MANAGEMENT -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Venue Approvals -->
                    <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-xl backdrop-blur-md">
                        <div class="flex items-center justify-between pb-3">
                            <div>
                                <h3 class="text-base font-bold uppercase text-white">Persetujuan Venue Baru</h3>
                                <p class="text-xs text-slate-400">Permohonan pendaftaran venue mitra yang menunggu verifikasi admin.</p>
                            </div>
                            <span class="rounded-full border border-slate-700/60 bg-slate-800/70 px-2.5 py-0.5 text-xs font-bold text-slate-300">
                                {{ pendingVenues.length }} Menunggu
                            </span>
                        </div>

                        <div class="mt-4">
                            <div v-if="pendingVenues.length" class="space-y-3">
                                <div
                                    v-for="v in pendingVenues"
                                    :key="v.id"
                                    class="flex items-center justify-between gap-3 rounded-2xl border border-slate-800/80 bg-slate-950/60 p-4 transition hover:bg-slate-800/40"
                                >
                                    <div class="min-w-0">
                                        <p class="font-bold text-white">{{ v.name }}</p>
                                        <p class="text-xs text-slate-400">{{ v.city }} · {{ v.address ?? 'Alamat lengkap' }}</p>
                                    </div>

                                    <div class="flex shrink-0 gap-2">
                                        <Button
                                            variant="accent"
                                            size="sm"
                                            class="gap-1 font-bold shadow-md shadow-emerald-500/20"
                                            @click="router.post(route('admin.venues.approve', v.id))"
                                        >
                                            <Check class="h-3.5 w-3.5 stroke-[2.5]" />
                                            Setujui
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            class="border-rose-500/30 text-rose-300 hover:bg-rose-500/20"
                                            @click="router.post(route('admin.venues.reject', v.id))"
                                        >
                                            <X class="h-3.5 w-3.5" />
                                            Tolak
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="rounded-2xl border border-dashed border-slate-800 p-8 text-center text-slate-400">
                                <CheckCircle2 class="mx-auto h-8 w-8 text-emerald-400" />
                                <p class="mt-2 text-sm font-semibold text-white">Semua Venue Terverifikasi</p>
                                <p class="text-xs text-slate-500">Tidak ada pengajuan venue yang menunggu persetujuan saat ini.</p>
                            </div>
                        </div>
                    </div>

                    <!-- User Management -->
                    <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-xl backdrop-blur-md">
                        <div class="flex items-center justify-between pb-3">
                            <div>
                                <h3 class="text-base font-bold uppercase text-white">Manajemen Akun Pengguna</h3>
                                <p class="text-xs text-slate-400">Audit status keaktifan user dan hak akses sistem.</p>
                            </div>
                            <span class="rounded-full border border-slate-700/60 bg-slate-800/70 px-2.5 py-0.5 text-xs font-bold text-slate-300">
                                {{ users.length }} Pengguna
                            </span>
                        </div>

                        <div class="mt-4">
                            <div class="max-h-80 space-y-2.5 overflow-y-auto pr-1">
                                <div
                                    v-for="u in users"
                                    :key="u.id"
                                    class="flex items-center justify-between gap-3 rounded-2xl border border-slate-800/80 bg-slate-950/60 p-3 transition hover:bg-slate-800/40"
                                >
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="truncate font-semibold text-white">{{ u.name }}</p>
                                            <span
                                                class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase"
                                                :class="u.role === 'admin' ? 'bg-rose-500/20 text-rose-300' : u.role === 'venue_owner' ? 'bg-blue-500/20 text-blue-300' : 'bg-slate-800 text-slate-300'"
                                            >
                                                {{ u.role }}
                                            </span>
                                        </div>
                                        <p class="truncate text-xs text-slate-400">{{ u.email }}</p>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <span
                                            class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                                            :class="u.is_active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300'"
                                        >
                                            {{ u.is_active ? 'Aktif' : 'Suspend' }}
                                        </span>

                                        <button
                                            v-if="u.role !== 'admin'"
                                            type="button"
                                            class="inline-flex items-center gap-1 rounded-xl border border-slate-700/80 bg-slate-800/70 px-2.5 py-1.5 text-[11px] font-medium transition hover:bg-slate-700"
                                            :class="u.is_active ? 'text-rose-300 hover:border-rose-500/40' : 'text-emerald-300 hover:border-emerald-500/40'"
                                            @click="router.post(route('admin.users.toggle', u.id))"
                                        >
                                            <Ban v-if="u.is_active" class="h-3 w-3" />
                                            <UserCheck v-else class="h-3 w-3" />
                                            <span>{{ u.is_active ? 'Suspend' : 'Aktifkan' }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION: MONITORING BOOKING PEMAIN TERBARU -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400">Audit Transaksi</p>
                            <h2 class="text-2xl font-black uppercase tracking-tight text-white">
                                Monitoring Pesanan & Booking Pemain
                            </h2>
                            <p class="text-xs text-slate-400">Daftar booking pemain terkini di seluruh lapangan padel mitra.</p>
                        </div>

                        <Link
                            :href="route('bookings.index')"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-400 hover:text-emerald-300 hover:underline"
                        >
                            Lihat seluruh booking
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 shadow-xl backdrop-blur-md">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="border-b border-slate-800/80 bg-slate-950/70 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    <tr>
                                        <th class="px-5 py-4">Pemain</th>
                                        <th class="px-5 py-4">Lapangan & Venue</th>
                                        <th class="px-5 py-4">Tanggal & Jam</th>
                                        <th class="px-5 py-4">Status Booking</th>
                                        <th class="px-5 py-4">Pembayaran</th>
                                        <th class="px-5 py-4 text-right">Total Transaksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60">
                                    <tr
                                        v-for="b in recentBookings"
                                        :key="b.id"
                                        class="transition hover:bg-slate-800/30"
                                    >
                                        <!-- Player Name -->
                                        <td class="px-5 py-4 font-bold text-white">
                                            {{ b.user.name }}
                                        </td>

                                        <!-- Court & Venue -->
                                        <td class="px-5 py-4">
                                            <p class="font-semibold text-white/90">{{ b.court.name }}</p>
                                            <p class="text-[11px] text-slate-400">{{ b.court.venue.name }}</p>
                                        </td>

                                        <!-- Date & Hour -->
                                        <td class="px-5 py-4 text-slate-200">
                                            <p class="font-semibold">{{ fmtDate(b.booking_date) }}</p>
                                            <p class="text-[11px] text-slate-400">{{ b.start_time }} – {{ b.end_time }} WIB</p>
                                        </td>

                                        <!-- Status Booking -->
                                        <td class="px-5 py-4">
                                            <Badge :variant="bookingVariant(b.status)">
                                                {{ bookingLabel(b.status) }}
                                            </Badge>
                                        </td>

                                        <!-- Payment Status -->
                                        <td class="px-5 py-4">
                                            <span
                                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                                :class="b.payment_status === 'paid' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300'"
                                            >
                                                {{ b.payment_status === 'paid' ? 'Lunas' : 'Pending' }}
                                            </span>
                                        </td>

                                        <!-- Total Amount -->
                                        <td class="px-5 py-4 text-right font-black text-emerald-400 text-sm">
                                            Rp {{ fmt(b.total_amount) }}
                                        </td>
                                    </tr>

                                    <tr v-if="!recentBookings.length">
                                        <td colspan="6" class="py-8 text-center text-xs text-slate-400">
                                            Belum ada data pesanan booking.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
