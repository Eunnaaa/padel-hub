<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PadelCourtBoard from '@/Components/PadelCourtBoard.vue';
import {
    Activity,
    ArrowRight,
    Building2,
    Calendar,
    CalendarCheck,
    CalendarDays,
    CheckCircle2,
    Clock,
    Flame,
    Layers,
    LayoutGrid,
    MapPin,
    Moon,
    RefreshCw,
    Search,
    Sparkles,
    Sun,
    Sunrise,
    Users,
    Wrench,
    X,
} from '@lucide/vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{
    date: string;
    venues: Array<{ id: number; name: string; city: string }>;
    courts: Array<{
        id: number;
        name: string;
        type?: string;
        price_per_hour: string;
        status: string;
        venue_id: number;
        venue: { id: number; name: string; city?: string };
    }>;
    bookings: Array<{ court_id: number; start_time: string; end_time: string; status: string }>;
}>();

// State
const venueId = ref<number | ''>('');
const localDate = ref(props.date);
const searchQuery = ref('');
const typeFilter = ref<'all' | 'indoor' | 'outdoor'>('all');
const viewMode = ref<'court' | 'grid' | 'cards'>('court');
const isRefreshing = ref(false);
const lastUpdatedTime = ref<string>('');

// Selected Slot for Booking Modal / Preview
const selectedSlotData = ref<{
    courtId: number;
    courtName: string;
    venueName: string;
    price: string;
    date: string;
    hour: number;
} | null>(null);

import { formatLocalDate } from '@/lib/utils';

// Dates calculation for quick pills
const now = new Date();
const todayStr = formatLocalDate(now);

const tomorrowObj = new Date();
tomorrowObj.setDate(now.getDate() + 1);
const tomorrowStr = formatLocalDate(tomorrowObj);

const dayAfterObj = new Date();
dayAfterObj.setDate(now.getDate() + 2);
const dayAfterStr = formatLocalDate(dayAfterObj);

const formatIndoDate = (dateStr: string) => {
    try {
        const d = new Date(`${dateStr}T00:00:00`);
        return d.toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return dateStr;
    }
};

// Update timestamp
const updateTimestamp = () => {
    const d = new Date();
    lastUpdatedTime.value = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
};

// 15 hours slots from 07:00 to 21:00
const hours = Array.from({ length: 15 }, (_, i) => i + 7);

// Slot groups for Cards view
const morningHours = [7, 8, 9, 10, 11];
const afternoonHours = [12, 13, 14, 15];
const eveningHours = [16, 17, 18, 19, 20, 21];

const filteredCourts = computed(() => {
    return props.courts.filter((c) => {
        // Venue filter
        if (venueId.value !== '' && c.venue_id !== venueId.value) return false;

        // Type filter
        if (typeFilter.value !== 'all') {
            const courtType = (c.type ?? 'indoor').toLowerCase();
            if (courtType !== typeFilter.value) return false;
        }

        // Search text
        if (searchQuery.value.trim()) {
            const q = searchQuery.value.toLowerCase();
            const matchesName = c.name.toLowerCase().includes(q);
            const matchesVenue = c.venue.name.toLowerCase().includes(q);
            const matchesCity = (c.venue.city ?? '').toLowerCase().includes(q);
            if (!matchesName && !matchesVenue && !matchesCity) return false;
        }

        return true;
    });
});

const toHHMM = (t: string) => t.slice(0, 5);

const slotState = (courtId: number, hour: number): 'available' | 'booked' | 'pending' | 'maintenance' => {
    const court = props.courts.find((c) => c.id === courtId);
    if (!court || court.status === 'inactive' || court.status === 'maintenance') return 'maintenance';

    const start = `${String(hour).padStart(2, '0')}:00`;
    const end = `${String(hour + 1).padStart(2, '0')}:00`;

    const booking = props.bookings.find(
        (b) =>
            b.court_id === courtId &&
            toHHMM(b.start_time) < end &&
            toHHMM(b.end_time) > start,
    );

    if (!booking) return 'available';
    return booking.status === 'pending' ? 'pending' : 'booked';
};

