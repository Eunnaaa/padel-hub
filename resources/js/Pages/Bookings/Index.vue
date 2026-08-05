<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import { CalendarDays, MapPin, Plus, XCircle } from '@lucide/vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    bookings: Array<{
        id: number;
        booking_date: string;
        start_time: string;
        end_time: string;
        status: string;
        payment_status: string;
        total_amount: string;
        recurrence_rule: string | null;
        recurring_group_id: string | null;
        court: { name: string; venue: { name: string; city: string } };
    }>;
}>();

const statusVariant = (s: string): 'warning' | 'success' | 'info' | 'muted' | 'destructive' =>
    ({ pending: 'warning', confirmed: 'success', checked_in: 'info', finished: 'muted', cancelled: 'destructive', expired: 'muted' } as Record<string, 'warning' | 'success' | 'info' | 'muted' | 'destructive'>)[s] ?? 'muted';

const paymentVariant = (s: string): 'success' | 'warning' | 'destructive' | 'muted' =>
    ({ paid: 'success', pending: 'warning', failed: 'destructive' } as Record<string, 'success' | 'warning' | 'destructive' | 'muted'>)[s] ?? 'muted';

const fmtDate = (d: string) =>
    new Date(`${d}T00:00:00`).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

const fmt = (n: string) => Number(n).toLocaleString('id-ID');
const isActive = (b: { status: string }) => !['cancelled', 'finished', 'expired'].includes(b.status);
const needsPayment = (b: { status: string; payment_status: string }) => b.status === 'pending' && b.payment_status !== 'paid';

const dialog = ref<{ open: boolean; id: number; label: string; cancelSeries: boolean }>({ open: false, id: 0, label: '', cancelSeries: false });
const askCancel = (b: (typeof props.bookings)[number], cancelSeries: boolean) => {
    dialog.value = { open: true, id: b.id, label: `${b.court.venue.name} — ${b.court.name} · ${fmtDate(b.booking_date)}`, cancelSeries };
};
const confirmCancel = () => {
    router.delete(route('bookings.destroy', dialog.value.id), {
        data: { cancel_series: dialog.value.cancelSeries },
    });
    dialog.value.open = false;
};
</script>

<template>
    <Head title="Bookings" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-foreground">Bookings</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Semua pemesanan lapanganmu.</p>
                </div>
                <Button as="link" :href="route('bookings.create')" variant="accent">
                    <Plus class="h-4 w-4" />
                    Booking baru
                </Button>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-5xl space-y-4 px-4 sm:px-6 lg:px-8">
                <Card v-for="b in bookings" :key="b.id" class="transition hover:shadow-md">
                    <CardContent class="p-6">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                                    <CalendarDays class="h-7 w-7" />
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-foreground">{{ b.court.venue.name }} — {{ b.court.name }}</p>
                                    <p class="flex items-center gap-1 text-sm text-muted-foreground">
                                        <MapPin class="h-3.5 w-3.5" />
                                        {{ b.court.venue.city }}
                                    </p>
                                    <p class="mt-1 text-sm text-muted-foreground">
                                        {{ fmtDate(b.booking_date) }} · {{ b.start_time }}–{{ b.end_time }}
                                        <span v-if="b.recurrence_rule" class="ml-1 rounded bg-accent/10 px-1.5 py-0.5 text-xs font-medium text-accent">recurring</span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <Badge :variant="statusVariant(b.status)">{{ b.status }}</Badge>
                                <Badge :variant="paymentVariant(b.payment_status)">{{ b.payment_status }}</Badge>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-border pt-4">
                            <p class="text-lg font-extrabold text-foreground">Rp {{ fmt(b.total_amount) }}</p>
                            <div class="flex flex-wrap gap-2">
                                <Button
                                    v-if="needsPayment(b)"
                                    as="link"
                                    :href="route('payments.create', b.id)"
                                    variant="accent"
                                >
                                    Bayar
                                </Button>
                                <Button
                                    v-if="b.payment_status === 'paid'"
                                    as="link"
                                    :href="route('payments.invoice', b.id)"
                                    variant="outline"
                                >
                                    Invoice
                                </Button>
                                <Button
                                    v-if="b.status === 'pending'"
                                    as="link"
                                    :href="route('bookings.edit', b.id)"
                                    variant="outline"
                                >
                                    Ubah
                                </Button>
                                <Button
                                    v-if="isActive(b)"
                                    variant="outline"
                                    class="border-destructive/30 text-destructive hover:bg-destructive/5"
                                    @click="askCancel(b, false)"
                                >
                                    <XCircle class="h-4 w-4" />
                                    Batalkan
                                </Button>
                                <Button
                                    v-if="isActive(b) && b.recurring_group_id"
                                    variant="outline"
                                    class="border-destructive/30 text-destructive hover:bg-destructive/5"
                                    @click="askCancel(b, true)"
                                >
                                    <XCircle class="h-4 w-4" />
                                    Batalkan seri
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="!bookings.length" class="border-dashed">
                    <CardContent class="flex flex-col items-center py-16 text-center">
                        <p class="font-semibold text-foreground">Belum ada booking</p>
                        <p class="mt-1 text-sm text-muted-foreground">Mulai booking pertama kamu sekarang.</p>
                        <Button as="link" :href="route('bookings.create')" variant="accent" class="mt-6">
                            <Plus class="h-4 w-4" />
                            Booking baru
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>

        <ConfirmDialog
            v-model:open="dialog.open"
            title="Batalkan booking"
            :description="dialog.cancelSeries
                ? `Batalkan seluruh seri mingguan yang tersisa dari ${dialog.label}?`
                : `Yakin ingin membatalkan ${dialog.label}?`"
            confirm-label="Batalkan"
            destructive
            @confirm="confirmCancel"
        />
    </AuthenticatedLayout>
</template>
