<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import { Banknote, Clock, TrendingUp, Users } from '@lucide/vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    revenue: { today: string; week: string; month: string };
    bookings: { today: number; week: number; month: number };
    trendDays: Array<{ date: string; label: string; bookings: number; revenue: number }>;
    maxRevenue: number;
    maxBookings: number;
    peakHours: Array<{ hour: string; total: string }>;
    courtUsage: Array<{ court: string; total: number }>;
}>();

const revenueHeight = (r: number) => `${Math.max((r / props.maxRevenue) * 100, 2)}%`;
const bookingHeight = (b: number) => `${Math.max((b / props.maxBookings) * 100, 2)}%`;
const fmt = (n: number | string) => Number(n).toLocaleString('id-ID');
const maxCourt = computed(() => Math.max(...props.courtUsage.map((c) => c.total), 1));

const metricCards = [
    { label: 'Revenue hari ini', value: `Rp ${fmt(props.revenue.today)}`, icon: Banknote },
    { label: 'Revenue minggu ini', value: `Rp ${fmt(props.revenue.week)}`, icon: TrendingUp },
    { label: 'Revenue bulan ini', value: `Rp ${fmt(props.revenue.month)}`, icon: Banknote },
    { label: 'Booking hari ini', value: `${props.bookings.today}`, icon: Users },
    { label: 'Booking minggu ini', value: `${props.bookings.week}`, icon: Users },
    { label: 'Booking bulan ini', value: `${props.bookings.month}`, icon: Users },
];
</script>

<template>
    <Head title="Analytics" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-foreground">Analytics venue</h2>
                <p class="mt-1 text-sm text-muted-foreground">Revenue, okupansi, dan tren booking.</p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
                            <CardTitle class="text-base">Revenue trend (7 hari)</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex h-40 items-end gap-2">
                                <div v-for="d in trendDays" :key="d.date" class="flex flex-1 flex-col items-center gap-1">
                                    <div class="w-full rounded-t bg-accent" :style="{ height: revenueHeight(d.revenue) }" :title="`Rp ${fmt(d.revenue)}`" />
                                    <span class="text-xs text-muted-foreground">{{ d.label }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Booking trend (7 hari)</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex h-40 items-end gap-2">
                                <div v-for="d in trendDays" :key="d.date" class="flex flex-1 flex-col items-center gap-1">
                                    <div class="w-full rounded-t bg-primary" :style="{ height: bookingHeight(d.bookings) }" :title="`${d.bookings} bookings`" />
                                    <span class="text-xs text-muted-foreground">{{ d.label }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-base">
                                <Clock class="h-4 w-4 text-accent" />
                                Jam terpadat
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ul class="space-y-2">
                                <li v-for="p in peakHours" :key="p.hour" class="flex items-center justify-between rounded-lg bg-muted px-3 py-2 text-sm">
                                    <span class="font-medium text-foreground">{{ p.hour }}</span>
                                    <span class="text-muted-foreground">{{ p.total }} booking</span>
                                </li>
                                <li v-if="!peakHours.length" class="text-sm text-muted-foreground">Belum ada data.</li>
                            </ul>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Pemakaian lapangan</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-for="c in courtUsage" :key="c.court">
                                    <div class="mb-1 flex justify-between text-sm">
                                        <span class="font-medium text-foreground">{{ c.court }}</span>
                                        <span class="text-muted-foreground">{{ c.total }}</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded bg-muted">
                                        <div class="h-2 rounded bg-accent" :style="{ width: `${(c.total / maxCourt) * 100}%` }" />
                                    </div>
                                </div>
                                <p v-if="!courtUsage.length" class="text-sm text-muted-foreground">Belum ada data.</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
