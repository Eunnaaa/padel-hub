<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import {
    AlertCircle,
    Calendar,
    CalendarDays,
    CheckCircle2,
    Clock,
    CreditCard,
    FileText,
    MapPin,
    Plus,
    Search,
    Sparkles,
    XCircle,
} from '@lucide/vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface BookingItem {
    id: number;
    booking_date: string;
    start_time: string;
    end_time: string;
    status: string;
    payment_status: string;
    total_amount: string;
    recurrence_rule: string | null;
    recurring_group_id: string | null;
    court: { name: string; venue: { name: string; city: string } };
}

const props = defineProps<{
    bookings: BookingItem[];
}>();

const searchQuery = ref('');
const activeFilter = ref<'all' | 'upcoming' | 'unpaid' | 'completed'>('all');

const statusLabel = (s: string) =>
    ({
        pending: 'Menunggu',
        confirmed: 'Dikonfirmasi',
        checked_in: 'Checked In',
        finished: 'Selesai',
        cancelled: 'Dibatalkan',
        expired: 'Kedaluwarsa',
    }[s] ?? s);

const paymentLabel = (s: string) =>
    ({
        paid: 'Lunas',
        pending: 'Belum Bayar',
        failed: 'Gagal',
    }[s] ?? s);

const fmtDate = (d: string) =>
    new Date(`${d}T00:00:00`).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });

const fmt = (n: string | number) => Number(n).toLocaleString('id-ID');
const isActive = (b: { status: string }) => !['cancelled', 'finished', 'expired'].includes(b.status);
const needsPayment = (b: { status: string; payment_status: string }) =>
    b.status === 'pending' && b.payment_status !== 'paid';

const upcomingBookings = computed(() => props.bookings.filter(isActive));
const unpaidBookings = computed(() => props.bookings.filter(needsPayment));
const completedBookings = computed(() =>
    props.bookings.filter((b) => ['finished', 'cancelled', 'expired'].includes(b.status)),
);

const filteredBookings = computed(() => {
    return props.bookings.filter((b) => {
        // Status filter
        if (activeFilter.value === 'upcoming' && !isActive(b)) return false;
        if (activeFilter.value === 'unpaid' && !needsPayment(b)) return false;
        if (activeFilter.value === 'completed' && isActive(b)) return false;

        // Search query
        if (searchQuery.value.trim()) {
            const q = searchQuery.value.toLowerCase().trim();
            const venue = b.court.venue.name.toLowerCase();
            const court = b.court.name.toLowerCase();
            const city = b.court.venue.city.toLowerCase();
            return venue.includes(q) || court.includes(q) || city.includes(q);
        }

        return true;
    });
});

const dialog = ref<{ open: boolean; id: number; label: string; cancelSeries: boolean }>({
    open: false,
    id: 0,
    label: '',
    cancelSeries: false,
});

const askCancel = (b: BookingItem, cancelSeries: boolean) => {
    dialog.value = {
        open: true,
        id: b.id,
        label: `${b.court.venue.name} — ${b.court.name} · ${fmtDate(b.booking_date)}`,
        cancelSeries,
    };
};

const confirmCancel = () => {
    router.delete(route('bookings.destroy', dialog.value.id), {
        data: { cancel_series: dialog.value.cancelSeries },
    });
    dialog.value.open = false;
};
</script>

