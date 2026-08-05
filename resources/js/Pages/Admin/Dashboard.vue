<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import { Ban, Banknote, CalendarDays, Check, ShieldCheck, UserCheck, Users, X, XCircle } from '@lucide/vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps<{
    stats: { users: number; pending_venues: number; bookings: number; revenue: string };
    users: Array<{ id: number; name: string; email: string; role: string; is_active: boolean; created_at: string }>;
    pendingVenues: Array<{ id: number; name: string; city: string; status: string }>;
    recentBookings: Array<{
        id: number;
        booking_date: string;
        start_time: string;
        end_time: string;
        status: string;
        payment_status: string;
        total_amount: string;
        court: { name: string; venue: { name: string } };
        user: { name: string };
    }>;
}>();

const fmt = (n: string | number) => Number(n).toLocaleString('id-ID');
const fmtDate = (d: string) => new Date(`${d}T00:00:00`).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });

const metricCards = [
    { label: 'Total users', value: fmt(props.stats.users), icon: Users },
    { label: 'Venue menunggu', value: fmt(props.stats.pending_venues), icon: ShieldCheck },
    { label: 'Total bookings', value: fmt(props.stats.bookings), icon: CalendarDays },
    { label: 'Revenue (paid)', value: `Rp ${fmt(props.stats.revenue)}`, icon: Banknote },
];

const bookingVariant = (s: string) =>
    ({ pending: 'warning', confirmed: 'success', checked_in: 'info', finished: 'muted', cancelled: 'destructive', expired: 'muted' } as Record<string, 'warning' | 'success' | 'info' | 'muted' | 'destructive'>)[s] ?? 'muted';
</script>

<template>
    <Head title="Admin" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-foreground">Admin dashboard</h2>
                <p class="mt-1 text-sm text-muted-foreground">Monitoring sistem, pengguna, dan venue.</p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card v-for="m in metricCards" :key="m.label" class="transition hover:shadow-md">
                        <CardContent class="p-6">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-muted-foreground">{{ m.label }}</p>
                                <component :is="m.icon" class="h-4 w-4 text-accent" />
                            </div>
                            <p class="mt-2 text-2xl font-extrabold text-foreground">{{ m.value }}</p>
                        </CardContent>
                    </Card>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Manajemen user</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="u in users" :key="u.id" class="flex items-center justify-between gap-3 rounded-xl border border-border p-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="truncate font-semibold text-foreground">{{ u.name }}</p>
                                            <Badge :variant="u.role === 'admin' ? 'accent' : 'secondary'">{{ u.role }}</Badge>
                                        </div>
                                        <p class="truncate text-xs text-muted-foreground">{{ u.email }}</p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <Badge :variant="u.is_active ? 'success' : 'destructive'">
                                            {{ u.is_active ? 'Aktif' : 'Suspend' }}
                                        </Badge>
                                        <Button
                                            v-if="u.role !== 'admin'"
                                            variant="outline"
                                            size="sm"
                                            @click="router.post(route('admin.users.toggle', u.id))"
                                        >
                                            <Ban v-if="u.is_active" class="h-3.5 w-3.5" />
                                            <UserCheck v-else class="h-3.5 w-3.5" />
                                            {{ u.is_active ? 'Suspend' : 'Aktifkan' }}
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Persetujuan venue</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="pendingVenues.length" class="space-y-3">
                                <div v-for="v in pendingVenues" :key="v.id" class="flex items-center justify-between gap-3 rounded-xl border border-border p-3">
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-foreground">{{ v.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ v.city }}</p>
                                    </div>
                                    <div class="flex shrink-0 gap-2">
                                        <Button variant="accent" size="sm" @click="router.post(route('admin.venues.approve', v.id))">
                                            <Check class="h-3.5 w-3.5" />
                                            Setujui
                                        </Button>
                                        <Button variant="outline" size="sm" class="text-destructive hover:bg-destructive/5" @click="router.post(route('admin.venues.reject', v.id))">
                                            <X class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="rounded-xl border border-dashed p-8 text-center">
                                <XCircle class="mx-auto h-8 w-8 text-muted-foreground" />
                                <p class="mt-2 text-sm font-medium text-foreground">Tidak ada venue menunggu persetujuan</p>
                                <p class="text-xs text-muted-foreground">Semua venue sudah diverifikasi.</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Booking terbaru</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div v-for="b in recentBookings" :key="b.id" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-border p-3">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-foreground">{{ b.court.venue.name }} — {{ b.court.name }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ b.user.name }} · {{ fmtDate(b.booking_date) }} · {{ b.start_time }}–{{ b.end_time }}
                                    </p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <Badge :variant="bookingVariant(b.status)">{{ b.status }}</Badge>
                                    <Badge :variant="b.payment_status === 'paid' ? 'success' : 'warning'">{{ b.payment_status }}</Badge>
                                    <span class="text-sm font-bold text-foreground">Rp {{ fmt(b.total_amount) }}</span>
                                </div>
                            </div>
                            <p v-if="!recentBookings.length" class="text-sm text-muted-foreground">Belum ada booking.</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
