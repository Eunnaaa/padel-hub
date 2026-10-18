<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    AlertCircle,
    ArrowLeft,
    Calendar,
    CalendarDays,
    CheckCircle2,
    Clock,
    Layers,
    MapPin,
    Repeat,
    ShieldCheck,
} from '@lucide/vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { formatLocalDate } from '@/lib/utils';

const props = defineProps<{
    courts: Array<{ id: number; name: string; price_per_hour: string; venue: { id: number; name: string; city: string } }>;
}>();

const query = new URLSearchParams(window.location.search);
const venueId = ref<number | ''>('');
const form = useForm({
    court_id: query.get('court_id') ?? '',
    booking_date: query.get('booking_date') ?? '',
    start_time: query.get('start_time') ?? '08:00',
    end_time: query.get('end_time') ?? '09:00',
    recurrence_rule: '',
});

if (form.court_id) {
    const prefilled = props.courts.find((c) => c.id === Number(form.court_id));
    if (prefilled) venueId.value = prefilled.venue.id;
}

const venues = computed(() => {
    const map = new Map<number, { id: number; name: string; city: string }>();
    props.courts.forEach((c) => map.set(c.venue.id, c.venue));
    return Array.from(map.values());
});

const filteredCourts = computed(() =>
    venueId.value === '' ? props.courts : props.courts.filter((c) => c.venue.id === venueId.value),
);

const selectedCourt = computed(() => filteredCourts.value.find((c) => c.id === Number(form.court_id)) ?? null);

const hours = computed(() => {
    const [sh, sm] = form.start_time.split(':').map(Number);
    const [eh, em] = form.end_time.split(':').map(Number);
    if ([sh, sm, eh, em].some(Number.isNaN)) return 0;
    return Math.max((eh + em / 60) - (sh + sm / 60), 0);
});

const total = computed(() => (selectedCourt.value ? Math.round(hours.value * Number(selectedCourt.value.price_per_hour)) : 0));

const fmt = (n: number | string) => Number(n).toLocaleString('id-ID');
const today = formatLocalDate();
const submit = () => form.post(route('bookings.store'));
</script>

