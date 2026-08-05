<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import { CalendarDays, CheckCircle2, ReceiptText } from '@lucide/vue';
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
}>();

const fmt = (n: string) => Number(n).toLocaleString('id-ID');
const fmtDate = (d: string) => new Date(`${d}T00:00:00`).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
const fmtDateTime = (d: string) => new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

const qr = ref<string>('');
onMounted(async () => {
    if (props.booking.payment_status === 'paid') {
        qr.value = await QRCode.toDataURL(route('checkin.scan', props.booking.id), { width: 220, margin: 1 });
    }
});
</script>

<template>
    <Head title="Invoice" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-foreground">Invoice</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Struk digital booking kamu.</p>
                </div>
                <Button as="link" :href="route('payments.index')" variant="outline">Kembali</Button>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-8">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                                    <ReceiptText class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-lg font-extrabold text-foreground">PadelHub</p>
                                    <p class="text-xs text-muted-foreground">Invoice {{ booking.payment?.transaction_reference ?? `BK-${booking.id}` }}</p>
                                </div>
                            </div>
                            <div v-if="booking.payment_status === 'paid'" class="flex items-center gap-1 text-sm font-semibold text-accent">
                                <CheckCircle2 class="h-4 w-4" />
                                Lunas
                            </div>
                            <Badge v-else variant="warning">{{ booking.payment_status }}</Badge>
                        </div>

                        <div class="mt-8 space-y-4 border-t border-border pt-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Venue</span>
                                <span class="font-semibold text-foreground">{{ booking.court.venue.name }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Lapangan</span>
                                <span class="font-semibold text-foreground">{{ booking.court.name }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Tanggal</span>
                                <span class="font-semibold text-foreground">{{ fmtDate(booking.booking_date) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Jam</span>
                                <span class="font-semibold text-foreground">{{ booking.start_time }}–{{ booking.end_time }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Alamat</span>
                                <span class="text-right font-semibold text-foreground">{{ booking.court.venue.address }}, {{ booking.court.venue.city }}</span>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-between border-t border-border pt-5">
                            <div>
                                <p class="text-sm font-medium text-foreground">Total</p>
                                <p v-if="booking.payment" class="text-xs text-muted-foreground">
                                    {{ booking.payment.method }} · {{ booking.payment.provider }} · {{ fmtDateTime(booking.payment.created_at) }}
                                </p>
                            </div>
                            <p class="text-3xl font-extrabold text-accent">Rp {{ fmt(booking.total_amount) }}</p>
                        </div>

                        <div v-if="qr" class="mt-6 flex flex-col items-center gap-3 border-t border-border pt-6">
                            <img :src="qr" alt="QR check-in" class="rounded-lg bg-white p-2 ring-1 ring-border" />
                            <p class="text-center text-xs text-muted-foreground">
                                Scan QR ini di venue dashboard untuk check-in.<br />
                                Berlaku setelah pembayaran dikonfirmasi.
                            </p>
                        </div>

                        <div class="mt-6 flex items-center gap-2 rounded-xl bg-muted p-4 text-xs text-muted-foreground">
                            <CalendarDays class="h-4 w-4 shrink-0 text-accent" />
                            <p>Tunjukkan invoice ini (atau QR di atas) ke staf venue saat check-in.</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
