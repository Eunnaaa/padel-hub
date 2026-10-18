<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ArrowLeft, Calendar, CalendarDays, CheckCircle2, Clock, Layers } from '@lucide/vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    booking: { id: number; court_id: number; booking_date: string; start_time: string; end_time: string };
    courts: Array<{ id: number; name: string; price_per_hour: string; venue: { name: string } }>;
}>();

const form = useForm({
    court_id: String(props.booking.court_id),
    booking_date: props.booking.booking_date.slice(0, 10),
    start_time: props.booking.start_time.slice(0, 5),
    end_time: props.booking.end_time.slice(0, 5),
});

const err = (k: string) => (form.errors as Record<string, string>)[k];
const submit = () => form.put(route('bookings.update', props.booking.id));
</script>

<template>
    <Head title="Edit Booking — PadelHub" />
    <AuthenticatedLayout :dark="true">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-400">
                        <Calendar class="h-3.5 w-3.5" />
                        <span>PERUBAHAN JADWAL</span>
                    </div>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">Ubah Jadwal Booking</h2>
                    <p class="mt-1 text-sm text-slate-400">Sesuaikan tanggal atau jam bermain untuk pemesanan yang berstatus pending.</p>
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
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md sm:p-8">
                    <form class="space-y-6" @submit.prevent="submit">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">
                                Lapangan *
                            </label>
                            <div class="relative mt-2">
                                <Layers class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-emerald-400" />
                                <select
                                    id="court"
                                    v-model="form.court_id"
                                    required
                                    class="h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 pl-10 pr-4 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 [&>option]:bg-slate-900 [&>option]:text-white"
                                >
                                    <option v-for="c in courts" :key="c.id" :value="c.id">
                                        {{ c.venue.name }} — {{ c.name }} (Rp {{ Number(c.price_per_hour).toLocaleString('id-ID') }}/jam)
                                    </option>
                                </select>
                            </div>
                        </div>

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
                                    required
                                    class="h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 pl-10 pr-4 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                />
                            </div>
                        </div>

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
                            </div>
                        </div>

                        <p v-if="err('start_time')" class="text-xs font-semibold text-rose-400">{{ err('start_time') }}</p>

                        <div class="flex items-center gap-4 border-t border-slate-800/80 pt-6">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-3 text-xs font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all hover:scale-105 hover:shadow-emerald-500/30 disabled:opacity-50"
                            >
                                <CheckCircle2 class="h-4 w-4" />
                                <span>{{ form.processing ? 'Menyimpan Perubahan…' : 'Simpan Perubahan' }}</span>
                            </button>
                            <Link :href="route('bookings.index')" class="text-xs font-semibold text-slate-400 transition hover:text-white">
                                Batal
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
