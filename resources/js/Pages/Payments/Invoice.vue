<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ArrowLeft, CalendarDays, CheckCircle2, Clock, MapPin, Printer, QrCode, ReceiptText, ShieldCheck } from '@lucide/vue';
import { Head, Link } from '@inertiajs/vue3';
import QRCode from 'qrcode';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    booking: {
        id: number;
        booking_date: string;
        start_time: string;
        end_time: string;
        total_amount: string;
        payment_status: string;
        status: string;
        court: { name: string; venue: { name: string; address: string; city: string } };
        payment: {
            method: string;
            provider: string;
            status: string;
            transaction_reference: string | null;
            created_at: string;
        } | null;
    };
    qrUrl?: string;
}>();

const fmt = (n: string) => Number(n).toLocaleString('id-ID');
const fmtDate = (d: string) =>
    new Date(`${d}T00:00:00`).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
const fmtDateTime = (d: string) =>
    new Date(d).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const qr = ref<string>('');
const printInvoice = () => {
    window.print();
};

onMounted(async () => {
    if (props.booking.payment_status === 'paid') {
        const qrContent = props.qrUrl || route('checkin.scan', props.booking.id);
        qr.value = await QRCode.toDataURL(qrContent, {
            width: 220,
            margin: 1,
            color: {
                dark: '#020617',
                light: '#FFFFFF',
            },
        });
    }
});
</script>

<template>
    <Head title="Invoice Digital — PadelHub" />
    <AuthenticatedLayout :dark="true">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-400">
                        <ReceiptText class="h-3.5 w-3.5" />
                        <span>BUKTI TRANSAKSI RESMI</span>
                    </div>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">Invoice Pembayaran</h2>
                    <p class="mt-1 text-sm text-slate-400">Struk digital resmi dan QR Pass check-in lapangan.</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <button
                        type="button"
                        @click="printInvoice"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-xs font-bold text-slate-200 transition hover:border-emerald-500/50 hover:bg-slate-700 hover:text-white"
                    >
                        <Printer class="h-4 w-4 text-emerald-400" />
                        <span>Cetak Invoice</span>
                    </button>
                    <Link
                        :href="route('payments.index')"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-xs font-semibold text-slate-300 transition hover:bg-slate-700 hover:text-white"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        <span>Kembali</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <!-- INVOICE RECEIPT CARD -->
                <div class="relative overflow-hidden rounded-3xl border border-slate-800/80 bg-slate-900/90 p-8 shadow-2xl backdrop-blur-xl sm:p-10 print:border-gray-200 print:bg-white print:text-black print:shadow-none">
                    <!-- Ambient Glow Watermark -->
                    <div class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-emerald-500/10 blur-3xl print:hidden" />

                    <!-- Header Struk -->
                    <div class="relative z-10 flex items-start justify-between border-b border-slate-800/80 pb-6 print:border-gray-200">
                        <div class="flex items-center gap-3.5">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-green-600 p-2 shadow-lg shadow-emerald-500/20 text-slate-950">
                                <ApplicationLogo class="h-full w-full" />
                            </div>
                            <div>
                                <h1 class="text-xl font-black tracking-tight text-white print:text-black">
                                    Padel<span class="text-emerald-400">Hub</span>
                                </h1>
                                <p class="font-mono text-xs text-slate-400 print:text-gray-600">
                                    INV-{{ booking.payment?.transaction_reference ?? `BK-${booking.id}` }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <span
                                v-if="booking.payment_status === 'paid'"
                                class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/15 px-3 py-1 text-xs font-bold text-emerald-400 shadow-sm print:border-green-600 print:text-green-700"
                            >
                                <CheckCircle2 class="h-3.5 w-3.5" />
                                <span>LUNAS</span>
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/30 bg-amber-500/15 px-3 py-1 text-xs font-bold text-amber-400"
                            >
                                <span>MENUNGGU</span>
                            </span>
                        </div>
                    </div>

                    <!-- Detail Transaksi Table -->
                    <div class="relative z-10 mt-6 space-y-3.5 text-xs">
                        <div class="flex items-center justify-between text-slate-400 print:text-gray-600">
                            <span>Nama Venue</span>
                            <span class="font-bold text-white print:text-black">{{ booking.court?.venue?.name ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400 print:text-gray-600">
                            <span>Lapangan</span>
                            <span class="font-bold text-white print:text-black">{{ booking.court?.name ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400 print:text-gray-600">
                            <span>Tanggal Bermain</span>
                            <span class="font-bold text-white print:text-black">{{ fmtDate(booking.booking_date) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400 print:text-gray-600">
                            <span>Jam Slot</span>
                            <span class="font-bold text-white print:text-black">{{ booking.start_time }} – {{ booking.end_time }}</span>
                        </div>
                        <div class="flex items-start justify-between text-slate-400 print:text-gray-600">
                            <span>Lokasi Venue</span>
                            <span class="max-w-[260px] text-right font-medium text-slate-300 print:text-black">
                                {{ booking.court?.venue?.address ?? '—' }}<template v-if="booking.court?.venue?.city">, {{ booking.court.venue.city }}</template>
                            </span>
                        </div>
                    </div>

                    <!-- Total & Payment Method -->
                    <div class="relative z-10 mt-6 border-t border-slate-800/80 pt-6 print:border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 print:text-gray-600">Total Dibayar</p>
                                <p v-if="booking.payment" class="mt-0.5 text-[11px] text-slate-400 print:text-gray-600">
                                    {{ booking.payment.method.toUpperCase() }} · {{ booking.payment.provider.toUpperCase() }} · {{ fmtDateTime(booking.payment.created_at) }}
                                </p>
                            </div>
                            <p class="text-2xl font-black text-emerald-400 print:text-green-700">
                                Rp {{ fmt(booking.total_amount) }}
                            </p>
                        </div>
                    </div>

                    <!-- QR Check-in Box -->
                    <div v-if="qr" class="relative z-10 mt-8 flex flex-col items-center gap-3 border-t border-slate-800/80 pt-6 text-center print:border-gray-200">
                        <div class="rounded-2xl border border-slate-700 bg-white p-3 shadow-lg">
                            <img :src="qr" alt="QR Pass Check-in" class="h-44 w-44 object-contain" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white print:text-black">QR Pass Check-in Pertandingan</p>
                            <p class="mt-0.5 text-[11px] text-slate-400 print:text-gray-600">
                                Scan QR ini di meja check-in venue untuk konfirmasi kedatangan instan.
                            </p>
                        </div>
                    </div>

                    <!-- Notice Note -->
                    <div class="relative z-10 mt-6 flex items-center gap-2.5 rounded-xl border border-slate-800 bg-slate-950/60 p-3.5 text-[11px] text-slate-300 print:border-gray-200 print:bg-gray-50 print:text-gray-700">
                        <ShieldCheck class="h-4 w-4 shrink-0 text-emerald-400 print:text-green-700" />
                        <p>Invoice ini adalah bukti pembayaran sah dari PadelHub. Harap hadir 10 menit sebelum jam bermain dimulai.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
