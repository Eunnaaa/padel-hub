<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardDescription from '@/Components/ui/CardDescription.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import { CalendarDays, ChevronRight, MapPin, Plus, Tag } from '@lucide/vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps<{
    stats: { total: number; upcoming: number; completed: number; cancelled: number };
    upcoming: Array<{
        id: number;
        booking_date: string;
        start_time: string;
        end_time: string;
        status: string;
        payment_status: string;
        total_amount: string;
        court: { name: string; venue: { name: string; city: string } };
    }>;
    promos: Array<{
        id: number;
        code: string;
        title: string;
        description: string | null;
        discount_type: string;
        discount_value: string;
        ends_at: string;
    }>;
    notifications: Array<{
        id: string;
        data: { title: string; body: string; url?: string };
        read_at: string | null;
        created_at: string;
    }>;
    unreadNotifications: number;
}>();

const fmtDate = (d: string) =>
    new Date(`${d}T00:00:00`).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });

const fmt = (n: string) => Number(n).toLocaleString('id-ID');

const fmtPromoValue = (p: (typeof props.promos)[number]) =>
    p.discount_type === 'percent' ? `${Number(p.discount_value)}%` : `Rp ${fmt(p.discount_value)}`;

const timeAgo = (d: string) => {
    const mins = Math.floor((Date.now() - new Date(d).getTime()) / 60000);
    if (mins < 60) return `${mins} mnt lalu`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return `${hours} jam lalu`;
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
};

const statusBadge = (s: string): 'warning' | 'success' | 'info' | 'muted' =>
    ({ pending: 'warning', confirmed: 'success', checked_in: 'info' } as Record<string, 'warning' | 'success' | 'info' | 'muted'>)[s] ?? 'muted';

const cards = [
    { label: 'Total booking', value: props.stats.total, icon: CalendarDays, accent: 'text-foreground' },
    { label: 'Segera datang', value: props.stats.upcoming, icon: Plus, accent: 'text-accent' },
    { label: 'Selesai', value: props.stats.completed, icon: CalendarDays, accent: 'text-muted-foreground' },
    { label: 'Dibatalkan', value: props.stats.cancelled, icon: CalendarDays, accent: 'text-destructive' },
];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-foreground">Halo, {{ $page.props.auth.user.name }}</h2>
                <p class="mt-1 text-sm text-muted-foreground">Ini ringkasan aktivitas padel kamu.</p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card v-for="card in cards" :key="card.label" class="transition hover:shadow-md">
                        <CardContent class="p-6">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-muted-foreground">{{ card.label }}</p>
                                <component :is="card.icon" class="h-4 w-4 text-muted-foreground" />
                            </div>
                            <p class="mt-2 text-3xl font-extrabold" :class="card.accent">{{ card.value }}</p>
                        </CardContent>
                    </Card>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-foreground">Booking segera datang</h3>
                        <p class="text-sm text-muted-foreground">Jadwal terdekat kamu.</p>
                    </div>
                    <Button as="link" :href="route('bookings.create')" variant="accent" size="lg">
                        <Plus class="h-4 w-4" />
                        Booking lapangan
                    </Button>
                </div>

                <div v-if="upcoming.length" class="grid gap-4 lg:grid-cols-2">
                    <Card v-for="b in upcoming" :key="b.id" class="transition hover:shadow-md">
                        <CardHeader class="flex-row items-start justify-between space-y-0">
                            <div class="space-y-1">
                                <CardTitle class="flex items-center gap-2">
                                    {{ b.court.venue.name }} — {{ b.court.name }}
                                </CardTitle>
                                <CardDescription class="flex items-center gap-1">
                                    <MapPin class="h-3.5 w-3.5" />
                                    {{ b.court.venue.city }}
                                </CardDescription>
                            </div>
                            <Badge :variant="statusBadge(b.status)">{{ b.status }}</Badge>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center justify-between rounded-xl bg-muted px-4 py-3 text-sm">
                                <span class="font-medium text-foreground">{{ fmtDate(b.booking_date) }} · {{ b.start_time }}–{{ b.end_time }}</span>
                                <span class="font-bold text-foreground">Rp {{ fmt(b.total_amount) }}</span>
                            </div>
                        </CardContent>
                        <CardFooter>
                            <Button
                                as="link"
                                :href="b.payment_status !== 'paid' && b.status === 'pending' ? route('payments.create', b.id) : route('bookings.index')"
                                variant="ghost"
                                class="gap-1 px-0 text-accent hover:bg-transparent hover:text-green-600"
                            >
                                {{ b.payment_status !== 'paid' && b.status === 'pending' ? 'Bayar sekarang' : 'Lihat detail' }}
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </CardFooter>
                    </Card>
                </div>

                <Card v-else class="border-dashed">
                    <CardContent class="flex flex-col items-center py-16 text-center">
                        <p class="font-semibold text-foreground">Belum ada booking</p>
                        <p class="mt-1 text-sm text-muted-foreground">Booking pertamamu tinggal beberapa klik lagi.</p>
                        <Button as="link" :href="route('bookings.create')" variant="accent" class="mt-6">
                            <Plus class="h-4 w-4" />
                            Mulai booking
                        </Button>
                    </CardContent>
                </Card>

                <div class="grid gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader class="flex-row items-center justify-between space-y-0">
                            <CardTitle class="text-base">Notifikasi</CardTitle>
                            <button
                                v-if="unreadNotifications > 0"
                                class="text-xs font-medium text-accent hover:underline"
                                @click="router.post(route('notifications.read-all'))"
                            >
                                Tandai dibaca
                            </button>
                        </CardHeader>
                        <CardContent>
                            <div v-if="notifications.length" class="space-y-3">
                                <div v-for="n in notifications" :key="n.id" class="flex items-start gap-3 rounded-xl border border-border p-3">
                                    <span
                                        class="mt-1.5 h-2 w-2 shrink-0 rounded-full"
                                        :class="n.read_at ? 'bg-transparent' : 'bg-accent'"
                                    />
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-foreground">{{ n.data.title }}</p>
                                        <p class="text-sm text-muted-foreground">{{ n.data.body }}</p>
                                        <p class="mt-1 text-xs text-muted-foreground/70">{{ timeAgo(n.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="py-6 text-center text-sm text-muted-foreground">Belum ada notifikasi.</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex-row items-center justify-between space-y-0">
                            <CardTitle class="text-base">Promo aktif</CardTitle>
                            <Tag class="h-4 w-4 text-accent" />
                        </CardHeader>
                        <CardContent>
                            <div v-if="promos.length" class="space-y-3">
                                <div v-for="p in promos" :key="p.id" class="flex items-center gap-4 rounded-xl bg-accent/5 p-4">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-accent text-white">
                                        <span class="text-sm font-extrabold">{{ fmtPromoValue(p) }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-foreground">{{ p.title }}</p>
                                        <p class="truncate text-sm text-muted-foreground">{{ p.description }}</p>
                                        <p class="mt-0.5 font-mono text-xs font-semibold uppercase text-accent">{{ p.code }}</p>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="py-6 text-center text-sm text-muted-foreground">Belum ada promo aktif.</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
