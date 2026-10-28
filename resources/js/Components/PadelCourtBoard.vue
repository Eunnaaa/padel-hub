<script setup lang="ts">
import {
    Calendar,
    Check,
    ChevronRight,
    Clock,
    Layers,
} from '@lucide/vue';
import { router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

export interface CourtItem {
    id: number;
    name: string;
    type?: string;
    price_per_hour: string;
    status: string;
    maintenance_from?: string | null;
    maintenance_to?: string | null;
    venue: {
        id: number;
        name: string;
        city?: string;
        slug?: string;
        rating?: string;
    };
}

export interface BookingItem {
    court_id: number;
    start_time: string;
    end_time: string;
    status: string;
}

const props = withDefaults(
    defineProps<{
        courts: CourtItem[];
        bookings?: BookingItem[];
        initialDate?: string;
        routeTarget?: string; // route name to navigate when changing date
        hideHeaderInfo?: boolean;
    }>(),
    {
        bookings: () => [],
        routeTarget: '',
        hideHeaderInfo: false,
    },
);

const emit = defineEmits<{
    (e: 'change-date', date: string): void;
    (e: 'select-slot', data: { courtId: number; hour: number; date: string }): void;
}>();

import { formatLocalDate } from '@/lib/utils';

const now = new Date();
const todayStr = formatLocalDate(now);

const tomorrowObj = new Date();
tomorrowObj.setDate(now.getDate() + 1);
const tomorrowStr = formatLocalDate(tomorrowObj);

const currentDate = ref<string>(props.initialDate || todayStr);
const activeCourtId = ref<number | null>(null);
const selectedSlot = ref<number | null>(null);

watch(
    () => props.initialDate,
    (newVal) => {
        if (newVal) currentDate.value = newVal;
    },
);

watch(
    () => props.courts,
    (newCourts) => {
        if (newCourts.length > 0 && (!activeCourtId.value || !newCourts.some((c) => c.id === activeCourtId.value))) {
            activeCourtId.value = newCourts[0].id;
        }
    },
    { immediate: true },
);

onMounted(() => {
    if (props.courts.length > 0 && !activeCourtId.value) {
        activeCourtId.value = props.courts[0].id;
    }
});

const activeCourt = computed(() => {
    return props.courts.find((c) => c.id === activeCourtId.value) ?? props.courts[0] ?? null;
});

const fmtPrice = (price: string) => `Rp ${Number(price).toLocaleString('id-ID')}`;
const typeLabel = (t?: string) => ((t ?? '').toLowerCase() === 'indoor' ? 'Indoor' : 'Outdoor');

const sideASlots = [7, 8, 9, 10, 11, 12, 13, 14];
const sideBSlots = [15, 16, 17, 18, 19, 20, 21, 22];
const allHours = [...sideASlots, ...sideBSlots];

const toHHMM = (t: string) => t.slice(0, 5);

const getSlotState = (courtId: number, hour: number): 'available' | 'booked' | 'pending' | 'maintenance' => {
    const court = props.courts.find((c) => c.id === courtId);
    if (!court || court.status === 'inactive' || court.status === 'maintenance') return 'maintenance';

    const start = `${String(hour).padStart(2, '0')}:00`;
    const end = `${String(hour + 1).padStart(2, '0')}:00`;

    if (court.maintenance_from && court.maintenance_to) {
        if (toHHMM(court.maintenance_from) < end && toHHMM(court.maintenance_to) > start) {
            return 'maintenance';
        }
    }

    const bookingList = props.bookings ?? [];
    const b = bookingList.find(
        (item) => item.court_id === courtId && toHHMM(item.start_time) < end && toHHMM(item.end_time) > start,
    );

    if (!b) return 'available';
    return b.status === 'pending' ? 'pending' : 'booked';
};

const activeCourtStats = computed(() => {
    if (!activeCourt.value) return { available: 0, booked: 0, pending: 0, maintenance: 0 };
    let available = 0;
    let booked = 0;
    let pending = 0;
    let maintenance = 0;

    allHours.forEach((h) => {
        const s = getSlotState(activeCourt.value!.id, h);
        if (s === 'available') available++;
        else if (s === 'booked') booked++;
        else if (s === 'pending') pending++;
        else if (s === 'maintenance') maintenance++;
    });

    return { available, booked, pending, maintenance };
});

const changeDate = (newDate: string) => {
    currentDate.value = newDate;
    selectedSlot.value = null;
    emit('change-date', newDate);

    if (props.routeTarget) {
        router.get(
            route(props.routeTarget),
            { date: newDate },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    } else {
        router.get(
            '',
            { date: newDate },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }
};

const selectSlot = (hour: number) => {
    if (!activeCourt.value) return;
    const state = getSlotState(activeCourt.value.id, hour);
    if (state !== 'available') return;
    selectedSlot.value = selectedSlot.value === hour ? null : hour;

    if (selectedSlot.value !== null) {
        emit('select-slot', {
            courtId: activeCourt.value.id,
            hour,
            date: currentDate.value,
        });
    }
};

const bookSelectedSlot = () => {
    if (!activeCourt.value || selectedSlot.value === null) return;
    const startTime = `${String(selectedSlot.value).padStart(2, '0')}:00`;
    const endTime = `${String(selectedSlot.value + 1).padStart(2, '0')}:00`;
    router.visit(
        route('bookings.create', {
            court_id: activeCourt.value.id,
            booking_date: currentDate.value,
            start_time: startTime,
            end_time: endTime,
        }),
    );
};
</script>

<template>
    <div
        id="court-visualizer"
        class="overflow-hidden rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md sm:p-8"
    >
        <!-- Board Controls: 2-Tier Clean Hierarchy -->
        <div class="space-y-4">
            <!-- Tier 1: Toolbar Header (Context Title + Unified Date Switcher) -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-800/80 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-emerald-400">
                        <Layers class="h-3.5 w-3.5" />
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black uppercase tracking-wider text-white">Pilih Lapangan</span>
                        <span class="rounded-full border border-slate-800 bg-slate-950/80 px-2 py-0.5 text-[10px] font-bold text-slate-400">
                            {{ courts.length }} Lapangan
                        </span>
                    </div>
                </div>

                <!-- Unified Date Switcher Pill -->
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 hidden sm:inline">Tanggal:</span>
                    <div class="inline-flex items-center gap-1 rounded-xl border border-slate-800 bg-slate-950/90 p-1 shadow-inner shrink-0">
                        <button
                            type="button"
                            class="rounded-lg px-3 py-1.5 text-xs font-bold transition-all"
                            :class="currentDate === todayStr
                                ? 'bg-emerald-500 text-slate-950 shadow-sm shadow-emerald-500/30 font-extrabold'
                                : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                            @click="changeDate(todayStr)"
                        >
                            Hari Ini
                        </button>
                        <button
                            type="button"
                            class="rounded-lg px-3 py-1.5 text-xs font-bold transition-all"
                            :class="currentDate === tomorrowStr
                                ? 'bg-emerald-500 text-slate-950 shadow-sm shadow-emerald-500/30 font-extrabold'
                                : 'text-slate-300 hover:text-white hover:bg-slate-800/70'"
                            @click="changeDate(tomorrowStr)"
                        >
                            Besok
                        </button>
                        <div class="h-4 w-px bg-slate-800 mx-0.5" />
                        <div class="relative flex items-center pr-1">
                            <input
                                type="date"
                                :value="currentDate"
                                :min="todayStr"
                                @change="changeDate(($event.target as HTMLInputElement).value)"
                                class="h-7 rounded-lg border-0 bg-transparent px-2 text-xs font-semibold text-slate-200 focus:bg-slate-900 focus:outline-none focus:ring-1 focus:ring-emerald-500 [color-scheme:dark] cursor-pointer"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tier 2: Court Selection Pills (Full Width Row) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1.5 no-scrollbar sm:flex-wrap sm:overflow-visible">
                <button
                    v-for="court in courts"
                    :key="court.id"
                    type="button"
                    class="group inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all shadow-sm"
                    :class="activeCourtId === court.id
                        ? 'bg-gradient-to-r from-emerald-500 to-green-600 text-slate-950 shadow-md shadow-emerald-500/25 ring-2 ring-emerald-400/60 font-extrabold'
                        : 'border border-slate-800/90 bg-slate-950/70 text-slate-300 hover:border-slate-700 hover:bg-slate-800 hover:text-white'"
                    @click="activeCourtId = court.id; selectedSlot = null"
                >
                    <span>{{ court.name }}</span>
                    <span
                        class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase transition-colors"
                        :class="activeCourtId === court.id ? 'bg-slate-950/25 text-slate-950' : 'bg-slate-800 text-slate-400 group-hover:text-slate-300'"
                    >
                        {{ typeLabel(court.type) }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Court Info Header & Legend -->
        <div class="mt-6 flex flex-col gap-4 border-t border-slate-800/80 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <div v-if="activeCourt" class="flex flex-wrap items-center gap-3">
                <h3 class="text-xl font-black tracking-tight text-white">{{ activeCourt.name }}</h3>
                <span class="text-sm text-slate-400">· {{ activeCourt.venue.name }}, {{ activeCourt.venue.city ?? 'Venue' }}</span>
                <span class="rounded-full border border-emerald-500/30 bg-emerald-500/15 px-3 py-1 text-xs font-black text-emerald-400">
                    {{ fmtPrice(activeCourt.price_per_hour) }}/jam
                </span>
            </div>

            <!-- Color Legend with Counters -->
            <div class="flex flex-wrap items-center gap-3 text-xs font-semibold">
                <span class="flex items-center gap-1.5 text-slate-200">
                    <span class="h-3.5 w-3.5 rounded-md bg-emerald-500 shadow-sm shadow-emerald-500/50" />
                    Kosong ({{ activeCourtStats.available }})
                </span>
                <span class="flex items-center gap-1.5 text-slate-200">
                    <span class="h-3.5 w-3.5 rounded-md bg-rose-500 shadow-sm shadow-rose-500/50" />
                    Terisi ({{ activeCourtStats.booked }})
                </span>
                <span class="flex items-center gap-1.5 text-slate-200">
                    <span class="h-3.5 w-3.5 rounded-md bg-amber-400 shadow-sm shadow-amber-400/50" />
                    Pending ({{ activeCourtStats.pending }})
                </span>
                <span class="flex items-center gap-1.5 text-slate-400">
                    <span class="h-3.5 w-3.5 rounded-md border border-slate-700 bg-slate-800" />
                    Tutup ({{ activeCourtStats.maintenance }})
                </span>
            </div>
        </div>

        <!-- THE VISUAL PADEL COURT LAYOUT (10M x 20M PROPORTION) -->
        <div class="relative mt-6 overflow-hidden rounded-2xl border-2 border-emerald-500/30 bg-gradient-to-b from-[#064232] via-[#093528] to-[#04241b] p-4 shadow-inner sm:p-6">
            <!-- Top Glass Wall Indicator -->
            <div class="mb-4 flex items-center justify-between border-b border-emerald-400/20 pb-2 text-[11px] font-bold uppercase tracking-widest text-emerald-300/70">
                <span class="flex items-center gap-1.5">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-cyan-400" />
                    Glass Wall · Dinding Kaca Belakang (3M)
                </span>
                <span class="hidden sm:inline">PadelHub Stadium Standard</span>
            </div>

            <!-- Side A: Morning & Midday Slots (07:00 - 14:00) -->
            <div class="mb-4">
                <div class="mb-2 flex items-center justify-between text-xs font-semibold text-emerald-200/80">
                    <span>SISI A · PAGI & SIANG (07:00 – 14:00 WIB)</span>
                    <span class="text-[10px] text-white/50">Area Servis Sisi A</span>
                </div>
                <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-4">
                    <button
                        v-for="hour in sideASlots"
                        :key="hour"
                        type="button"
                        :disabled="getSlotState(activeCourt?.id ?? 0, hour) !== 'available'"
                        @click="selectSlot(hour)"
                        class="group relative flex select-none flex-col items-center justify-between rounded-xl border p-3 text-center transition duration-200"
                        :class="[
                            selectedSlot === hour
                                ? 'ring-4 ring-white bg-emerald-500 border-white text-slate-950 font-bold shadow-xl scale-[1.04] z-10'
                                : getSlotState(activeCourt?.id ?? 0, hour) === 'available'
                                ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300 hover:bg-emerald-500 hover:text-slate-950 hover:border-emerald-400 hover:scale-[1.02] hover:shadow-lg hover:shadow-emerald-500/20 cursor-pointer font-semibold'
                                : getSlotState(activeCourt?.id ?? 0, hour) === 'booked'
                                ? 'bg-rose-500/20 border-rose-500/40 text-rose-300 opacity-75 cursor-not-allowed'
                                : getSlotState(activeCourt?.id ?? 0, hour) === 'pending'
                                ? 'bg-amber-500/20 border-amber-500/40 text-amber-300 opacity-80 cursor-not-allowed'
                                : 'border-slate-800 bg-slate-950/40 text-slate-400 opacity-40 cursor-not-allowed'
                        ]"
                        :title="`${String(hour).padStart(2, '0')}:00 – ${String(hour + 1).padStart(2, '0')}:00 (${getSlotState(activeCourt?.id ?? 0, hour)})`"
                    >
                        <div class="flex items-center gap-1.5 text-xs font-black tracking-tight">
                            <Clock class="h-3.5 w-3.5 opacity-80" />
                            <span>{{ String(hour).padStart(2, '0') }}:00</span>
                        </div>
                        <span class="mt-1 text-[10px] opacity-75">
                            {{ String(hour).padStart(2, '0') }}:00–{{ String(hour + 1).padStart(2, '0') }}:00
                        </span>
                        <div class="mt-2 flex items-center gap-1 text-[10px] font-bold">
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                :class="selectedSlot === hour ? 'bg-slate-950' : getSlotState(activeCourt?.id ?? 0, hour) === 'available' ? 'bg-emerald-400' : getSlotState(activeCourt?.id ?? 0, hour) === 'booked' ? 'bg-rose-400' : getSlotState(activeCourt?.id ?? 0, hour) === 'pending' ? 'bg-amber-400' : 'bg-gray-400'"
                            />
                            <span v-if="selectedSlot === hour">Dipilih</span>
                            <span v-else-if="getSlotState(activeCourt?.id ?? 0, hour) === 'available'">Kosong</span>
                            <span v-else-if="getSlotState(activeCourt?.id ?? 0, hour) === 'booked'">Terisi</span>
                            <span v-else-if="getSlotState(activeCourt?.id ?? 0, hour) === 'pending'">Pending</span>
                            <span v-else>Tutup</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- THE NET DIVIDER -->
            <div class="relative my-6 flex items-center justify-center">
                <div class="h-1 w-full bg-white/40 shadow-sm" />
                <div class="absolute flex items-center gap-2 rounded-full border border-white/30 bg-black/85 px-4 py-1 text-[10px] font-black uppercase tracking-[0.25em] text-white shadow-md backdrop-blur">
                    <span class="h-1.5 w-1.5 animate-ping rounded-full bg-emerald-400" />
                    NET · JARING TENGAH
                </div>
            </div>

            <!-- Side B: Afternoon & Evening Slots (15:00 - 22:00) -->
            <div class="mt-4">
                <div class="mb-2 flex items-center justify-between text-xs font-semibold text-emerald-200/80">
                    <span>SISI B · SORE & MALAM (15:00 – 22:00 WIB)</span>
                    <span class="text-[10px] text-white/50">Area Servis Sisi B</span>
                </div>
                <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-4">
                    <button
                        v-for="hour in sideBSlots"
                        :key="hour"
                        type="button"
                        :disabled="getSlotState(activeCourt?.id ?? 0, hour) !== 'available'"
                        @click="selectSlot(hour)"
                        class="group relative flex select-none flex-col items-center justify-between rounded-xl border p-3 text-center transition duration-200"
                        :class="[
                            selectedSlot === hour
                                ? 'ring-4 ring-white bg-emerald-500 border-white text-slate-950 font-bold shadow-xl scale-[1.04] z-10'
                                : getSlotState(activeCourt?.id ?? 0, hour) === 'available'
                                ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300 hover:bg-emerald-500 hover:text-slate-950 hover:border-emerald-400 hover:scale-[1.02] hover:shadow-lg hover:shadow-emerald-500/20 cursor-pointer font-semibold'
                                : getSlotState(activeCourt?.id ?? 0, hour) === 'booked'
                                ? 'bg-rose-500/20 border-rose-500/40 text-rose-300 opacity-75 cursor-not-allowed'
                                : getSlotState(activeCourt?.id ?? 0, hour) === 'pending'
                                ? 'bg-amber-500/20 border-amber-500/40 text-amber-300 opacity-80 cursor-not-allowed'
                                : 'border-slate-800 bg-slate-950/40 text-slate-400 opacity-40 cursor-not-allowed'
                        ]"
                        :title="`${String(hour).padStart(2, '0')}:00 – ${String(hour + 1).padStart(2, '0')}:00 (${getSlotState(activeCourt?.id ?? 0, hour)})`"
                    >
                        <div class="flex items-center gap-1.5 text-xs font-black tracking-tight">
                            <Clock class="h-3.5 w-3.5 opacity-80" />
                            <span>{{ String(hour).padStart(2, '0') }}:00</span>
                        </div>
                        <span class="mt-1 text-[10px] opacity-75">
                            {{ String(hour).padStart(2, '0') }}:00–{{ String(hour + 1).padStart(2, '0') }}:00
                        </span>
                        <div class="mt-2 flex items-center gap-1 text-[10px] font-bold">
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                :class="selectedSlot === hour ? 'bg-slate-950' : getSlotState(activeCourt?.id ?? 0, hour) === 'available' ? 'bg-emerald-400' : getSlotState(activeCourt?.id ?? 0, hour) === 'booked' ? 'bg-rose-400' : getSlotState(activeCourt?.id ?? 0, hour) === 'pending' ? 'bg-amber-400' : 'bg-gray-400'"
                            />
                            <span v-if="selectedSlot === hour">Dipilih</span>
                            <span v-else-if="getSlotState(activeCourt?.id ?? 0, hour) === 'available'">Kosong</span>
                            <span v-else-if="getSlotState(activeCourt?.id ?? 0, hour) === 'booked'">Terisi</span>
                            <span v-else-if="getSlotState(activeCourt?.id ?? 0, hour) === 'pending'">Pending</span>
                            <span v-else>Tutup</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Bottom Glass Wall Indicator -->
            <div class="mt-4 flex items-center justify-between border-t border-emerald-400/20 pt-2 text-[11px] font-bold uppercase tracking-widest text-emerald-300/70">
                <span class="flex items-center gap-1.5">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-cyan-400" />
                    Glass Wall · Dinding Kaca Belakang (3M)
                </span>
                <span class="hidden sm:inline">10M x 20M Standar Internasional FIP</span>
            </div>
        </div>

        <!-- Selected Slot Call-to-Action Bar -->
        <div
            v-if="selectedSlot !== null && activeCourt"
            class="mt-6 flex flex-col gap-4 rounded-2xl border-2 border-emerald-500 bg-emerald-500/15 p-5 backdrop-blur-md sm:flex-row sm:items-center sm:justify-between animate-in fade-in slide-in-from-bottom-2 duration-300"
        >
            <div class="flex items-center gap-3.5">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-green-600 text-slate-950 shadow-md shadow-emerald-500/20">
                    <Check class="h-6 w-6 stroke-[3]" />
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-400">Slot Waktu Dipilih</p>
                    <p class="text-base font-extrabold text-white sm:text-lg">
                        {{ activeCourt.name }} · Pukul {{ String(selectedSlot).padStart(2, '0') }}:00 – {{ String(selectedSlot + 1).padStart(2, '0') }}:00 WIB
                    </p>
                    <p class="text-xs text-slate-300">
                        {{ activeCourt.venue.name }} · {{ activeCourt.venue.city ?? 'Kota' }} · Tarif: <span class="font-extrabold text-emerald-400">{{ fmtPrice(activeCourt.price_per_hour) }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-auto">
                <button
                    type="button"
                    class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2.5 text-xs font-semibold text-slate-300 transition hover:bg-slate-700 hover:text-white"
                    @click="selectedSlot = null"
                >
                    Batal
                </button>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-2.5 text-xs font-bold text-slate-950 shadow-xl shadow-emerald-500/25 transition hover:scale-105"
                    @click="bookSelectedSlot"
                >
                    <span>Booking Slot Ini Sekarang</span>
                    <ChevronRight class="h-4 w-4" />
                </button>
            </div>
        </div>
    </div>
</template>
