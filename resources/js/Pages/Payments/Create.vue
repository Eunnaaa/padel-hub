<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import Label from '@/Components/ui/Label.vue';
import { CalendarDays, Info } from '@lucide/vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

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
        booking_date: string;
        start_time: string;
        end_time: string;
        court: { name: string; venue: { name: string } };
    };
    snapToken?: string | null;
    midtransClientKey?: string;
}>();

const form = useForm({ method: 'qris' });
const isMidtrans = ref(!!props.snapToken);
const submit = () => form.post(route('payments.store', props.booking.id));

const loadSnap = () => {
    if (window.snap) {
        openSnap();
        return;
    }
    if (!props.snapToken || !props.midtransClientKey) return;
    const script = document.createElement('script');
    script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
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

onMounted(loadSnap);
</script>

<template>
    <Head title="Pay booking" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-foreground">Bayar booking</h2>
                <p class="mt-1 text-sm text-muted-foreground">Selesaikan pembayaran untuk mengunci lapangan.</p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-2xl space-y-4 px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                                <CalendarDays class="h-6 w-6" />
                            </div>
                            <div>
                                <p class="font-bold text-foreground">{{ booking.court.venue.name }} — {{ booking.court.name }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ booking.booking_date }} · {{ booking.start_time }}–{{ booking.end_time }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-5 flex items-center justify-between border-t border-border pt-5">
                            <span class="text-sm font-medium text-muted-foreground">Total</span>
                            <p class="text-2xl font-extrabold text-accent">Rp {{ Number(booking.total_amount).toLocaleString('id-ID') }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <form v-if="!isMidtrans" class="space-y-5" @submit.prevent="submit">
                            <div>
                                <Label for="method">Metode pembayaran</Label>
                                <select
                                    id="method"
                                    v-model="form.method"
                                    class="mt-1 flex h-10 w-full rounded-lg border border-input bg-white px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                >
                                    <option value="qris">QRIS</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="e_wallet">E-Wallet</option>
                                    <option value="cash">Cash</option>
                                </select>
                            </div>

                            <div class="flex items-start gap-2 rounded-lg bg-muted p-3 text-xs text-muted-foreground">
                                <Info class="mt-0.5 h-4 w-4 shrink-0 text-accent" />
                                <p>Mode MVP: pembayaran manual. Integrasi gateway (Midtrans/Xendit) menyusul saat API key siap.</p>
                            </div>

                            <div class="flex items-center gap-3 pt-1">
                                <Button variant="accent" type="submit" :disabled="form.processing">
                                    {{ form.processing ? 'Memproses…' : 'Tandai lunas' }}
                                </Button>
                                <Link :href="route('bookings.index')" class="text-sm font-medium text-muted-foreground transition hover:text-foreground">
                                    Kembali
                                </Link>
                            </div>
                        </form>

                        <div v-else class="space-y-4">
                            <div class="flex items-start gap-2 rounded-lg bg-accent/5 p-3 text-xs text-muted-foreground">
                                <Info class="mt-0.5 h-4 w-4 shrink-0 text-accent" />
                                <p>Jendela pembayaran Midtrans akan terbuka. Selesaikan pembayaran di sana — status booking ter-update otomatis setelah konfirmasi.</p>
                            </div>
                            <div class="flex items-center gap-3 pt-1">
                                <Button variant="accent" @click="loadSnap">Buka pembayaran</Button>
                                <Link :href="route('bookings.index')" class="text-sm font-medium text-muted-foreground transition hover:text-foreground">
                                    Kembali
                                </Link>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
