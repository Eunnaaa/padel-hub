<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    ArrowRight,
    Banknote,
    CalendarDays,
    CheckCircle2,
    Clock,
    CreditCard,
    FileText,
    MapPin,
    Plus,
    ReceiptText,
    Search,
    Sparkles,
    XCircle,
} from '@lucide/vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface PaymentItem {
    id: number;
    amount: string;
    method: string;
    provider: string;
    status: string;
    transaction_reference: string | null;
    created_at: string;
    booking: {
        id: number;
        booking_date: string;
        start_time: string;
        end_time: string;
        payment_status: string;
        court: { name: string; venue: { name: string; city?: string } };
    };
}

const props = defineProps<{
    payments: PaymentItem[];
}>();

const searchQuery = ref('');
const activeFilter = ref<'all' | 'paid' | 'pending' | 'failed'>('all');

const fmt = (n: string | number) => Number(n).toLocaleString('id-ID');
const fmtDateTime = (d: string) =>
    new Date(d).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const fmtDate = (d: string) =>
    new Date(`${d}T00:00:00`).toLocaleDateString('id-ID', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });

const paidPayments = computed(() => props.payments.filter((p) => p.status === 'paid'));
const pendingPayments = computed(() => props.payments.filter((p) => p.status === 'pending'));
const failedPayments = computed(() => props.payments.filter((p) => ['failed', 'cancelled', 'expired'].includes(p.status)));

const totalSpent = computed(() =>
    paidPayments.value.reduce((acc, curr) => acc + Number(curr.amount || 0), 0),
);

const filteredPayments = computed(() => {
    return props.payments.filter((p) => {
        // Status filter
        if (activeFilter.value === 'paid' && p.status !== 'paid') return false;
        if (activeFilter.value === 'pending' && p.status !== 'pending') return false;
        if (activeFilter.value === 'failed' && !['failed', 'cancelled', 'expired'].includes(p.status)) return false;

        // Search query
        if (searchQuery.value.trim()) {
            const q = searchQuery.value.toLowerCase().trim();
            const venue = p.booking?.court?.venue?.name?.toLowerCase() ?? '';
            const court = p.booking?.court?.name?.toLowerCase() ?? '';
            const refNo = (p.transaction_reference || '').toLowerCase();
            const method = (p.method || '').toLowerCase();
            return venue.includes(q) || court.includes(q) || refNo.includes(q) || method.includes(q);
        }

        return true;
    });
});
</script>