// Overall Metrics
const totalSlotsCount = computed(() => filteredCourts.value.length * hours.length);

const slotStats = computed(() => {
    let available = 0;
    let booked = 0;
    let pending = 0;
    let maintenance = 0;

    filteredCourts.value.forEach((court) => {
        hours.forEach((h) => {
            const s = slotState(court.id, h);
            if (s === 'available') available++;
            else if (s === 'booked') booked++;
            else if (s === 'pending') pending++;
            else if (s === 'maintenance') maintenance++;
        });
    });

    const activeTotal = available + booked + pending;
    const occupancyRate = activeTotal > 0 ? Math.round(((booked + pending) / activeTotal) * 100) : 0;

    return {
        available,
        booked,
        pending,
        maintenance,
        occupancyRate,
    };
});

// Navigation & Actions
const changeDate = (newDate: string) => {
    localDate.value = newDate;
    router.get(
        route('availability.index'),
        { date: newDate },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const refreshData = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['bookings'],
        onFinish: () => {
            isRefreshing.value = false;
            updateTimestamp();
        },
    });
};

const openBookingModal = (court: (typeof props.courts)[number], hour: number) => {
    const state = slotState(court.id, hour);
    if (state !== 'available') return;

    selectedSlotData.value = {
        courtId: court.id,
        courtName: court.name,
        venueName: court.venue.name,
        price: court.price_per_hour,
        date: localDate.value,
        hour,
    };
};

const proceedToBooking = () => {
    if (!selectedSlotData.value) return;
    const { courtId, date, hour } = selectedSlotData.value;
    const startTime = `${String(hour).padStart(2, '0')}:00`;
    const endTime = `${String(hour + 1).padStart(2, '0')}:00`;

    router.get(route('bookings.create'), {
        court_id: courtId,
        booking_date: date,
        start_time: startTime,
        end_time: endTime,
    });
};