<template>
    <Head title="Booking Lapangan Baru — PadelHub" />
    <AuthenticatedLayout :dark="true">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-400">
                        <Calendar class="h-3.5 w-3.5" />
                        <span>RESERVASI LAPANGAN RESMI</span>
                    </div>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">Booking Lapangan Baru</h2>
                    <p class="mt-1 text-sm text-slate-400">Pilih venue favorit, lapangan, dan tentukan jadwal permainanmu.</p>
                </div>
                <Link
                    :href="route('bookings.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-xs font-semibold text-slate-300 transition hover:bg-slate-700 hover:text-white"
                >
                    <ArrowLeft class="h-4 w-4" />
                    <span>Kembali ke Jadwal</span>
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto grid max-w-6xl gap-8 px-4 sm:px-6 lg:grid-cols-5 lg:px-8">
                <!-- FORM COLUMN -->
                <div class="lg:col-span-3">
                    <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md sm:p-8">
                        <form class="space-y-6" @submit.prevent="submit">
                            <!-- Flash error if any -->
                            <div
                                v-if="($page.props.errors as Record<string, string>).error"
                                class="flex items-center gap-3 rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4 text-xs font-semibold text-rose-300"
                            >
                                <AlertCircle class="h-5 w-5 shrink-0 text-rose-400" />
                                <p>{{ ($page.props.errors as Record<string, string>).error }}</p>
                            </div>

                            <!-- Venue Selector -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">
                                    Pilih Venue Padel
                                </label>
                                <div class="relative mt-2">
                                    <MapPin class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-emerald-400" />
                                    <select
                                        id="venue"
                                        v-model="venueId"
                                        class="h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 pl-10 pr-4 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 [&>option]:bg-slate-900 [&>option]:text-white"
                                    >
                                        <option :value="''">Semua Venue</option>
                                        <option v-for="v in venues" :key="v.id" :value="v.id">{{ v.name }} — {{ v.city }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Court Selector -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">
                                    Pilih Lapangan *
                                </label>
                                <div class="relative mt-2">
                                    <Layers class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-emerald-400" />
                                    <select
                                        id="court"
                                        v-model="form.court_id"
                                        required
                                        class="h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 pl-10 pr-4 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 [&>option]:bg-slate-900 [&>option]:text-white"
                                    >
                                        <option value="">Pilih Lapangan</option>
                                        <option v-for="c in filteredCourts" :key="c.id" :value="c.id">
                                            {{ c.venue.name }} · {{ c.name }} — Rp {{ fmt(c.price_per_hour) }}/jam
                                        </option>
                                    </select>
                                </div>
                                <p v-if="form.errors.court_id" class="mt-1.5 text-xs font-semibold text-rose-400">{{ form.errors.court_id }}</p>
                            </div>

                            <!-- Date Picker -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">
                                    Tanggal Bermain *
                                </label>
                                <div class="relative mt-2">
                                    <CalendarDays class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-emerald-400" />
                                    <input
                                        id="date"
                                        v-model="form.booking_date"
                                        type="date"
                                        :min="today"
                                        required
                                        class="h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 pl-10 pr-4 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                    />
                                </div>
                                <p v-if="form.errors.booking_date" class="mt-1.5 text-xs font-semibold text-rose-400">{{ form.errors.booking_date }}</p>
                            </div>

                            <!-- Time Range -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Jam Mulai *</label>
                                    <div class="relative mt-2">
                                        <Clock class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-emerald-400" />
                                        <input
                                            id="start"
                                            v-model="form.start_time"
                                            type="time"
                                            required
                                            class="h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 pl-10 pr-4 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                        />
                                    </div>
                                    <p v-if="form.errors.start_time" class="mt-1.5 text-xs font-semibold text-rose-400">{{ form.errors.start_time }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Jam Selesai *</label>
                                    <div class="relative mt-2">
                                        <Clock class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-emerald-400" />
                                        <input
                                            id="end"
                                            v-model="form.end_time"
                                            type="time"
                                            required
                                            class="h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 pl-10 pr-4 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                        />
                                    </div>
                                    <p v-if="form.errors.end_time" class="mt-1.5 text-xs font-semibold text-rose-400">{{ form.errors.end_time }}</p>
                                </div>
                            </div>

                            <p v-if="hours <= 0 && form.start_time && form.end_time" class="text-xs font-semibold text-rose-400">
                                Waktu selesai harus lebih lambat dari waktu mulai.
                            </p>

                            <!-- Recurring Option -->
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-800 bg-slate-950/50 p-4 transition hover:border-slate-700">
                                <input
                                    v-model="form.recurrence_rule"
                                    type="checkbox"
                                    :true-value="'weekly'"
                                    :false-value="''"
                                    class="mt-1 h-4 w-4 rounded border-slate-700 bg-slate-900 text-emerald-500 focus:ring-emerald-500"
                                />
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <Repeat class="h-3.5 w-3.5 text-emerald-400" />
                                        <span class="text-xs font-bold text-white">Booking Rutin Mingguan</span>
                                    </div>
                                    <span class="mt-0.5 block text-[11px] text-slate-400">
                                        Otomatis dipesan setiap minggu selama 4 pekan berturut-turut pada hari & jam yang sama.
                                    </span>
                                </div>
                            </label>
                            <p v-if="form.errors.recurrence_rule" class="text-xs font-semibold text-rose-400">{{ form.errors.recurrence_rule }}</p>

                            <!-- Submit Button -->
                            <div class="flex items-center gap-4 border-t border-slate-800/80 pt-6">
                                <button
                                    type="submit"
                                    :disabled="form.processing || !form.court_id || !form.booking_date || hours <= 0"
                                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-3 text-xs font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all hover:scale-105 hover:shadow-emerald-500/30 disabled:opacity-50"
                                >
                                    <CheckCircle2 class="h-4 w-4" />
                                    <span>{{ form.processing ? 'Menyimpan Booking…' : 'Lanjutkan ke Pembayaran' }}</span>
                                </button>
                                <Link :href="route('bookings.index')" class="text-xs font-semibold text-slate-400 transition hover:text-white">
                                    Batal
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- SUMMARY COLUMN -->
                <div class="lg:col-span-2">
                    <div class="sticky top-24 space-y-4">
                        <div class="rounded-3xl border border-slate-800/80 bg-slate-900/70 p-6 shadow-2xl backdrop-blur-md">
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Ringkasan Pemesanan</h3>

                            <div v-if="selectedCourt && form.booking_date" class="mt-5 space-y-3 rounded-2xl border border-slate-800 bg-slate-950/60 p-4 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Venue</span>
                                    <span class="font-bold text-white">{{ selectedCourt.venue.name }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Lapangan</span>
                                    <span class="font-bold text-white">{{ selectedCourt.name }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-1 text-slate-400">
                                        <CalendarDays class="h-3.5 w-3.5 text-emerald-400" />
                                        Tanggal
                                    </span>
                                    <span class="font-semibold text-white">{{ form.booking_date }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-1 text-slate-400">
                                        <Clock class="h-3.5 w-3.5 text-emerald-400" />
                                        Durasi
                                    </span>
                                    <span class="font-semibold text-white">{{ hours }} jam ({{ form.start_time }}–{{ form.end_time }})</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Tarif per Jam</span>
                                    <span class="font-semibold text-white">Rp {{ fmt(selectedCourt.price_per_hour) }}</span>
                                </div>
                            </div>
                            <div v-else class="mt-5 rounded-2xl border border-dashed border-slate-800 bg-slate-950/40 p-5 text-center text-xs text-slate-400">
                                <p>Pilih lapangan dan tanggal di formulir untuk melihat estimasi rincian biaya.</p>
                            </div>

                            <div class="mt-6 flex items-center justify-between border-t border-slate-800/80 pt-5">
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Estimasi Total</span>
                                    <p class="text-[10.5px] text-slate-400">Belum termasuk promo</p>
                                </div>
                                <span class="text-2xl font-black text-emerald-400">Rp {{ fmt(total) }}</span>
                            </div>
                        </div>

                        <!-- Perk note -->
                        <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-4 text-xs text-slate-300">
                            <div class="flex items-center gap-2 text-emerald-400">
                                <ShieldCheck class="h-4 w-4" />
                                <span class="font-bold">Garansi Slot 15 Menit</span>
                            </div>
                            <p class="mt-1 text-[11px] text-slate-400">
                                Setelah tombol ditekan, slot lapangan akan dikunci selama 15 menit agar Anda dapat menyelesaikan checkout dengan nyaman.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
