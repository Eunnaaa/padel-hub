<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    AlertCircle,
    ArrowLeft,
    CalendarDays,
    CheckCircle2,
    Clock,
    CreditCard,
    Info,
    RotateCcw,
    ShieldCheck,
    Sparkles,
    Tag,
    Trash2,
} from '@lucide/vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

declare global {
    interface Window {
        snap?: {
            pay: (token: string, options?: { onSuccess: () => void; onPending: () => void; onError: () => void }) => void;
        };
    }
}

const props = defineProps<{
    booking: {
        id: number;
        total_amount: string;
        discount_amount?: string | null;
        booking_date: string;
        start_time: string;
        end_time: string;
        status: string;
        locked_until?: string | null;
        court: { name: string; price_per_hour: string; venue: { name: string; slug?: string } };
        promo?: { id: number; code: string; title: string; discount_type: string; discount_value: string } | null;
    };
    snapToken?: string | null;
    midtransClientKey?: string;
    isProduction?: boolean;
}>();

const page = usePage();
const form = useForm({ method: 'qris' });
const promoForm = useForm({ code: '' });
const isMidtrans = ref(!!props.snapToken);

// Countdown timer
const remainingSeconds = ref<number>(0);
const isExpired = ref<boolean>(props.booking.status === 'expired');

const updateCountdown = () => {
    if (props.booking.status === 'expired') {
        isExpired.value = true;
        remainingSeconds.value = 0;
        return;
    }
    if (!props.booking.locked_until) {
        remainingSeconds.value = 0;
        return;
    }
    const diff = Math.floor((new Date(props.booking.locked_until).getTime() - Date.now()) / 1000);
    if (diff <= 0) {
        remainingSeconds.value = 0;
        isExpired.value = true;
    } else {
        remainingSeconds.value = diff;
        isExpired.value = false;
    }
};

const formattedTime = computed(() => {
    const m = Math.floor(remainingSeconds.value / 60);
    const s = remainingSeconds.value % 60;
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
});

let timerInterval: ReturnType<typeof setInterval> | null = null;

const subtotal = computed(() => {
    const total = Number(props.booking.total_amount) || 0;
    const discountVal = Number(props.booking.discount_amount) || 0;
    return total + discountVal;
});

const discount = computed(() => Number(props.booking.discount_amount) || 0);
const finalTotal = computed(() => Number(props.booking.total_amount) || 0);

const submit = () => {
    if (isExpired.value) return;
    form.post(route('payments.store', props.booking.id));
};

const applyPromo = () => {
    if (isExpired.value || !promoForm.code.trim()) return;
    promoForm.post(route('payments.apply-promo', props.booking.id), {
        preserveScroll: true,
        onSuccess: () => promoForm.reset(),
    });
};

const removePromo = () => {
    if (isExpired.value) return;
    router.delete(route('payments.remove-promo', props.booking.id), {
        preserveScroll: true,
    });
};

const loadSnap = () => {
    if (window.snap) {
        openSnap();
        return;
    }
    if (!props.snapToken || !props.midtransClientKey) return;
    const script = document.createElement('script');
    script.src = props.isProduction
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
    script.setAttribute('data-client-key', props.midtransClientKey);
    script.onload = () => openSnap();
    document.body.appendChild(script);
};

const openSnap = () => {
    if (!props.snapToken || !window.snap) return;
    window.snap.pay(props.snapToken, {
        onSuccess: () => router.visit(route('bookings.index')),
        onPending: () => router.visit(route('bookings.index')),
        onError: () => router.visit(route('bookings.index')),
    });
};

