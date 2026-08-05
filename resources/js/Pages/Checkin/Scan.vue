<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import { QrCode, User } from '@lucide/vue';
import { Head, router } from '@inertiajs/vue3';
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
    <Head :title="`Check-in — ${booking.court.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-foreground">Hasil scan</h2>
                <p class="mt-1 text-sm text-muted-foreground">Verifikasi booking dari QR pemain.</p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-accent/10 text-accent">
                                    <QrCode class="h-7 w-7" />
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-foreground">{{ booking.court.venue.name }} — {{ booking.court.name }}</p>
                                    <p class="mt-0.5 text-sm font-medium text-foreground">{{ booking.start_time }}–{{ booking.end_time }}</p>
                                    <p class="flex items-center gap-1 text-sm text-muted-foreground">
                                        <User class="h-3.5 w-3.5" />
                                        {{ booking.user.name }}
                                        <span v-if="booking.payment" class="ml-2 rounded bg-muted px-1.5 py-0.5 font-mono text-xs">
                                            {{ booking.payment.transaction_reference }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <Badge :variant="statusVariant(booking.status)">{{ booking.status }}</Badge>
                                <Badge :variant="booking.payment_status === 'paid' ? 'success' : 'warning'">{{ booking.payment_status }}</Badge>
                            </div>
                        </div>

                        <div v-if="booking.status === 'confirmed'" class="mt-5 flex flex-wrap gap-2 border-t border-border pt-4">
                            <Button variant="accent" @click="setStatus('checked_in', 'Check-in pemain?')">Check in</Button>
                            <Button variant="outline" class="text-destructive hover:bg-destructive/5" @click="setStatus('no_show', 'Tandai no-show?')">No show</Button>
                        </div>
                        <p v-else-if="booking.status === 'checked_in'" class="mt-5 border-t border-border pt-4 text-sm text-muted-foreground">
                            Sudah check-in. Tandai selesai dari daftar check-in hari ini.
                        </p>
                        <p v-else class="mt-5 border-t border-border pt-4 text-sm text-muted-foreground">Booking tidak dalam status check-in aktif.</p>
                    </CardContent>
                </Card>
            </div>
        </div>

        <ConfirmDialog
            v-model:open="dialog.open"
            title="Update status"
            :description="dialog.label"
            @confirm="confirmStatus"
        />
    </AuthenticatedLayout>
</template>