<template>
    <Head title="Bookings Saya — PadelHub" />
    <AuthenticatedLayout :dark="true">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-400">
                        <Calendar class="h-3.5 w-3.5" />
                        <span>RESERVASI & LAPANGAN</span>
                    </div>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">Bookings Saya</h2>
                    <p class="mt-1 text-sm text-slate-400">Kelola reservasi jadwal main, status pembayaran, dan invoice pemesanan lapanganmu.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('bookings.create')"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-4 py-2.5 text-xs font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all hover:scale-105 hover:shadow-emerald-500/30 active:scale-95"
                    >
                        <Plus class="h-4 w-4" />
                        <span>Booking Baru</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-6xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- KPI SUMMARY STATS CARDS -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <!-- Total Booking -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-xl backdrop-blur-md">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Booking</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-500/30">
                                <CalendarDays class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-2xl font-black tracking-tight text-white">
                            {{ bookings.length }} <span class="text-sm font-semibold text-slate-400">Sesi</span>
                        </p>
                        <p class="mt-1 text-xs text-slate-400">Semua riwayat pemesananmu</p>
                    </div>

                    <!-- Jadwal Mendatang -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-xl backdrop-blur-md">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Jadwal Mendatang</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-500/30">
                                <Clock class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-2xl font-black tracking-tight text-white">
                            {{ upcomingBookings.length }} <span class="text-sm font-semibold text-slate-400">Aktif</span>
                        </p>
                        <div class="mt-1 flex items-center gap-1.5 text-xs text-emerald-400">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse" />
                            <span>Siap bertanding</span>
                        </div>
                    </div>

                    <!-- Perlu Bayar -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-xl backdrop-blur-md">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Perlu Pembayaran</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/15 text-amber-400 ring-1 ring-amber-500/30">
                                <AlertCircle class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-2xl font-black tracking-tight text-white">
                            {{ unpaidBookings.length }} <span class="text-sm font-semibold text-slate-400">Menunggu</span>
                        </p>
                        <p class="mt-1 text-xs" :class="unpaidBookings.length > 0 ? 'text-amber-400 font-semibold' : 'text-slate-400'">
                            {{ unpaidBookings.length > 0 ? 'Segera lunasi sebelum kedaluwarsa' : 'Semua sesi aman lunas' }}
                        </p>
                    </div>
                </div>

                <!-- FILTER & SEARCH BAR -->
                <div class="flex flex-col gap-4 rounded-2xl border border-slate-800/80 bg-slate-900/70 p-4 shadow-xl backdrop-blur-md md:flex-row md:items-center md:justify-between">
                    <!-- Status Filter Tabs -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button
                            type="button"
                            @click="activeFilter = 'all'"
                            class="rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
                            :class="activeFilter === 'all'
                                ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20'
                                : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                        >
                            Semua ({{ bookings.length }})
                        </button>
                        <button
                            type="button"
                            @click="activeFilter = 'upcoming'"
                            class="rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
                            :class="activeFilter === 'upcoming'
                                ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20'
                                : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                        >
                            Mendatang ({{ upcomingBookings.length }})
                        </button>
                        <button
                            type="button"
                            @click="activeFilter = 'unpaid'"
                            class="rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
                            :class="activeFilter === 'unpaid'
                                ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20'
                                : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                        >
                            Perlu Bayar ({{ unpaidBookings.length }})
                        </button>
                        <button
                            type="button"
                            @click="activeFilter = 'completed'"
                            class="rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
                            :class="activeFilter === 'completed'
                                ? 'bg-slate-700 text-white shadow-md'
                                : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                        >
                            Selesai / Riwayat ({{ completedBookings.length }})
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full md:w-72">
                        <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari venue, kota, lapangan..."
                            class="h-10 w-full rounded-xl border border-slate-800 bg-slate-950/80 pl-9 pr-3 text-xs text-white placeholder-slate-400 shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        />
                    </div>
                </div>

                <!-- BOOKINGS LIST -->
                <div v-if="filteredBookings.length > 0" class="space-y-4">
                    <div
                        v-for="b in filteredBookings"
                        :key="b.id"
                        class="group relative overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-xl backdrop-blur-md transition-all hover:border-emerald-500/40 hover:shadow-2xl hover:shadow-emerald-950/20"
                    >
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex items-start gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 text-emerald-400 ring-1 ring-emerald-500/30">
                                    <CalendarDays class="h-7 w-7" />
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-extrabold text-white">
                                            {{ b.court.venue.name }}
                                        </h3>
                                        <span class="rounded-md border border-slate-700 bg-slate-800 px-2 py-0.5 text-xs font-semibold text-slate-300">
                                            {{ b.court.name }}
                                        </span>
                                        <span
                                            v-if="b.recurrence_rule"
                                            class="rounded-md border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-[10.5px] font-bold text-emerald-400"
                                        >
                                            🔁 Mingguan
                                        </span>
                                    </div>

                                    <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-slate-400">
                                        <span class="flex items-center gap-1 text-slate-300">
                                            <MapPin class="h-3.5 w-3.5 text-emerald-400" />
                                            {{ b.court.venue.city }}
                                        </span>
                                        <span>•</span>
                                        <span class="flex items-center gap-1 font-semibold text-white">
                                            <Clock class="h-3.5 w-3.5 text-emerald-400" />
                                            {{ fmtDate(b.booking_date) }} ({{ b.start_time }} – {{ b.end_time }})
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Badges -->
                            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                <!-- Status Badge -->
                                <span
                                    v-if="b.status === 'confirmed'"
                                    class="inline-flex items-center gap-1 rounded-full border border-emerald-500/30 bg-emerald-500/15 px-3 py-1 text-xs font-bold text-emerald-400 shadow-sm"
                                >
                                    <CheckCircle2 class="h-3.5 w-3.5" />
                                    <span>Dikonfirmasi</span>
                                </span>
                                <span
                                    v-else-if="b.status === 'checked_in'"
                                    class="inline-flex items-center gap-1 rounded-full border border-sky-500/30 bg-sky-500/15 px-3 py-1 text-xs font-bold text-sky-400 shadow-sm"
                                >
                                    <span>Checked In</span>
                                </span>
                                <span
                                    v-else-if="b.status === 'pending'"
                                    class="inline-flex items-center gap-1 rounded-full border border-amber-500/30 bg-amber-500/15 px-3 py-1 text-xs font-bold text-amber-400 animate-pulse"
                                >
                                    <Clock class="h-3.5 w-3.5" />
                                    <span>Menunggu Konfirmasi</span>
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1 rounded-full border border-slate-700 bg-slate-800 px-3 py-1 text-xs font-bold text-slate-400"
                                >
                                    <span>{{ statusLabel(b.status) }}</span>
                                </span>

                                <!-- Payment Badge -->
                                <span
                                    v-if="b.payment_status === 'paid'"
                                    class="inline-flex items-center rounded-md border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-bold text-emerald-400"
                                >
                                    Lunas
                                </span>
                                <span
                                    v-else-if="b.payment_status === 'pending'"
                                    class="inline-flex items-center rounded-md border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-xs font-bold text-amber-400"
                                >
                                    Belum Bayar
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center rounded-md border border-rose-500/30 bg-rose-500/10 px-2 py-0.5 text-xs font-bold text-rose-400"
                                >
                                    {{ paymentLabel(b.payment_status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Footer Details & Actions -->
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-800/80 pt-3.5">
                            <div>
                                <span class="text-xs text-slate-400">Total Biaya: </span>
                                <span class="text-base font-black text-emerald-400">Rp {{ fmt(b.total_amount) }}</span>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <Link
                                    v-if="needsPayment(b)"
                                    :href="route('payments.create', b.id)"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-3.5 py-1.5 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition-all hover:scale-105"
                                >
                                    <CreditCard class="h-3.5 w-3.5" />
                                    <span>Bayar Sekarang</span>
                                </Link>

                                <Link
                                    v-if="b.payment_status === 'paid'"
                                    :href="route('payments.invoice', b.id)"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800/80 px-3 py-1.5 text-xs font-semibold text-slate-200 transition-colors hover:border-emerald-500/50 hover:bg-slate-700 hover:text-white"
                                >
                                    <FileText class="h-3.5 w-3.5 text-emerald-400" />
                                    <span>Invoice</span>
                                </Link>

                                <Link
                                    v-if="b.status === 'pending'"
                                    :href="route('bookings.edit', b.id)"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800/80 px-3 py-1.5 text-xs font-semibold text-slate-300 transition hover:bg-slate-700 hover:text-white"
                                >
                                    <span>Ubah</span>
                                </Link>

                                <button
                                    v-if="isActive(b) && b.payment_status !== 'paid'"
                                    type="button"
                                    @click="askCancel(b, false)"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-1.5 text-xs font-semibold text-rose-300 transition hover:bg-rose-500/20 hover:text-white"
                                >
                                    <XCircle class="h-3.5 w-3.5" />
                                    <span>Batalkan</span>
                                </button>

                                <button
                                    v-if="isActive(b) && b.payment_status !== 'paid' && b.recurring_group_id"
                                    type="button"
                                    @click="askCancel(b, true)"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-1.5 text-xs font-semibold text-rose-300 transition hover:bg-rose-500/20 hover:text-white"
                                >
                                    <XCircle class="h-3.5 w-3.5" />
                                    <span>Batalkan Seri</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EMPTY STATE -->
                <div
                    v-else
                    class="relative overflow-hidden rounded-3xl border border-slate-800/80 bg-gradient-to-br from-slate-900/90 via-slate-900/50 to-slate-950 p-8 text-center shadow-2xl backdrop-blur-xl sm:p-12"
                >
                    <!-- Background Glow -->
                    <div class="pointer-events-none absolute -top-24 left-1/2 h-56 w-56 -translate-x-1/2 rounded-full bg-emerald-500/10 blur-3xl" />

                    <div class="relative z-10 mx-auto max-w-md space-y-4">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400/20 to-teal-500/10 text-emerald-400 ring-1 ring-emerald-400/30 shadow-lg shadow-emerald-500/10">
                            <CalendarDays class="h-8 w-8" />
                        </div>

                        <div>
                            <h3 class="text-xl font-black text-white">
                                {{ searchQuery || activeFilter !== 'all' ? 'Tidak Ada Booking Sesuai Filter' : 'Belum Ada Jadwal Booking' }}
                            </h3>
                            <p class="mt-2 text-sm text-slate-400">
                                {{
                                    searchQuery || activeFilter !== 'all'
                                        ? 'Coba ganti tab filter atau cari dengan nama lapangan atau venue lainnya.'
                                        : 'Kamu belum memesan lapangan padel. Yuk jadwalkan sesi permainan atau latihan padel pertamamu sekarang!'
                                }}
                            </p>
                        </div>

                        <div class="pt-2">
                            <button
                                v-if="searchQuery || activeFilter !== 'all'"
                                type="button"
                                @click="searchQuery = ''; activeFilter = 'all'"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-800 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-slate-700"
                            >
                                <span>Reset Filter</span>
                            </button>
                            <Link
                                v-else
                                :href="route('bookings.create')"
                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-5 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all hover:scale-105 hover:shadow-emerald-500/30 active:scale-95"
                            >
                                <Plus class="h-4 w-4" />
                                <span>Booking Lapangan Sekarang</span>
                            </Link>
                        </div>

                        <!-- Member Voucher Hint Banner -->
                        <div class="mt-6 rounded-2xl border border-dashed border-emerald-500/30 bg-emerald-500/5 p-4 text-left">
                            <div class="flex items-center gap-2 text-emerald-400">
                                <Sparkles class="h-4 w-4" />
                                <span class="text-xs font-black uppercase tracking-wider">Garansi Booking Instan</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-300">
                                Slot lapangan langsung terkunci begitu kamu memilih jam main. Selesaikan pembayaran dalam 15 menit untuk konfirmasi instan!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDialog
            v-model:open="dialog.open"
            title="Batalkan booking"
            :description="dialog.cancelSeries
                ? `Batalkan seluruh seri mingguan yang tersisa dari ${dialog.label}?`
                : `Yakin ingin membatalkan ${dialog.label}?`"
            confirm-label="Batalkan"
            destructive
            @confirm="confirmCancel"
        />
    </AuthenticatedLayout>
</template>