onMounted(() => {
    updateCountdown();
    if (remainingSeconds.value > 0) {
        timerInterval = setInterval(updateCountdown, 1000);
    }
    loadSnap();
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

const fmt = (n: number | string) => Number(n).toLocaleString('id-ID');
const fmtDate = (d: string) =>
    new Date(`${d}T00:00:00`).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
</script>

<template>
    <Head title="Checkout & Pembayaran — PadelHub" />
    <AuthenticatedLayout :dark="true">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-400">
                        <CreditCard class="h-3.5 w-3.5" />
                        <span>CHECKOUT RESMI PADELHUB</span>
                    </div>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">Checkout & Pembayaran</h2>
                    <p class="mt-1 text-sm text-slate-400">Selesaikan pembayaran untuk mengonfirmasi jadwal booking lapanganmu.</p>
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
            <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Lock Timer Banner -->
                <div
                    v-if="!isExpired && remainingSeconds > 0"
                    class="relative overflow-hidden rounded-2xl border p-4 shadow-xl backdrop-blur-md transition-all"
                    :class="remainingSeconds <= 60
                        ? 'border-rose-500/40 bg-rose-500/15 text-rose-300 animate-pulse'
                        : 'border-amber-500/30 bg-amber-500/10 text-amber-300'"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" :class="remainingSeconds <= 60 ? 'bg-rose-500/20 text-rose-400' : 'bg-amber-500/20 text-amber-400'">
                                <Clock class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-white">Slot Lapangan Dikunci Sementara</p>
                                <p class="text-xs text-slate-300">Selesaikan transaksi sebelum hitung mundur berakhir agar slot tidak dilepas.</p>
                            </div>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/90 px-3.5 py-1.5 font-mono text-lg font-black tracking-widest text-emerald-400 shadow-inner">
                            {{ formattedTime }}
                        </div>
                    </div>
                </div>

                <!-- Expired Banner -->
                <div
                    v-else-if="isExpired"
                    class="rounded-2xl border border-rose-500/40 bg-rose-500/10 p-6 text-rose-300 shadow-xl backdrop-blur-md"
                >
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-500/20 text-rose-400">
                            <AlertCircle class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-base font-extrabold text-white">Waktu Booking Telah Kedaluwarsa</p>
                            <p class="mt-1 text-xs text-slate-300">
                                Batas waktu reservasi telah berakhir dan slot lapangan ini telah dibuka kembali untuk pemain lain.
                            </p>
                            <div class="mt-4">
                                <Link
                                    :href="route('bookings.create')"
                                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-4 py-2 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition hover:scale-105"
                                >
                                    <RotateCcw class="h-4 w-4" />
                                    <span>Pesan Lapangan Lagi</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Flash Error Banner -->
                <div
                    v-if="page.props.errors && (page.props.errors as Record<string, string>).error"
                    class="flex items-center gap-3 rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4 text-xs font-semibold text-rose-300"
                >
                    <AlertCircle class="h-5 w-5 shrink-0 text-rose-400" />
                    <p>{{ (page.props.errors as Record<string, string>).error }}</p>
                </div>

                <!-- Booking Details & Price Breakdown Card -->
                <div class="overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 text-emerald-400 ring-1 ring-emerald-500/30">
                            <CalendarDays class="h-6 w-6" />
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-black text-white">{{ booking.court.venue.name }}</h3>
                                <span class="rounded-md border border-slate-700 bg-slate-800 px-2 py-0.5 text-xs font-semibold text-slate-300">
                                    {{ booking.court.name }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ fmtDate(booking.booking_date) }} · <span class="font-bold text-white">{{ booking.start_time }} – {{ booking.end_time }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Price Breakdown Table -->
                    <div class="mt-6 space-y-2.5 border-t border-slate-800/80 pt-5 text-xs">
                        <div class="flex justify-between text-slate-300">
                            <span>Tarif Lapangan</span>
                            <span class="font-semibold text-white">Rp {{ fmt(subtotal) }}</span>
                        </div>

                        <div v-if="discount > 0" class="flex justify-between text-emerald-400">
                            <span class="flex items-center gap-1.5 font-bold">
                                <Tag class="h-3.5 w-3.5" />
                                Diskon Voucher Promo
                            </span>
                            <span class="font-black">- Rp {{ fmt(discount) }}</span>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-800/80 pt-4">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Pembayaran</span>
                                <p class="text-[11px] text-slate-400">Sudah termasuk pajak & service fee</p>
                            </div>
                            <p class="text-2xl font-black text-emerald-400">
                                Rp {{ fmt(finalTotal) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Promo Voucher Card -->
                <div v-if="!isExpired" class="rounded-2xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md">
                    <div class="flex items-center gap-2 text-white">
                        <Sparkles class="h-4 w-4 text-emerald-400" />
                        <h3 class="text-sm font-black uppercase tracking-wider">Kode Promo / Voucher</h3>
                    </div>

                    <!-- Applied Promo State -->
                    <div
                        v-if="booking.promo"
                        class="mt-3 flex items-center justify-between rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3.5"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400">
                                <Tag class="h-4 w-4" />
                            </div>
                            <div>
                                <p class="font-mono text-sm font-black tracking-wider text-emerald-300">{{ booking.promo.code }}</p>
                                <p class="text-xs text-slate-300">{{ booking.promo.title }}</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="removePromo"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-1.5 text-xs font-bold text-rose-300 transition hover:bg-rose-500/20 hover:text-white"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                            <span>Hapus</span>
                        </button>
                    </div>

                    <!-- Apply Promo Form -->
                    <form v-else class="mt-3 flex gap-2" @submit.prevent="applyPromo">
                        <input
                            v-model="promoForm.code"
                            type="text"
                            placeholder="Ketik kode promo (misal: PADELBARU)"
                            class="h-10 flex-1 rounded-xl border border-slate-800 bg-slate-950/80 px-3.5 text-xs uppercase text-white placeholder-slate-400 shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            :disabled="promoForm.processing"
                        />
                        <button
                            type="submit"
                            :disabled="promoForm.processing || !promoForm.code.trim()"
                            class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-bold text-white transition hover:border-emerald-500/50 hover:bg-slate-700 disabled:opacity-50"
                        >
                            {{ promoForm.processing ? 'Memeriksa…' : 'Terapkan' }}
                        </button>
                    </form>

                    <p
                        v-if="promoForm.errors.code || (page.props.errors as Record<string, string>).promo_code"
                        class="mt-2 text-xs font-semibold text-rose-400"
                    >
                        {{ promoForm.errors.code || (page.props.errors as Record<string, string>).promo_code }}
                    </p>
                </div>

                <!-- Payment Method Card -->
                <div v-if="!isExpired" class="rounded-2xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md">
                    <form v-if="!isMidtrans" class="space-y-5" @submit.prevent="submit">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Metode Pembayaran</label>
                            <select
                                id="method"
                                v-model="form.method"
                                class="mt-2 flex h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 [&>option]:bg-slate-900 [&>option]:text-white"
                            >
                                <option value="qris">QRIS (GoPay, OVO, Dana, ShopeePay, BCA Mobile)</option>
                                <option value="bank_transfer">Virtual Account / Bank Transfer</option>
                                <option value="credit_card">Kartu Kredit / Debit Online</option>
                                <option value="e_wallet">E-Wallet Langsung</option>
                                <option value="cash">Bayar di Tempat (Kasir Venue)</option>
                            </select>
                        </div>

                        <div class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/60 p-3.5 text-xs text-slate-300">
                            <ShieldCheck class="mt-0.5 h-4 w-4 shrink-0 text-emerald-400" />
                            <p>Pembayaran diproses secara instan dan aman. Invoice digital serta QR Check-in akan otomatis terbit setelah transaksi berhasil.</p>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing || isExpired"
                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-3 text-xs font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all hover:scale-105 hover:shadow-emerald-500/30 disabled:opacity-50"
                            >
                                <CheckCircle2 class="h-4 w-4" />
                                <span>{{ form.processing ? 'Memproses Transaksi…' : 'Konfirmasi & Bayar Sekarang' }}</span>
                            </button>
                        </div>
                    </form>

                    <div v-else class="space-y-4">
                        <div class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/60 p-3.5 text-xs text-slate-300">
                            <Info class="mt-0.5 h-4 w-4 shrink-0 text-emerald-400" />
                            <p>Jendela pembayaran resmi Midtrans Snap akan terbuka. Selesaikan pembayaran sebelum batas waktu berakhir.</p>
                        </div>
                        <div class="flex items-center gap-3 pt-2">
                            <button
                                type="button"
                                :disabled="isExpired"
                                @click="loadSnap"
                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-3 text-xs font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all hover:scale-105"
                            >
                                <CreditCard class="h-4 w-4" />
                                <span>Buka Pembayaran Snap</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
