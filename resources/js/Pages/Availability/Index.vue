<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import { CalendarDays, RefreshCw, Wrench } from '@lucide/vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{
    date: string;
    venues: Array<{ id: number; name: string; city: string }>;
    courts: Array<{
        id: number;
        name: string;
        price_per_hour: string;
        status: string;
        venue_id: number;
        venue: { id: number; name: string };
    }>;
    bookings: Array<{ court_id: number; start_time: string; end_time: string; status: string }>;
}>();

const venueId = ref<number | ''>('');
const localDate = ref(props.date);

const hours = Array.from({ length: 15 }, (_, i) => i + 7); // 07:00–21:00

const filteredCourts = computed(() =>
    venueId.value === '' ? props.courts : props.courts.filter((c) => c.venue_id === venueId.value),
);

const toHHMM = (t: string) => t.slice(0, 5);

const slotState = (courtId: number, hour: number): 'available' | 'booked' | 'pending' | 'maintenance' => {
    const court = props.courts.find((c) => c.id === courtId);
    if (court?.status === 'maintenance') return 'maintenance';

    const start = `${hour}:00`;
    const end = `${hour + 1}:00`;
    const booking = props.bookings.find(
        (b) =>
            b.court_id === courtId &&
            toHHMM(b.start_time) < end &&
            toHHMM(b.end_time) > start,
    );
    if (!booking) return 'available';
    return booking.status === 'pending' ? 'pending' : 'booked';
};

const pickSlot = (courtId: number, hour: number) => {
    const state = slotState(courtId, hour);
    if (state !== 'available') return;
    router.get(route('bookings.create'), {
        court_id: courtId,
        booking_date: localDate.value,
        start_time: `${hour}:00`,
        end_time: `${hour + 1}:00`,
    });
};

const today = new Date().toISOString().slice(0, 10);
const changeDate = () => {
    router.get(route('availability.index'), { date: localDate.value }, { preserveState: true, preserveScroll: true, replace: true });
};

let timer: ReturnType<typeof setInterval> | null = null;
onMounted(() => {
    timer = setInterval(() => {
        router.reload({ only: ['bookings'] });
    }, 60000);
});
onUnmounted(() => {
    if (timer) clearInterval(timer);
});

</script>

<template>
    <Head title="Ketersediaan" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-foreground">Ketersediaan lapangan</h2>
                <p class="mt-1 text-sm text-muted-foreground">Pilih slot kosong untuk langsung booking.</p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-foreground">Tanggal</label>
                        <div class="relative">
                            <CalendarDays class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <input
                                v-model="localDate"
                                type="date"
                                :min="today"
                                @change="changeDate"
                                class="h-10 rounded-lg border border-input bg-white pl-10 pr-3 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-foreground">Venue</label>
                        <select
                            v-model="venueId"
                            class="h-10 rounded-lg border border-input bg-white px-3 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            <option :value="''">Semua venue</option>
                            <option v-for="v in venues" :key="v.id" :value="v.id">{{ v.name }} — {{ v.city }}</option>
                        </select>
                    </div>

                    <Button variant="outline" size="icon" title="Refresh" @click="router.reload({ only: ['bookings'] })">

                        <RefreshCw class="h-4 w-4" />
                    </Button>

                    <div class="ml-auto flex flex-wrap items-center gap-4 text-xs">
                        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-muted ring-1 ring-border" /> Kosong</span>
                        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-accent/20 ring-1 ring-accent/40" /> Pending</span>
                        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-destructive/15 ring-1 ring-destructive/40" /> Terbooking</span>
                        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-amber-200 ring-1 ring-amber-300" /> Maintenance</span>
                    </div>
                </div>

                <Card class="overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="sticky left-0 z-10 min-w-48 bg-card px-4 py-3 text-left font-semibold text-foreground">Lapangan</th>
                                    <th
                                        v-for="h in hours"
                                        :key="h"
                                        class="px-1 py-3 text-center font-medium text-muted-foreground"
                                    >
                                        {{ String(h).padStart(2, '0') }}:00
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="court in filteredCourts" :key="court.id" class="border-b border-border last:border-0">
                                    <td class="sticky left-0 z-10 bg-card px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <Wrench v-if="court.status === 'maintenance'" class="h-4 w-4 shrink-0 text-amber-500" />
                                            <div class="min-w-0">
                                                <p class="font-semibold text-foreground">{{ court.name }}</p>
                                                <p class="truncate text-xs text-muted-foreground">{{ court.venue.name }} · Rp {{ Number(court.price_per_hour).toLocaleString('id-ID') }}/jam</p>
                                            </div>
                                            <Badge v-if="court.status === 'maintenance'" variant="warning" class="ml-1">Maint</Badge>
                                        </div>
                                    </td>
                                    <td
                                        v-for="h in hours"
                                        :key="h"
                                        class="p-1 text-center"
                                    >
                                        <button
                                            v-if="slotState(court.id, h) === 'available'"
                                            @click="pickSlot(court.id, h)"
                                            class="h-8 w-full rounded-md bg-muted transition hover:bg-accent"
                                            title="Klik untuk booking"
                                        />
                                        <div
                                            v-else
                                            class="h-8 w-full rounded-md"
                                            :class="{
                                                'bg-accent/20 ring-1 ring-accent/40': slotState(court.id, h) === 'pending',
                                                'bg-destructive/15 ring-1 ring-destructive/40': slotState(court.id, h) === 'booked',
                                                'bg-amber-200/60 ring-1 ring-amber-300': slotState(court.id, h) === 'maintenance',
                                            }"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <p v-if="!filteredCourts.length" class="text-sm text-muted-foreground">Tidak ada lapangan untuk venue ini.</p>
                <p class="text-xs text-muted-foreground">
                    Halaman auto-refresh setiap 60 detik. Slot tersedia bisa langsung diklik untuk memulai booking.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
