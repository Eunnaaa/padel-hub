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

defineProps<{
    bookings: Array<{
        id: number;
        start_time: string;
        end_time: string;
        status: string;
        payment_status: string;
        user: { name: string };
        court: { name: string; venue: { name: string } };
        payment: { transaction_reference: string } | null;
    }>;
}>();

const statusVariant = (s: string): 'warning' | 'success' | 'info' | 'muted' | 'destructive' =>
    ({ pending: 'warning', confirmed: 'success', checked_in: 'info', finished: 'muted', cancelled: 'destructive', no_show: 'muted' } as Record<string, 'warning' | 'success' | 'info' | 'muted' | 'destructive'>)[s] ?? 'muted';

const dialog = ref<{ open: boolean; id: number; label: string; status: string }>({ open: false, id: 0, label: '', status: '' });
const setStatus = (b: { id: number; status: string }, status: string, label: string) => {
    dialog.value = { open: true, id: b.id, label, status };
};
const confirmStatus = () => {
    router.patch(route('checkin.update', dialog.value.id), { status: dialog.value.status });
    dialog.value.open = false;
};
</script>

<template>
    <Head title="Check-in" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-foreground">Today's check-in</h2>
                <p class="mt-1 text-sm text-muted-foreground">Verifikasi pemain dan update status lapangan hari ini.</p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-5xl space-y-4 px-4 sm:px-6 lg:px-8">
                <Card v-for="b in bookings" :key="b.id" class="transition hover:shadow-md">
                    <CardContent class="p-6">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-accent/10 text-accent">
                                    <QrCode class="h-7 w-7" />
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-foreground">{{ b.court.venue.name }} — {{ b.court.name }}</p>
                                    <p class="mt-0.5 text-sm font-medium text-foreground">{{ b.start_time }}–{{ b.end_time }}</p>
                                    <p class="flex items-center gap-1 text-sm text-muted-foreground">
                                        <User class="h-3.5 w-3.5" />
                                        {{ b.user.name }}
                                        <span v-if="b.payment" class="ml-2 rounded bg-muted px-1.5 py-0.5 font-mono text-xs">
                                            {{ b.payment.transaction_reference }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <Badge :variant="statusVariant(b.status)">{{ b.status }}</Badge>
                                <Badge :variant="b.payment_status === 'paid' ? 'success' : 'warning'">{{ b.payment_status }}</Badge>
                            </div>
                        </div>

                        <div v-if="b.payment_status === 'paid'" class="mt-5 flex flex-wrap gap-2 border-t border-border pt-4">
                            <Button v-if="b.status === 'confirmed'" variant="accent" @click="setStatus(b, 'checked_in', 'Check-in pemain?')">
                                Check in
                            </Button>
                            <Button v-if="b.status === 'checked_in'" variant="secondary" @click="setStatus(b, 'finished', 'Tandai selesai?')">
                                Finish
                            </Button>
                            <Button v-if="b.status === 'confirmed'" variant="outline" class="text-destructive hover:bg-destructive/5" @click="setStatus(b, 'no_show', 'Tandai no-show?')">
                                No show
                            </Button>
                        </div>
                        <p v-else class="mt-4 border-t border-border pt-4 text-sm text-muted-foreground">Belum dibayar — tunggu pembayaran sebelum check-in.</p>
                    </CardContent>
                </Card>

                <Card v-if="!bookings.length" class="border-dashed">
                    <CardContent class="py-16 text-center">
                        <p class="font-semibold text-foreground">Tidak ada booking hari ini</p>
                        <p class="mt-1 text-sm text-muted-foreground">Semua lapangan kosong untuk hari ini.</p>
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
