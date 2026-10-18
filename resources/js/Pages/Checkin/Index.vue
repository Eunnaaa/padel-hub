<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import { CalendarCheck, CheckCircle2, Clock, QrCode, User, XCircle } from '@lucide/vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    bookings: Array<{
        id: number;
        start_time: string;
        end_time: string;
        status: string;
        payment_status: string;
        court: { name: string; venue: { name: string } };
        user: { name: string; email: string };
        payment: { transaction_reference: string } | null;
    }>;
}>();

const statusVariant = (s: string): 'warning' | 'success' | 'info' | 'muted' | 'destructive' =>
    ({ pending: 'warning', confirmed: 'success', checked_in: 'info', finished: 'muted', cancelled: 'destructive' } as Record<string, 'warning' | 'success' | 'info' | 'muted' | 'destructive'>)[s] ?? 'muted';

const dialog = ref<{ open: boolean; id: number; status: string; label: string }>({ open: false, id: 0, status: '', label: '' });

const setStatus = (b: (typeof props.bookings)[number], s: string, label: string) => {
    dialog.value = { open: true, id: b.id, status: s, label };
};

const confirmStatus = () => {
    router.patch(route('checkin.update-status', dialog.value.id), { status: dialog.value.status });
    dialog.value.open = false;
};
</script>

<template>
    <Head title="Check-in Pemain — PadelHub" />
    <AuthenticatedLayout :dark="true">
        <template #header>
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-400">
                    <QrCode class="h-3.5 w-3.5" />
                    <span>CHECK-IN & VERIFIKASI PEMAIN</span>
                </div>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">Check-in Hari Ini</h2>
                <p class="mt-1 text-sm text-slate-400">Verifikasi pemain, perbarui status pertandingan, dan pantau penggunaan lapangan.</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-4 px-4 sm:px-6 lg:px-8">
                <div
                    v-for="b in bookings"
                    :key="b.id"
                    class="group relative overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-xl backdrop-blur-md transition-all hover:border-emerald-500/40 hover:shadow-2xl hover:shadow-emerald-950/20"
                >
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 text-emerald-400 ring-1 ring-emerald-500/30">
                                <QrCode class="h-7 w-7" />
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-base font-extrabold text-white">{{ b.court.venue.name }} — {{ b.court.name }}</p>
                                <p class="mt-0.5 flex items-center gap-1 text-xs font-bold text-emerald-400">
                                    <Clock class="h-3.5 w-3.5" />
                                    {{ b.start_time }} – {{ b.end_time }}
                                </p>
                                <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-400">
                                    <User class="h-3.5 w-3.5 text-slate-400" />
                                    <span class="font-semibold text-white">{{ b.user.name }}</span>
                                    <span v-if="b.payment" class="ml-2 rounded-md border border-slate-700 bg-slate-800/80 px-2 py-0.5 font-mono text-[10.5px] text-slate-300">
                                        {{ b.payment.transaction_reference }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Badge :variant="statusVariant(b.status)">{{ b.status }}</Badge>
                            <span
                                v-if="b.payment_status === 'paid'"
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

                    <div v-if="b.payment_status === 'paid'" class="mt-5 flex flex-wrap items-center gap-2.5 border-t border-slate-800/80 pt-4">
                        <button
                            v-if="b.status === 'confirmed'"
                            type="button"
                            @click="setStatus(b, 'checked_in', 'Konfirmasi check-in pemain?')"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-4 py-2 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition hover:scale-105"
                        >
                            <CheckCircle2 class="h-3.5 w-3.5" />
                            <span>Check-in Pemain</span>
                        </button>
                        <button
                            v-if="b.status === 'checked_in'"
                            type="button"
                            @click="setStatus(b, 'finished', 'Tandai sesi permainan telah selesai?')"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-bold text-slate-200 transition hover:bg-slate-700 hover:text-white"
                        >
                            <CalendarCheck class="h-3.5 w-3.5 text-emerald-400" />
                            <span>Tandai Selesai</span>
                        </button>
                        <button
                            v-if="b.status === 'confirmed'"
                            type="button"
                            @click="setStatus(b, 'no_show', 'Tandai pemain sebagai no-show?')"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-2 text-xs font-semibold text-rose-300 transition hover:bg-rose-500/20 hover:text-white"
                        >
                            <XCircle class="h-3.5 w-3.5" />
                            <span>No Show</span>
                        </button>
                    </div>
                    <p v-else class="mt-4 border-t border-slate-800/80 pt-4 text-xs text-amber-400">
                        ⚠️ Belum dibayar — tunggu konfirmasi pembayaran sebelum melakukan check-in pemain.
                    </p>
                </div>

                <div v-if="!bookings.length" class="rounded-3xl border border-dashed border-slate-800 bg-slate-900/40 py-16 text-center">
                    <p class="font-bold text-white">Tidak ada booking untuk hari ini</p>
                    <p class="mt-1 text-xs text-slate-400">Semua lapangan kosong atau belum memiliki reservasi aktif hari ini.</p>
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
