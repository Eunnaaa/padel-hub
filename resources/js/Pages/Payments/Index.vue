<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import { Banknote, FileText } from '@lucide/vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    payments: Array<{
        id: number;
        amount: string;
        method: string;
        provider: string;
        status: string;
        transaction_reference: string | null;
        created_at: string;
        booking: {
            id: number;
            booking_date: string;
            start_time: string;
            end_time: string;
            payment_status: string;
            court: { name: string; venue: { name: string } };
        };
    }>;
}>();

const fmt = (n: string) => Number(n).toLocaleString('id-ID');
const fmtDateTime = (d: string) => new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <Head title="Riwayat pembayaran" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-foreground">Riwayat pembayaran</h2>
                <p class="mt-1 text-sm text-muted-foreground">Semua transaksi dan invoice kamu.</p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-4xl space-y-4 px-4 sm:px-6 lg:px-8">
                <Card v-for="p in payments" :key="p.id" class="transition hover:shadow-md">
                    <CardContent class="p-6">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-accent/10 text-accent">
                                    <Banknote class="h-6 w-6" />
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-foreground">{{ p.booking.court.venue.name }} — {{ p.booking.court.name }}</p>
                                    <p class="mt-0.5 text-sm text-muted-foreground">
                                        {{ fmtDateTime(p.created_at) }} · {{ p.booking.booking_date }} {{ p.booking.start_time }}–{{ p.booking.end_time }}
                                    </p>
                                    <p class="mt-0.5 font-mono text-xs text-muted-foreground">{{ p.transaction_reference }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <Badge :variant="p.status === 'paid' ? 'success' : 'warning'">{{ p.status }}</Badge>
                                <span class="text-lg font-extrabold text-foreground">Rp {{ fmt(p.amount) }}</span>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-border pt-4">
                            <span class="text-xs capitalize text-muted-foreground">{{ p.method }} · {{ p.provider }}</span>
                            <Button as="link" :href="route('payments.invoice', p.booking.id)" variant="outline" size="sm">
                                <FileText class="h-4 w-4" />
                                Invoice
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="!payments.length" class="border-dashed">
                    <CardContent class="flex flex-col items-center py-16 text-center">
                        <p class="font-semibold text-foreground">Belum ada transaksi</p>
                        <p class="mt-1 text-sm text-muted-foreground">Pembayaran yang sudah kamu lakukan akan tampil di sini.</p>
                        <Button as="link" :href="route('bookings.create')" variant="accent" class="mt-6">Booking lapangan</Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