let timer: ReturnType<typeof setInterval> | null = null;
onMounted(() => {
    updateTimestamp();
    timer = setInterval(() => {
        router.reload({
            only: ['bookings'],
            onFinish: () => updateTimestamp(),
        });
    }, 60000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <Head title="Ketersediaan Lapangan — PadelHub" />
    <AuthenticatedLayout :dark="true">
        <template #header>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3.5 py-1 text-[11px] font-bold text-emerald-400">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                        <span>REALTIME SLOT MONITORING</span>
                        <span class="text-slate-500">·</span>
                        <span class="text-[10px] text-slate-400">Live Sync 60s</span>
                    </div>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">Ketersediaan Lapangan</h2>
                    <p class="mt-1 text-sm text-slate-400">
                        Pantau jadwal ketersediaan lapangan secara langsung. Klik slot kosong untuk booking cepat.
                    </p>
                </div>

                <!-- Header Actions -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <button
                        type="button"
                        @click="refreshData"
                        :disabled="isRefreshing"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-900/80 px-4 py-2.5 text-xs font-semibold text-slate-300 shadow-sm transition hover:border-slate-700 hover:bg-slate-800 hover:text-white disabled:opacity-50"
                        title="Perbarui ketersediaan slot terbaru"
                    >
                        <RefreshCw class="h-3.5 w-3.5 text-emerald-400" :class="{ 'animate-spin': isRefreshing }" />
                        <span>{{ isRefreshing ? 'Menyinkronkan…' : 'Refresh Slot' }}</span>
                    </button>

                    <Link
                        :href="route('bookings.index')"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-4 py-2.5 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition hover:scale-105"
                    >
                        <CalendarCheck class="h-4 w-4" />
                        <span>Riwayat Booking Saya</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- 4 KPI SUMMARY CARDS -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Metric 1: Lapangan Terpantau -->
                    <div class="group relative overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-xl backdrop-blur-md transition-all hover:border-emerald-500/30">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Lapangan Terpantau</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400 ring-1 ring-emerald-500/20">
                                <Layers class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-3xl font-black tracking-tight text-white">{{ filteredCourts.length }}</p>
                        <p class="mt-1 text-xs text-slate-400">
                            Dari total {{ courts.length }} lapangan terdaftar
                        </p>
                    </div>

                    <!-- Metric 2: Slot Siap Booking -->
                    <div class="group relative overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-xl backdrop-blur-md transition-all hover:border-emerald-500/30">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Slot Siap Booking</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-500/30">
                                <Sparkles class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-3xl font-black tracking-tight text-emerald-400">{{ slotStats.available }}</p>
                        <p class="mt-1 text-xs text-slate-400">
                            Slot kosong pada {{ formatIndoDate(localDate) }}
                        </p>
                    </div>

                    <!-- Metric 3: Slot Terisi & Pending -->
                    <div class="group relative overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-xl backdrop-blur-md transition-all hover:border-amber-500/30">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Terisi & Pending</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/15 text-amber-400 ring-1 ring-amber-500/30">
                                <Activity class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-3xl font-black tracking-tight text-white">
                            {{ slotStats.booked + slotStats.pending }}
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ slotStats.booked }} dikonfirmasi · {{ slotStats.pending }} pending
                        </p>
                    </div>

                    <!-- Metric 4: Okupansi Lapangan -->
                    <div class="group relative overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-xl backdrop-blur-md transition-all hover:border-sky-500/30">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tingkat Okupansi</span>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500/15 text-sky-400 ring-1 ring-sky-500/30">
                                <Flame class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-3xl font-black tracking-tight text-white">{{ slotStats.occupancyRate }}%</p>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ lastUpdatedTime ? `Sync: ${lastUpdatedTime}` : 'Otomatis tersinkronisasi' }}
                        </p>
                    </div>
                </div>

                <!-- MAIN CONTROL FILTER PANEL -->
                <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-2xl backdrop-blur-md sm:p-6 space-y-5">
                    <!-- Row 1: Date Quick Jumpers + Calendar Picker -->
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <!-- Quick Date Pills -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1 flex items-center gap-1.5">
                                <Calendar class="h-3.5 w-3.5 text-emerald-400" />
                                <span>Pilih Tanggal:</span>
                            </span>

                            <button
                                type="button"
                                @click="changeDate(todayStr)"
                                class="rounded-xl px-3.5 py-2 text-xs font-bold transition shadow-sm"
                                :class="localDate === todayStr
                                    ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20'
                                    : 'border border-slate-800 bg-slate-950/80 text-slate-300 hover:border-slate-700 hover:bg-slate-800 hover:text-white'"
                            >
                                Hari Ini
                            </button>

                            <button
                                type="button"
                                @click="changeDate(tomorrowStr)"
                                class="rounded-xl px-3.5 py-2 text-xs font-bold transition shadow-sm"
                                :class="localDate === tomorrowStr
                                    ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20'
                                    : 'border border-slate-800 bg-slate-950/80 text-slate-300 hover:border-slate-700 hover:bg-slate-800 hover:text-white'"
                            >
                                Besok
                            </button>

                            <button
                                type="button"
                                @click="changeDate(dayAfterStr)"
                                class="rounded-xl px-3.5 py-2 text-xs font-bold transition shadow-sm"
                                :class="localDate === dayAfterStr
                                    ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20'
                                    : 'border border-slate-800 bg-slate-950/80 text-slate-300 hover:border-slate-700 hover:bg-slate-800 hover:text-white'"
                            >
                                Lusa
                            </button>

                            <!-- Date Picker Input -->
                            <div class="relative ml-1">
                                <CalendarDays class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                                <input
                                    v-model="localDate"
                                    type="date"
                                    :min="todayStr"
                                    @change="changeDate(($event.target as HTMLInputElement).value)"
                                    class="h-9 rounded-xl border border-slate-800 bg-slate-950/80 pl-9 pr-3 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                />
                            </div>
                        </div>

                        <!-- Active Date Banner Label -->
                        <div class="inline-flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-950/60 px-3.5 py-1.5 text-xs text-slate-300">
                            <span class="h-2 w-2 rounded-full bg-emerald-400" />
                            <span>Jadwal untuk: <strong class="text-white">{{ formatIndoDate(localDate) }}</strong></span>
                        </div>
                    </div>

                    <!-- Row 2: Search, Venue Select, Type Filter, & View Mode Switcher -->
                    <div class="flex flex-col gap-3 pt-3 border-t border-slate-800/80 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-wrap items-center gap-2.5 flex-1">
                            <!-- Search Court -->
                            <div class="relative w-full sm:w-60">
                                <Search class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                                <input
                                    v-model="searchQuery"
                                    type="search"
                                    placeholder="Cari lapangan atau venue…"
                                    class="h-9 w-full rounded-xl border border-slate-800 bg-slate-950/80 pl-9 pr-3 text-xs text-white placeholder-slate-400 shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                />
                            </div>

                            <!-- Venue Dropdown -->
                            <div class="w-full sm:w-56">
                                <select
                                    v-model="venueId"
                                    class="h-9 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 [&>option]:bg-slate-900 [&>option]:text-white"
                                >
                                    <option :value="''">Semua Venue Padel</option>
                                    <option v-for="v in venues" :key="v.id" :value="v.id">{{ v.name }} ({{ v.city }})</option>
                                </select>
                            </div>

                            <!-- Court Type Tabs -->
                            <div class="inline-flex rounded-xl border border-slate-800 bg-slate-950/80 p-0.5">
                                <button
                                    type="button"
                                    @click="typeFilter = 'all'"
                                    class="rounded-lg px-2.5 py-1 text-xs font-semibold transition"
                                    :class="typeFilter === 'all' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:text-white'"
                                >
                                    Semua
                                </button>
                                <button
                                    type="button"
                                    @click="typeFilter = 'indoor'"
                                    class="rounded-lg px-2.5 py-1 text-xs font-semibold transition"
                                    :class="typeFilter === 'indoor' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:text-white'"
                                >
                                    Indoor
                                </button>
                                <button
                                    type="button"
                                    @click="typeFilter = 'outdoor'"
                                    class="rounded-lg px-2.5 py-1 text-xs font-semibold transition"
                                    :class="typeFilter === 'outdoor' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:text-white'"
                                >
                                    Outdoor
                                </button>
                            </div>
                        </div>

                        <!-- View Mode Switcher -->
                        <div class="flex items-center gap-1.5 self-end sm:self-auto">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tampilan:</span>
                            <div class="inline-flex rounded-xl border border-slate-800 bg-slate-950/80 p-0.5">
                                <button
                                    type="button"
                                    @click="viewMode = 'court'"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-semibold transition"
                                    :class="viewMode === 'court' ? 'bg-emerald-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
                                    title="Tampilan Denah Lapangan Standar"
                                >
                                    <Sparkles class="h-3.5 w-3.5" />
                                    <span>Denah Court</span>
                                </button>
                                <button
                                    type="button"
                                    @click="viewMode = 'grid'"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-semibold transition"
                                    :class="viewMode === 'grid' ? 'bg-emerald-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
                                    title="Tampilan Matriks Timeline"
                                >
                                    <Clock class="h-3.5 w-3.5" />
                                    <span>Timeline</span>
                                </button>
                                <button
                                    type="button"
                                    @click="viewMode = 'cards'"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-semibold transition"
                                    :class="viewMode === 'cards' ? 'bg-emerald-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
                                    title="Tampilan Kartu per Lapangan"
                                >
                                    <LayoutGrid class="h-3.5 w-3.5" />
                                    <span>Kartu</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Visual Legend -->
                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-800/80 pt-4 text-xs font-semibold">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-slate-400">Petunjuk Warna:</span>
                            <span class="inline-flex items-center gap-1.5 rounded-md border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1 text-[11px] text-emerald-400">
                                <span class="h-2 w-2 rounded-full bg-emerald-400" />
                                <span>Tersedia (Klik untuk booking)</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-md border border-amber-500/30 bg-amber-500/10 px-2.5 py-1 text-[11px] text-amber-400">
                                <span class="h-2 w-2 rounded-full bg-amber-400" />
                                <span>Pending (Menunggu Bayar)</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-md border border-rose-500/30 bg-rose-500/10 px-2.5 py-1 text-[11px] text-rose-400">
                                <span class="h-2 w-2 rounded-full bg-rose-400" />
                                <span>Terisi (Telah Dibayar)</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-md border border-slate-700 bg-slate-800/60 px-2.5 py-1 text-[11px] text-slate-400">
                                <span class="h-2 w-2 rounded-full bg-slate-500" />
                                <span>Perawatan / Tutup</span>
                            </span>
                        </div>

                        <span class="text-[11px] text-slate-400">
                            Rentang waktu operasional: <strong class="text-white">07:00 – 22:00 WIB</strong>
                        </span>
                    </div>
                </div>

                <!-- VIEW MODE 0: OFFICIAL VISUAL COURT BOARD (SEPERTI WELCOME PAGE) -->
                <div v-if="viewMode === 'court' && filteredCourts.length">
                    <PadelCourtBoard
                        :courts="filteredCourts"
                        :bookings="bookings"
                        :initial-date="localDate"
                        route-target="availability.index"
                    />
                </div>

                <!-- VIEW MODE 1: TIMELINE MATRIX -->
                <div v-if="viewMode === 'grid' && filteredCourts.length" class="overflow-hidden rounded-3xl border border-slate-800/80 bg-slate-900/60 shadow-2xl backdrop-blur-md">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 bg-slate-950/90 text-slate-300">
                                    <th class="sticky left-0 z-20 min-w-64 border-r border-slate-800/80 bg-slate-950 px-5 py-4 text-left font-black tracking-wider text-white">
                                        <div class="flex items-center gap-2">
                                            <Building2 class="h-4 w-4 text-emerald-400" />
                                            <span>LAPANGAN & VENUE</span>
                                        </div>
                                    </th>
                                    <th
                                        v-for="h in hours"
                                        :key="h"
                                        class="min-w-[62px] px-1.5 py-3.5 text-center font-bold"
                                    >
                                        <div class="text-[11px] font-extrabold text-white">
                                            {{ String(h).padStart(2, '0') }}:00
                                        </div>
                                        <div class="text-[9.5px] text-slate-400">
                                            {{ String(h + 1).padStart(2, '0') }}:00
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                <tr
                                    v-for="court in filteredCourts"
                                    :key="court.id"
                                    class="transition hover:bg-slate-800/25"
                                >
                                    <!-- Sticky Left Court Column -->
                                    <td class="sticky left-0 z-10 border-r border-slate-800/80 bg-slate-900/95 px-5 py-3.5 backdrop-blur-md">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-800 text-slate-300">
                                                <Layers class="h-4 w-4 text-emerald-400" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="h-2 w-2 rounded-full" :class="court.status === 'maintenance' ? 'bg-amber-400' : 'bg-emerald-400'" />
                                                    <p class="truncate font-black text-white text-sm">{{ court.name }}</p>
                                                </div>
                                                <p class="truncate text-[11px] text-slate-400 mt-0.5">
                                                    {{ court.venue.name }} · {{ court.venue.city ?? 'Venue' }}
                                                </p>
                                                <div class="mt-1 flex items-center gap-2">
                                                    <span class="rounded bg-slate-800/80 px-1.5 py-0.5 text-[9.5px] font-bold uppercase text-slate-300">
                                                        {{ court.type ?? 'Indoor' }}
                                                    </span>
                                                    <span class="font-extrabold text-emerald-400 text-xs">
                                                        Rp {{ Number(court.price_per_hour).toLocaleString('id-ID') }}<span class="text-[10px] text-slate-400">/jam</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Time Slots -->
                                    <td
                                        v-for="h in hours"
                                        :key="h"
                                        class="p-1 text-center"
                                    >
                                        <!-- Available Slot -->
                                        <button
                                            v-if="slotState(court.id, h) === 'available'"
                                            type="button"
                                            @click="openBookingModal(court, h)"
                                            class="group relative flex h-11 w-full flex-col items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 transition-all hover:scale-105 hover:border-emerald-400 hover:bg-gradient-to-r hover:from-emerald-500 hover:to-green-600 hover:shadow-lg hover:shadow-emerald-500/25 cursor-pointer"
                                            :title="`Klik untuk booking ${court.name} pukul ${String(h).padStart(2, '0')}:00`"
                                        >
                                            <span class="text-xs font-black text-emerald-300 transition group-hover:text-slate-950">+</span>
                                            <span class="text-[9px] font-semibold text-emerald-400/80 transition group-hover:text-slate-950">Slot</span>
                                        </button>

                                        <!-- Pending Slot -->
                                        <div
                                            v-else-if="slotState(court.id, h) === 'pending'"
                                            class="flex h-11 w-full flex-col items-center justify-center rounded-xl border border-amber-500/30 bg-amber-500/15 text-amber-400 cursor-not-allowed"
                                            :title="`Slot ${String(h).padStart(2, '0')}:00 sedang diproses/menunggu pembayaran`"
                                        >
                                            <Clock class="h-3.5 w-3.5 animate-pulse" />
                                            <span class="text-[9px] font-bold">Pending</span>
                                        </div>

                                        <!-- Booked Slot -->
                                        <div
                                            v-else-if="slotState(court.id, h) === 'booked'"
                                            class="flex h-11 w-full flex-col items-center justify-center rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-400/90 cursor-not-allowed"
                                            :title="`Slot ${String(h).padStart(2, '0')}:00 sudah terisi`"
                                        >
                                            <Users class="h-3.5 w-3.5 opacity-70" />
                                            <span class="text-[9px] font-bold">Terisi</span>
                                        </div>

                                        <!-- Maintenance Slot -->
                                        <div
                                            v-else
                                            class="flex h-11 w-full flex-col items-center justify-center rounded-xl border border-slate-700/60 bg-slate-800/40 text-slate-400 cursor-not-allowed"
                                            :title="`Lapangan dalam perawatan pada jam ${String(h).padStart(2, '0')}:00`"
                                        >
                                            <Wrench class="h-3.5 w-3.5 opacity-60" />
                                            <span class="text-[9px] font-bold">Tutup</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- VIEW MODE 2: CARDS PER LAPANGAN (MOBILE/TABLET FRIENDLY) -->
                <div v-else-if="viewMode === 'cards' && filteredCourts.length" class="space-y-5">
                    <div
                        v-for="court in filteredCourts"
                        :key="court.id"
                        class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-2xl backdrop-blur-md sm:p-6"
                    >
                        <!-- Court Card Header -->
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-800/80 pb-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 text-emerald-400 ring-1 ring-emerald-500/30">
                                    <Layers class="h-5 w-5" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-base font-extrabold text-white">{{ court.name }}</h3>
                                        <span class="rounded-md border border-slate-700 bg-slate-800/60 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-300">
                                            {{ court.type ?? 'Indoor' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                                        <MapPin class="h-3 w-3 text-emerald-400" />
                                        {{ court.venue.name }} · {{ court.venue.city ?? 'Kota' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-left sm:text-right">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tarif Sewa</p>
                                    <p class="text-base font-black text-emerald-400">
                                        Rp {{ Number(court.price_per_hour).toLocaleString('id-ID') }}<span class="text-xs font-normal text-slate-400">/jam</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- 3 Day Sections (Pagi, Siang, Malam) -->
                        <div class="mt-5 space-y-4">
                            <!-- Pagi (07:00 - 11:00) -->
                            <div>
                                <div class="flex items-center gap-2 text-xs font-bold text-slate-300 mb-2.5">
                                    <Sunrise class="h-4 w-4 text-amber-400" />
                                    <span>Sesi Pagi (07:00 – 11:00 WIB)</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-5">
                                    <button
                                        v-for="h in morningHours"
                                        :key="h"
                                        type="button"
                                        :disabled="slotState(court.id, h) !== 'available'"
                                        @click="openBookingModal(court, h)"
                                        class="flex items-center justify-between rounded-xl border p-2.5 transition text-left"
                                        :class="slotState(court.id, h) === 'available'
                                            ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300 hover:scale-[1.02] hover:border-emerald-400 hover:bg-emerald-500 hover:text-slate-950 cursor-pointer'
                                            : slotState(court.id, h) === 'pending'
                                            ? 'border-amber-500/30 bg-amber-500/10 text-amber-400 opacity-80 cursor-not-allowed'
                                            : slotState(court.id, h) === 'booked'
                                            ? 'border-rose-500/30 bg-rose-500/10 text-rose-400 opacity-70 cursor-not-allowed'
                                            : 'border-slate-800 bg-slate-950/40 text-slate-400 opacity-40 cursor-not-allowed'"
                                    >
                                        <div>
                                            <span class="block text-xs font-black">{{ String(h).padStart(2, '0') }}:00</span>
                                            <span class="block text-[10px] opacity-80">{{ String(h + 1).padStart(2, '0') }}:00</span>
                                        </div>
                                        <span class="text-[10px] font-bold">
                                            {{ slotState(court.id, h) === 'available' ? 'Kosong' : slotState(court.id, h) === 'pending' ? 'Pending' : slotState(court.id, h) === 'booked' ? 'Terisi' : 'Tutup' }}
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <!-- Siang (12:00 - 15:00) -->
                            <div>
                                <div class="flex items-center gap-2 text-xs font-bold text-slate-300 mb-2.5">
                                    <Sun class="h-4 w-4 text-amber-400" />
                                    <span>Sesi Siang (12:00 – 15:00 WIB)</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                    <button
                                        v-for="h in afternoonHours"
                                        :key="h"
                                        type="button"
                                        :disabled="slotState(court.id, h) !== 'available'"
                                        @click="openBookingModal(court, h)"
                                        class="flex items-center justify-between rounded-xl border p-2.5 transition text-left"
                                        :class="slotState(court.id, h) === 'available'
                                            ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300 hover:scale-[1.02] hover:border-emerald-400 hover:bg-emerald-500 hover:text-slate-950 cursor-pointer'
                                            : slotState(court.id, h) === 'pending'
                                            ? 'border-amber-500/30 bg-amber-500/10 text-amber-400 opacity-80 cursor-not-allowed'
                                            : slotState(court.id, h) === 'booked'
                                            ? 'border-rose-500/30 bg-rose-500/10 text-rose-400 opacity-70 cursor-not-allowed'
                                            : 'border-slate-800 bg-slate-950/40 text-slate-400 opacity-40 cursor-not-allowed'"
                                    >
                                        <div>
                                            <span class="block text-xs font-black">{{ String(h).padStart(2, '0') }}:00</span>
                                            <span class="block text-[10px] opacity-80">{{ String(h + 1).padStart(2, '0') }}:00</span>
                                        </div>
                                        <span class="text-[10px] font-bold">
                                            {{ slotState(court.id, h) === 'available' ? 'Kosong' : slotState(court.id, h) === 'pending' ? 'Pending' : slotState(court.id, h) === 'booked' ? 'Terisi' : 'Tutup' }}
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <!-- Malam (16:00 - 21:00) -->
                            <div>
                                <div class="flex items-center gap-2 text-xs font-bold text-slate-300 mb-2.5">
                                    <Moon class="h-4 w-4 text-sky-400" />
                                    <span>Sesi Sore & Malam (16:00 – 21:00 WIB)</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-6">
                                    <button
                                        v-for="h in eveningHours"
                                        :key="h"
                                        type="button"
                                        :disabled="slotState(court.id, h) !== 'available'"
                                        @click="openBookingModal(court, h)"
                                        class="flex items-center justify-between rounded-xl border p-2.5 transition text-left"
                                        :class="slotState(court.id, h) === 'available'
                                            ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300 hover:scale-[1.02] hover:border-emerald-400 hover:bg-emerald-500 hover:text-slate-950 cursor-pointer'
                                            : slotState(court.id, h) === 'pending'
                                            ? 'border-amber-500/30 bg-amber-500/10 text-amber-400 opacity-80 cursor-not-allowed'
                                            : slotState(court.id, h) === 'booked'
                                            ? 'border-rose-500/30 bg-rose-500/10 text-rose-400 opacity-70 cursor-not-allowed'
                                            : 'border-slate-800 bg-slate-950/40 text-slate-400 opacity-40 cursor-not-allowed'"
                                    >
                                        <div>
                                            <span class="block text-xs font-black">{{ String(h).padStart(2, '0') }}:00</span>
                                            <span class="block text-[10px] opacity-80">{{ String(h + 1).padStart(2, '0') }}:00</span>
                                        </div>
                                        <span class="text-[10px] font-bold">
                                            {{ slotState(court.id, h) === 'available' ? 'Kosong' : slotState(court.id, h) === 'pending' ? 'Pending' : slotState(court.id, h) === 'booked' ? 'Terisi' : 'Tutup' }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="rounded-3xl border border-dashed border-slate-800 bg-slate-900/40 py-16 text-center">
                    <p class="font-bold text-white text-base">Tidak ada lapangan yang sesuai filter</p>
                    <p class="mt-1 text-xs text-slate-400">Silakan ubah venue atau hapus kata kunci pencarian.</p>
                    <button
                        type="button"
                        @click="venueId = ''; searchQuery = ''; typeFilter = 'all'"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:bg-slate-700 hover:text-white"
                    >
                        Reset Filter
                    </button>
                </div>

                <!-- Helper Footer Note -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400 pt-2 border-t border-slate-800/80">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse" />
                        <p>
                            Status ketersediaan tersinkronisasi otomatis setiap 60 detik. Terakhir update: <span class="font-semibold text-white">{{ lastUpdatedTime }}</span>.
                        </p>
                    </div>
                    <p class="text-[11px] text-slate-400">
                        Butuh reservasi khusus/event turnamen? Hubungi customer care venue terkait.
                    </p>
                </div>
            </div>
        </div>

        <!-- QUICK BOOKING MODAL / CONFIRMATION SHEET -->
        <div
            v-if="selectedSlotData"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md animate-in fade-in duration-200"
            @click.self="selectedSlotData = null"
        >
            <div class="relative w-full max-w-md overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-2xl backdrop-blur-xl sm:p-7">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 text-emerald-400 ring-1 ring-emerald-500/30">
                            <Sparkles class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-400">Konfirmasi Pemilihan Slot</p>
                            <h3 class="text-base font-black text-white">Lanjut Booking Lapangan?</h3>
                        </div>
                    </div>
                    <button
                        type="button"
                        aria-label="Tutup modal"
                        @click="selectedSlotData = null"
                        class="rounded-xl p-1.5 text-slate-400 transition hover:bg-slate-800 hover:text-white"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Slot Detail Box -->
                <div class="mt-5 space-y-3 rounded-2xl border border-slate-800 bg-slate-950/80 p-4">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Lapangan:</span>
                        <span class="font-bold text-white">{{ selectedSlotData.courtName }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Venue:</span>
                        <span class="font-semibold text-slate-200">{{ selectedSlotData.venueName }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Tanggal:</span>
                        <span class="font-bold text-emerald-300">{{ formatIndoDate(selectedSlotData.date) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Jam Sesi:</span>
                        <span class="rounded bg-emerald-500/10 px-2 py-0.5 font-mono font-bold text-emerald-400">
                            {{ String(selectedSlotData.hour).padStart(2, '0') }}:00 – {{ String(selectedSlotData.hour + 1).padStart(2, '0') }}:00 WIB
                        </span>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-800/80 pt-2 text-xs">
                        <span class="font-bold text-slate-300">Estimasi Tarif:</span>
                        <span class="text-base font-black text-emerald-400">
                            Rp {{ Number(selectedSlotData.price).toLocaleString('id-ID') }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2 text-[11px] text-slate-400">
                    <CheckCircle2 class="h-4 w-4 text-emerald-400 shrink-0" />
                    <span>Slot akan dikunci selama 15 menit setelah formulir reservasi diajukan.</span>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex items-center gap-3">
                    <button
                        type="button"
                        @click="selectedSlotData = null"
                        class="flex-1 rounded-xl border border-slate-700 bg-slate-800 py-3 text-xs font-semibold text-slate-300 transition hover:bg-slate-700 hover:text-white"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="proceedToBooking"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 py-3 text-xs font-bold text-slate-950 shadow-lg shadow-emerald-500/25 transition hover:scale-105"
                    >
                        <span>Lanjut Booking</span>
                        <ArrowRight class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
