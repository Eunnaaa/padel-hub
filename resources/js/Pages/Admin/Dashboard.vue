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
    <Head title="Admin Dashboard — PadelHub" />

    <AuthenticatedLayout :dark="true">
        <div class="py-8 text-white">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- PAGE HEADER: CLEAN & PROFESSIONAL -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">Dashboard Admin</h1>
                            <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-400">
                                Super Admin
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-slate-400">
                            Pusat kendali operasional lapangan, verifikasi mitra venue, dan transaksi platform.
                        </p>
                    </div>

                    <!-- Management Action Toolbar -->
                    <div class="flex flex-wrap items-center gap-2">
                        <Link
                            :href="route('courts.create')"
                            class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-emerald-500 px-3.5 text-xs font-semibold text-slate-950 shadow-sm transition hover:bg-emerald-400"
                        >
                            <Plus class="h-4 w-4 stroke-[2.5]" />
                            <span>Tambah Lapangan</span>
                        </Link>

                        <Link
                            :href="route('venues.create')"
                            class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-slate-800 bg-slate-900/80 px-3.5 text-xs font-medium text-slate-200 transition hover:border-slate-700 hover:bg-slate-800 hover:text-white"
                        >
                            <Building2 class="h-3.5 w-3.5 text-slate-400" />
                            <span>Tambah Venue</span>
                        </Link>

                        <Link
                            :href="route('availability.index')"
                            class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-slate-800 bg-slate-900/80 px-3.5 text-xs font-medium text-slate-200 transition hover:border-slate-700 hover:bg-slate-800 hover:text-white"
                        >
                            <Clock class="h-3.5 w-3.5 text-slate-400" />
                            <span>Jadwal Slot</span>
                        </Link>
                    </div>
                </div>

                <!-- 4 CLEAN & BALANCED METRIC CARDS -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Metric 1: Lapangan Terdaftar -->
                    <div class="rounded-2xl border border-slate-800/80 bg-slate-900/50 p-5 shadow-sm transition hover:border-slate-700">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-slate-400">Total Lapangan</span>
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-800 bg-slate-950/70 text-slate-300">
                                <CalendarDays class="h-4 w-4" />
                            </div>
                        </div>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-white">{{ stats.total_courts }}</p>
                        <p class="mt-1.5 text-xs text-slate-400">
                            <span class="font-medium text-emerald-400">{{ stats.available_courts }} tersedia</span> · {{ stats.maintenance_courts }} maintenance
                        </p>
                    </div>

                    <!-- Metric 2: Venue Terverifikasi -->
                    <div class="rounded-2xl border border-slate-800/80 bg-slate-900/50 p-5 shadow-sm transition hover:border-slate-700">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-slate-400">Mitra Venue</span>
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-800 bg-slate-950/70 text-slate-300">
                                <Building2 class="h-4 w-4" />
                            </div>
                        </div>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-white">{{ stats.approved_venues }}</p>
                        <p class="mt-1.5 text-xs">
                            <span v-if="stats.pending_venues > 0" class="font-medium text-amber-400">
                                {{ stats.pending_venues }} menunggu verifikasi
                            </span>
                            <span v-else class="text-slate-400">Semua venue terverifikasi</span>
                        </p>
                    </div>

                    <!-- Metric 3: Booking Hari Ini -->
                    <div class="rounded-2xl border border-slate-800/80 bg-slate-900/50 p-5 shadow-sm transition hover:border-slate-700">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-slate-400">Sesi Hari Ini</span>
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-800 bg-slate-950/70 text-slate-300">
                                <Activity class="h-4 w-4" />
                            </div>
                        </div>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-white">{{ stats.today_bookings }}</p>
                        <p class="mt-1.5 text-xs text-slate-400">Dari total {{ fmt(stats.bookings) }} booking</p>
                    </div>

                    <!-- Metric 4: Total Revenue -->
                    <div class="rounded-2xl border border-slate-800/80 bg-slate-900/50 p-5 shadow-sm transition hover:border-slate-700">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-slate-400">Pendapatan Platform</span>
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-800 bg-slate-950/70 text-emerald-400">
                                <Banknote class="h-4 w-4" />
                            </div>
                        </div>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-emerald-400">Rp {{ fmt(stats.revenue) }}</p>
                        <p class="mt-1.5 text-xs text-slate-400">Transaksi sukses & lunas</p>
                    </div>
                </div>

                <!-- MAIN SECTION: DAFTAR LAPANGAN -->
                <div class="space-y-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold tracking-tight text-white">
                                Daftar Lapangan
                            </h2>
                            <p class="text-xs text-slate-400">
                                Pantau status operasional tiap lapangan, atur pemeliharaan, atau sesuaikan tarif sewa.
                            </p>
                        </div>

                        <Link
                            :href="route('courts.index')"
                            class="inline-flex items-center gap-1 text-xs font-medium text-emerald-400 hover:text-emerald-300 hover:underline"
                        >
                            Buka modul lapangan
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </div>

                    <!-- Filter & Search Controls -->
                    <div class="flex flex-col gap-3 rounded-2xl border border-slate-800/80 bg-slate-900/50 p-3 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                        <!-- Search Box -->
                        <div class="relative w-full sm:w-72">
                            <Search class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                            <input
                                v-model="courtSearch"
                                type="text"
                                placeholder="Cari lapangan, venue, kota..."
                                class="h-8 w-full rounded-xl border border-slate-800 bg-slate-950/80 pl-9 pr-3 text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500/50"
                            />
                        </div>

                        <!-- Filter Tabs -->
                        <div class="inline-flex flex-wrap items-center gap-1 rounded-xl border border-slate-800 bg-slate-950/80 p-1 text-xs">
                            <button
                                type="button"
                                class="rounded-lg px-2.5 py-1 text-xs transition"
                                :class="courtFilter === 'all'
                                    ? 'bg-slate-800 font-semibold text-white shadow-sm border border-slate-700/60'
                                    : 'text-slate-400 hover:text-white'"
                                @click="courtFilter = 'all'"
                            >
                                Semua ({{ courts.length }})
                            </button>
                            <button
                                type="button"
                                class="rounded-lg px-2.5 py-1 text-xs transition"
                                :class="courtFilter === 'available'
                                    ? 'bg-slate-800 font-semibold text-white shadow-sm border border-slate-700/60'
                                    : 'text-slate-400 hover:text-white'"
                                @click="courtFilter = 'available'"
                            >
                                Tersedia ({{ stats.available_courts }})
                            </button>
                            <button
                                type="button"
                                class="rounded-lg px-2.5 py-1 text-xs transition"
                                :class="courtFilter === 'maintenance'
                                    ? 'bg-slate-800 font-semibold text-white shadow-sm border border-slate-700/60'
                                    : 'text-slate-400 hover:text-white'"
                                @click="courtFilter = 'maintenance'"
                            >
                                Maintenance ({{ stats.maintenance_courts }})
                            </button>
                            <button
                                type="button"
                                class="rounded-lg px-2.5 py-1 text-xs transition"
                                :class="courtFilter === 'indoor'
                                    ? 'bg-slate-800 font-semibold text-white shadow-sm border border-slate-700/60'
                                    : 'text-slate-400 hover:text-white'"
                                @click="courtFilter = 'indoor'"
                            >
                                Indoor
                            </button>
                            <button
                                type="button"
                                class="rounded-lg px-2.5 py-1 text-xs transition"
                                :class="courtFilter === 'outdoor'
                                    ? 'bg-slate-800 font-semibold text-white shadow-sm border border-slate-700/60'
                                    : 'text-slate-400 hover:text-white'"
                                @click="courtFilter = 'outdoor'"
                            >
                                Outdoor
                            </button>
                        </div>
                    </div>

                    <!-- Courts Management Table -->
                    <div class="overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/50 shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="border-b border-slate-800/80 bg-slate-950/70 text-[11px] font-semibold text-slate-400">
                                    <tr>
                                        <th class="px-4 py-3.5">Lapangan</th>
                                        <th class="px-4 py-3.5">Venue & Lokasi</th>
                                        <th class="px-4 py-3.5">Tipe</th>
                                        <th class="px-4 py-3.5">Tarif Sewa</th>
                                        <th class="px-4 py-3.5">Status</th>
                                        <th class="px-4 py-3.5">Sesi Hari Ini</th>
                                        <th class="px-4 py-3.5 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60">
                                    <tr
                                        v-for="c in filteredCourts"
                                        :key="c.id"
                                        class="transition hover:bg-slate-800/30"
                                    >
                                        <!-- Court Name -->
                                        <td class="px-4 py-3.5 font-semibold text-white">
                                            <div class="flex items-center gap-2">
                                                <span class="h-2 w-2 rounded-full" :class="c.status === 'available' ? 'bg-emerald-400' : 'bg-amber-400'" />
                                                <span>{{ c.name }}</span>
                                            </div>
                                        </td>

                                        <!-- Venue -->
                                        <td class="px-4 py-3.5">
                                            <p class="font-medium text-slate-200">{{ c.venue.name }}</p>
                                            <p class="text-[11px] text-slate-400">{{ c.venue.city }}</p>
                                        </td>

                                        <!-- Type -->
                                        <td class="px-4 py-3.5">
                                            <span class="rounded-md border border-slate-800 bg-slate-950/60 px-2 py-0.5 text-[10px] font-medium uppercase text-slate-400">
                                                {{ c.type }}
                                            </span>
                                        </td>

                                        <!-- Price -->
                                        <td class="px-4 py-3.5 font-medium text-emerald-400">
                                            {{ fmtPrice(c.price_per_hour) }}<span class="text-[10px] text-slate-500">/jam</span>
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="px-4 py-3.5">
                                            <span
                                                v-if="c.status === 'available'"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 text-[11px] font-medium text-emerald-400"
                                            >
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" />
                                                Tersedia
                                            </span>
                                            <span
                                                v-else-if="c.status === 'maintenance'"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 border border-amber-500/20 px-2.5 py-0.5 text-[11px] font-medium text-amber-400"
                                            >
                                                <Wrench class="h-3 w-3" />
                                                Maintenance
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex items-center rounded-full bg-slate-800/80 px-2.5 py-0.5 text-[11px] font-medium text-slate-400"
                                            >
                                                Nonaktif
                                            </span>
                                        </td>

                                        <!-- Bookings Today -->
                                        <td class="px-4 py-3.5 text-slate-300">
                                            {{ c.today_bookings_count }} sesi
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-4 py-3.5 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <!-- Quick Toggle Status Button -->
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 text-xs font-medium transition"
                                                    :class="c.status === 'available'
                                                        ? 'border-slate-700 bg-slate-800/80 text-slate-300 hover:text-white hover:bg-slate-700'
                                                        : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300 hover:bg-emerald-500/20'"
                                                    @click="toggleCourt(c.id)"
                                                    :title="c.status === 'available' ? 'Ubah menjadi Maintenance' : 'Aktifkan Lapangan'"
                                                >
                                                    <Wrench v-if="c.status === 'available'" class="h-3 w-3 text-slate-400" />
                                                    <Check v-else class="h-3 w-3 text-emerald-400" />
                                                    <span>{{ c.status === 'available' ? 'Set Maintenance' : 'Set Tersedia' }}</span>
                                                </button>

                                                <!-- Edit Court Link -->
                                                <Link
                                                    :href="route('courts.edit', c.id)"
                                                    class="rounded-lg border border-slate-800 bg-slate-950/60 px-2.5 py-1 text-xs font-medium text-slate-400 hover:text-white hover:bg-slate-800 transition"
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
                    <div class="rounded-2xl border border-slate-800/80 bg-slate-900/50 p-5 shadow-sm">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                            <div>
                                <h3 class="text-sm font-bold text-white">Persetujuan Venue</h3>
                                <p class="text-xs text-slate-400">Pengajuan pendaftaran venue mitra yang perlu verifikasi.</p>
                            </div>
                            <span class="rounded-full border border-slate-800 bg-slate-950/80 px-2.5 py-0.5 text-xs font-medium text-slate-300">
                                {{ pendingVenues.length }} Menunggu
                            </span>
                        </div>

                        <div class="mt-4">
                            <div v-if="pendingVenues.length" class="space-y-2.5">
                                <div
                                    v-for="v in pendingVenues"
                                    :key="v.id"
                                    class="flex items-center justify-between gap-3 rounded-xl border border-slate-800/80 bg-slate-950/60 p-3.5 transition hover:bg-slate-800/40"
                                >
                                    <div class="min-w-0">
                                        <p class="font-semibold text-white text-sm">{{ v.name }}</p>
                                        <p class="text-xs text-slate-400">{{ v.city }} · {{ v.address ?? 'Alamat lengkap' }}</p>
                                    </div>

                                    <div class="flex shrink-0 gap-1.5">
                                        <Button
                                            variant="accent"
                                            size="sm"
                                            class="gap-1 font-semibold text-xs h-8 px-3"
                                            @click="router.post(route('admin.venues.approve', v.id))"
                                        >
                                            <Check class="h-3.5 w-3.5" />
                                            Setujui
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            class="border-slate-700 text-rose-300 hover:bg-rose-500/10 text-xs h-8 px-3"
                                            @click="router.post(route('admin.venues.reject', v.id))"
                                        >
                                            <X class="h-3.5 w-3.5" />
                                            Tolak
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="rounded-xl border border-dashed border-slate-800 p-6 text-center text-slate-400">
                                <CheckCircle2 class="mx-auto h-7 w-7 text-emerald-400" />
                                <p class="mt-2 text-xs font-semibold text-white">Semua Venue Terverifikasi</p>
                                <p class="text-[11px] text-slate-500">Tidak ada pengajuan venue yang menunggu saat ini.</p>
                            </div>
                        </div>
                    </div>

                    <!-- User Management -->
                    <div class="rounded-2xl border border-slate-800/80 bg-slate-900/50 p-5 shadow-sm">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                            <div>
                                <h3 class="text-sm font-bold text-white">Manajemen Pengguna</h3>
                                <p class="text-xs text-slate-400">Audit akun dan status akses pengguna sistem.</p>
                            </div>
                            <span class="rounded-full border border-slate-800 bg-slate-950/80 px-2.5 py-0.5 text-xs font-medium text-slate-300">
                                {{ users.length }} Pengguna
                            </span>
                        </div>

                        <div class="mt-4">
                            <div class="max-h-80 space-y-2 overflow-y-auto pr-1">
                                <div
                                    v-for="u in users"
                                    :key="u.id"
                                    class="flex items-center justify-between gap-3 rounded-xl border border-slate-800/80 bg-slate-950/60 p-3 transition hover:bg-slate-800/40"
                                >
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="truncate font-semibold text-white text-xs">{{ u.name }}</p>
                                            <span
                                                class="rounded px-1.5 py-0.2 text-[10px] font-medium uppercase"
                                                :class="u.role === 'admin' ? 'bg-rose-500/10 text-rose-300 border border-rose-500/20' : u.role === 'venue_owner' ? 'bg-blue-500/10 text-blue-300 border border-blue-500/20' : 'bg-slate-800 text-slate-300'"
                                            >
                                                {{ u.role }}
                                            </span>
                                        </div>
                                        <p class="truncate text-[11px] text-slate-400">{{ u.email }}</p>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <span
                                            class="rounded-full px-2 py-0.5 text-[10px] font-medium"
                                            :class="u.is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400'"
                                        >
                                            {{ u.is_active ? 'Aktif' : 'Suspend' }}
                                        </span>

                                        <button
                                            v-if="u.role !== 'admin'"
                                            type="button"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-800 bg-slate-900 px-2 py-1 text-[11px] font-medium transition hover:bg-slate-800"
                                            :class="u.is_active ? 'text-rose-400 hover:border-rose-500/30' : 'text-emerald-400 hover:border-emerald-500/30'"
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
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold tracking-tight text-white">
                                Booking Terbaru
                            </h2>
                            <p class="text-xs text-slate-400">Riwayat reservasi lapangan pemain dari seluruh venue mitra.</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="rounded-full border border-slate-800 bg-slate-950/80 px-2.5 py-1 text-xs font-medium text-slate-400">
                                10 Reservasi Terakhir
                            </span>
                            <Link
                                :href="route('venue-dashboard.index')"
                                class="inline-flex items-center gap-1 text-xs font-medium text-emerald-400 hover:text-emerald-300 hover:underline"
                            >
                                Analytics Platform
                                <ChevronRight class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/50 shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="border-b border-slate-800/80 bg-slate-950/70 text-[11px] font-semibold text-slate-400">
                                    <tr>
                                        <th class="px-4 py-3.5">Pemain</th>
                                        <th class="px-4 py-3.5">Lapangan & Venue</th>
                                        <th class="px-4 py-3.5">Tanggal & Jam</th>
                                        <th class="px-4 py-3.5">Status Booking</th>
                                        <th class="px-4 py-3.5">Pembayaran</th>
                                        <th class="px-4 py-3.5 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60">
                                    <tr
                                        v-for="b in recentBookings"
                                        :key="b.id"
                                        class="transition hover:bg-slate-800/30"
                                    >
                                        <!-- Player Name -->
                                        <td class="px-4 py-3.5 font-semibold text-white">
                                            {{ b.user?.name ?? 'Pengguna' }}
                                        </td>

                                        <!-- Court & Venue -->
                                        <td class="px-4 py-3.5">
                                            <p class="font-medium text-slate-200">{{ b.court?.name ?? 'Lapangan' }}</p>
                                            <p class="text-[11px] text-slate-400">{{ b.court?.venue?.name ?? 'Venue' }}</p>
                                        </td>

                                        <!-- Date & Hour -->
                                        <td class="px-4 py-3.5 text-slate-300">
                                            <p class="font-medium">{{ fmtDate(b.booking_date) }}</p>
                                            <p class="text-[11px] text-slate-400">{{ b.start_time }} – {{ b.end_time }} WIB</p>
                                        </td>

                                        <!-- Status Booking -->
                                        <td class="px-4 py-3.5">
                                            <Badge :variant="bookingVariant(b.status)">
                                                {{ bookingLabel(b.status) }}
                                            </Badge>
                                        </td>

                                        <!-- Payment Status -->
                                        <td class="px-4 py-3.5">
                                            <span
                                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium"
                                                :class="b.payment_status === 'paid' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400'"
                                            >
                                                {{ b.payment_status === 'paid' ? 'Lunas' : 'Pending' }}
                                            </span>
                                        </td>

                                        <!-- Total Amount -->
                                        <td class="px-4 py-3.5 text-right font-semibold text-emerald-400">
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
