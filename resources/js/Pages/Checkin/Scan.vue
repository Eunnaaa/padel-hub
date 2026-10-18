<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import { ArrowLeft, Calendar, CheckCircle2, Clock, QrCode, User, XCircle } from '@lucide/vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    booking: {
        id: number;
        booking_date: string;
        start_time: string;
        end_time: string;
        status: string;
        payment_status: string;
        user: { name: string };
        court: { name: string; venue: { name: string } };
        payment: { transaction_reference: string } | null;
    };
}>();

const statusVariant = (s: string): 'warning' | 'success' | 'info' | 'muted' | 'destructive' =>
    ({ pending: 'warning', confirmed: 'success', checked_in: 'info', finished: 'muted', cancelled: 'destructive', no_show: 'muted' } as Record<string, 'warning' | 'success' | 'info' | 'muted' | 'destructive'>)[s] ?? 'muted';

const dialog = ref<{ open: boolean; status: string; label: string }>({ open: false, status: '', label: '' });

const setStatus = (status: string, label: string) => {
    dialog.value = { open: true, status, label };
};

const confirmStatus = () => {
    router.patch(route('checkin.update', props.booking.id), { status: dialog.value.status });
    dialog.value.open = false;
};
</script>

<template>
    <Head :title="`Hasil Scan QR: ${booking.court.name} — PadelHub`" />
    <AuthenticatedLayout :dark="true">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-400">
                        <QrCode class="h-3.5 w-3.5" />
                        <span>HASIL SCAN TIKET DIGITAL</span>
                    </div>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">Verifikasi Check-in Pemain</h2>
                    <p class="mt-1 text-sm text-slate-400">Pemeriksaan validitas QR pass dan konfirmasi kehadiran pemain di lapangan.</p>
                </div>
                <Link
                    :href="route('checkin.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-xs font-semibold text-slate-300 transition hover:bg-slate-700 hover:text-white"
                >
                    <ArrowLeft class="h-4 w-4" />
                    <span>Daftar Check-in</span>
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md sm:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 text-emerald-400 ring-1 ring-emerald-500/30">
                                <QrCode class="h-8 w-8" />
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-lg font-black text-white">{{ booking.court.venue.name }} — {{ booking.court.name }}</p>
                                <p class="mt-1 flex items-center gap-1.5 text-xs font-bold text-emerald-400">
                                    <Clock class="h-3.5 w-3.5" />
                                    {{ booking.start_time }} – {{ booking.end_time }} WIB
                                </p>
                                <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-400">
                                    <Calendar class="h-3.5 w-3.5 text-slate-500" />
                                    <span>{{ booking.booking_date }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-2">
                            <Badge :variant="statusVariant(booking.status)">{{ booking.status }}</Badge>
                            <span
                                v-if="booking.payment_status === 'paid'"
                                class="inline-flex items-center rounded-md border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-bold text-emerald-400"
                            >
                                Lunas
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center rounded-md border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-xs font-bold text-amber-400"
                            >
                                Belum Bayar
                            </span>
                        </div>
                    </div>

                    <!-- Player & Transaction detail box -->
                    <div class="mt-6 rounded-2xl border border-slate-800 bg-slate-950/60 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-800 text-slate-300">
                                    <User class="h-4 w-4" />
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Nama Pemain</p>
                                    <p class="text-sm font-bold text-white">{{ booking.user.name }}</p>
                                </div>
                            </div>
                            <div v-if="booking.payment" class="text-right">
                                <p class="text-xs text-slate-400">No. Referensi</p>
                                <p class="font-mono text-xs font-semibold text-emerald-400">{{ booking.payment.transaction_reference }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons based on status -->
                    <div v-if="booking.status === 'confirmed'" class="mt-6 flex flex-wrap items-center gap-3 border-t border-slate-800/80 pt-6">
                        <button
                            type="button"
                            @click="setStatus('checked_in', 'Konfirmasi check-in pemain?')"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-2.5 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition hover:scale-105"
                        >
                            <CheckCircle2 class="h-4 w-4" />
                            <span>Check-in Pemain</span>
                        </button>
                        <button
                            type="button"
                            @click="setStatus('no_show', 'Tandai pemain sebagai no-show?')"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-2.5 text-xs font-semibold text-rose-300 transition hover:bg-rose-500/20 hover:text-white"
                        >
                            <XCircle class="h-3.5 w-3.5" />
                            <span>Tandai No-Show</span>
                        </button>
                    </div>

                    <div v-else-if="booking.status === 'checked_in'" class="mt-6 border-t border-slate-800/80 pt-6">
                        <div class="flex items-center gap-2 text-xs font-semibold text-emerald-400">
                            <CheckCircle2 class="h-4 w-4" />
                            <span>Pemain telah berhasil check-in di lapangan.</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">
                            Status sesi dapat ditandai selesai melalui menu Check-in Hari Ini.
                        </p>
                    </div>

                    <div v-else class="mt-6 border-t border-slate-800/80 pt-6">
                        <p class="text-xs text-slate-400">
                            Booking ini tidak dalam status siap check-in (Status saat ini: <span class="font-bold text-white">{{ booking.status }}</span>).
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDialog
            v-model:open="dialog.open"
            title="Update Status Check-in"
            :description="dialog.label"
            @confirm="confirmStatus"
        />
    </AuthenticatedLayout>
</template>