<template>
    <Head title="Riwayat Pembayaran — PadelHub" />
    <AuthenticatedLayout :dark="true">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-400">
                        <CreditCard class="h-3.5 w-3.5" />
                        <span>FINANSIAL & INVOICE</span>
                    </div>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">Riwayat Pembayaran</h2>
                    <p class="mt-1 text-sm text-slate-400">Pantau transaksi booking lapangan, tagihan, dan invoice resmi kamu secara transparan.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('bookings.create')"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-4 py-2.5 text-xs font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all hover:scale-105 hover:shadow-emerald-500/30 active:scale-95"
                    >
                        <Plus class="h-4 w-4" />
                        <span>Booking Lapangan</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-6xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- KPI SUMMARY STATS CARDS -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <!-- Total Pengeluaran -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-xl backdrop-blur-md">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Pengeluaran</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-500/30">
                                <Banknote class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-2xl font-black tracking-tight text-white">
                            Rp {{ fmt(totalSpent) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            Dari <span class="font-bold text-emerald-400">{{ paidPayments.length }}</span> transaksi sukses
                        </p>
                    </div>

                    <!-- Transaksi Berhasil -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-xl backdrop-blur-md">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Transaksi Lunas</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-500/30">
                                <CheckCircle2 class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-2xl font-black tracking-tight text-white">
                            {{ paidPayments.length }} <span class="text-sm font-semibold text-slate-400">Invoice</span>
                        </p>
                        <div class="mt-1 flex items-center gap-1.5 text-xs text-emerald-400">
                            <span class="h-2 w-2 rounded-full bg-emerald-400" />
                            <span>Status Terverifikasi</span>
                        </div>
                    </div>

                    <!-- Menunggu Pembayaran -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-xl backdrop-blur-md">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Perlu Dibayar</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/15 text-amber-400 ring-1 ring-amber-500/30">
                                <Clock class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-2xl font-black tracking-tight text-white">
                            {{ pendingPayments.length }} <span class="text-sm font-semibold text-slate-400">Tagihan</span>
                        </p>
                        <p class="mt-1 text-xs" :class="pendingPayments.length > 0 ? 'text-amber-400 font-semibold' : 'text-slate-400'">
                            {{ pendingPayments.length > 0 ? 'Selesaikan sebelum slot kedaluwarsa' : 'Tidak ada tagihan tertunda' }}
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
                            Semua ({{ payments.length }})
                        </button>
                        <button
                            type="button"
                            @click="activeFilter = 'paid'"
                            class="rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
                            :class="activeFilter === 'paid'
                                ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20'
                                : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                        >
                            Lunas ({{ paidPayments.length }})
                        </button>
                        <button
                            type="button"
                            @click="activeFilter = 'pending'"
                            class="rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
                            :class="activeFilter === 'pending'
                                ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20'
                                : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                        >
                            Menunggu ({{ pendingPayments.length }})
                        </button>
                        <button
                            type="button"
                            @click="activeFilter = 'failed'"
                            class="rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
                            :class="activeFilter === 'failed'
                                ? 'bg-rose-500 text-white shadow-md shadow-rose-500/20'
                                : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                        >
                            Gagal/Batal ({{ failedPayments.length }})
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full md:w-72">
                        <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari venue, lapangan, no ref..."
                            class="h-10 w-full rounded-xl border border-slate-800 bg-slate-950/80 pl-9 pr-3 text-xs text-white placeholder-slate-400 shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        />
                    </div>
                </div>

                <!-- PAYMENTS LIST -->
                <div v-if="filteredPayments.length > 0" class="space-y-4">
                    <div
                        v-for="p in filteredPayments"
                        :key="p.id"
                        class="group relative overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-xl backdrop-blur-md transition-all hover:border-emerald-500/40 hover:shadow-2xl hover:shadow-emerald-950/20"
                    >
                        <!-- Top Row: Court & Amount -->
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 text-emerald-400 ring-1 ring-emerald-500/30">
                                    <Banknote class="h-6 w-6" />
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-extrabold text-white">
                                            {{ p.booking?.court?.venue?.name ?? 'Venue' }}
                                        </h3>
                                        <span class="rounded-md border border-slate-700 bg-slate-800 px-2 py-0.5 text-xs font-semibold text-slate-300">
                                            {{ p.booking?.court?.name ?? 'Lapangan' }}
                                        </span>
                                    </div>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-3 text-xs text-slate-400">
                                        <span class="flex items-center gap-1 text-slate-300">
                                            <CalendarDays class="h-3.5 w-3.5 text-emerald-400" />
                                            {{ fmtDate(p.booking.booking_date) }}
                                        </span>
                                        <span>•</span>
                                        <span class="flex items-center gap-1 text-slate-300">
                                            <Clock class="h-3.5 w-3.5 text-emerald-400" />
                                            {{ p.booking.start_time }} – {{ p.booking.end_time }}
                                        </span>
                                        <span>•</span>
                                        <span class="font-mono text-[11px] text-slate-400">
                                            Ref: {{ p.transaction_reference ?? ('TRX-' + p.id) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-row items-center justify-between gap-3 border-t border-slate-800/60 pt-3 sm:flex-col sm:items-end sm:border-t-0 sm:pt-0">
                                <div class="text-left sm:text-right">
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Nominal</p>
                                    <p class="text-xl font-black text-emerald-400">Rp {{ fmt(p.amount) }}</p>
                                </div>
                                <div>
                                    <span
                                        v-if="p.status === 'paid'"
                                        class="inline-flex items-center gap-1 rounded-full border border-emerald-500/30 bg-emerald-500/15 px-3 py-1 text-xs font-bold text-emerald-400 shadow-sm"
                                    >
                                        <CheckCircle2 class="h-3.5 w-3.5" />
                                        <span>Lunas</span>
                                    </span>
                                    <span
                                        v-else-if="p.status === 'pending'"
                                        class="inline-flex items-center gap-1 rounded-full border border-amber-500/30 bg-amber-500/15 px-3 py-1 text-xs font-bold text-amber-400 animate-pulse"
                                    >
                                        <Clock class="h-3.5 w-3.5" />
                                        <span>Menunggu Pembayaran</span>
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-full border border-rose-500/30 bg-rose-500/15 px-3 py-1 text-xs font-bold text-rose-400"
                                    >
                                        <XCircle class="h-3.5 w-3.5" />
                                        <span>{{ p.status }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Meta & Action Row -->
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-800/80 pt-3.5 text-xs">
                            <div class="flex items-center gap-2 text-slate-400">
                                <span>Metode:</span>
                                <span class="rounded-md border border-slate-700/80 bg-slate-800/60 px-2 py-0.5 font-bold uppercase tracking-wider text-slate-200">
                                    {{ p.method }} · {{ p.provider }}
                                </span>
                                <span class="hidden sm:inline">•</span>
                                <span class="hidden text-slate-400 sm:inline">{{ fmtDateTime(p.created_at) }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="p.status === 'pending'"
                                    :href="route('payments.create', p.booking.id)"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-3.5 py-1.5 font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition-all hover:scale-105"
                                >
                                    <span>Bayar Sekarang</span>
                                    <ArrowRight class="h-3.5 w-3.5" />
                                </Link>

                                <Link
                                    :href="route('payments.invoice', p.booking.id)"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800/80 px-3.5 py-1.5 font-semibold text-slate-200 transition-colors hover:border-emerald-500/50 hover:bg-slate-700 hover:text-white"
                                >
                                    <FileText class="h-3.5 w-3.5 text-emerald-400" />
                                    <span>Lihat Invoice</span>
                                </Link>
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
                            <ReceiptText class="h-8 w-8" />
                        </div>

                        <div>
                            <h3 class="text-xl font-black text-white">
                                {{ searchQuery || activeFilter !== 'all' ? 'Tidak Ada Transaksi Sesuai Filter' : 'Belum Ada Riwayat Transaksi' }}
                            </h3>
                            <p class="mt-2 text-sm text-slate-400">
                                {{
                                    searchQuery || activeFilter !== 'all'
                                        ? 'Coba ganti filter status atau kata kunci pencarian kamu untuk melihat riwayat lainnya.'
                                        : 'Semua bukti pembayaran, status pelunasan booking, dan invoice resmi PadelHub akan tercatat rapi di sini.'
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
                                <span class="text-xs font-black uppercase tracking-wider">Tips Hemat PadelHub</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-300">
                                Dapatkan potongan <span class="font-bold text-emerald-400">20%</span> pada booking pertamamu dengan memasukkan kode kupon
                                <span class="rounded bg-emerald-500/20 px-1.5 py-0.5 font-mono font-bold text-emerald-300">PADELBARU</span> saat checkout!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
