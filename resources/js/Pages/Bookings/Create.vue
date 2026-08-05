<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import { CalendarDays, Clock } from '@lucide/vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    courts: Array<{ id: number; name: string; price_per_hour: string; venue: { id: number; name: string; city: string } }>;
}>();

const query = new URLSearchParams(window.location.search);
const venueId = ref<number | ''>('');
const form = useForm({
    court_id: query.get('court_id') ?? '',
    booking_date: query.get('booking_date') ?? '',
    start_time: query.get('start_time') ?? '08:00',
    end_time: query.get('end_time') ?? '09:00',
    recurrence_rule: '',
});

if (form.court_id) {
    const prefilled = props.courts.find((c) => c.id === Number(form.court_id));
    if (prefilled) venueId.value = prefilled.venue.id;
}

const venues = computed(() => {
    const map = new Map<number, { id: number; name: string; city: string }>();
    props.courts.forEach((c) => map.set(c.venue.id, c.venue));
    return [...map.values()];
});

const filteredCourts = computed(() =>
    venueId.value === '' ? props.courts : props.courts.filter((c) => c.venue.id === venueId.value),
);

const selectedCourt = computed(() => filteredCourts.value.find((c) => c.id === Number(form.court_id)) ?? null);

const hours = computed(() => {
    const [sh, sm] = form.start_time.split(':').map(Number);
    const [eh, em] = form.end_time.split(':').map(Number);
    if ([sh, sm, eh, em].some(Number.isNaN)) return 0;
    return Math.max((eh + em / 60) - (sh + sm / 60), 0);
});

const total = computed(() => (selectedCourt.value ? Math.round(hours.value * Number(selectedCourt.value.price_per_hour)) : 0));

const today = new Date().toISOString().slice(0, 10);
const selectClass = 'mt-1 flex h-10 w-full rounded-lg border border-input bg-white px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring';
const submit = () => form.post(route('bookings.store'));
</script>

<template>
    <Head title="New booking" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-2xl font-bold tracking-tight text-foreground">Booking lapangan</h2></template>

        <div class="py-10">
            <div class="mx-auto grid max-w-5xl gap-6 px-4 sm:px-6 lg:px-8 lg:grid-cols-5">
                <Card class="lg:col-span-3">
                    <CardContent class="p-8">
                        <form class="space-y-5" @submit.prevent="submit">
                            <div>
                                <Label for="venue">Venue</Label>
                                <select id="venue" v-model="venueId" class="mt-1 flex h-10 w-full rounded-lg border border-input bg-white px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                    <option :value="''">Semua venue</option>
                                    <option v-for="v in venues" :key="v.id" :value="v.id">{{ v.name }} — {{ v.city }}</option>
                                </select>
                            </div>

                            <div>
                                <Label for="court">Lapangan *</Label>
                                <select id="court" v-model="form.court_id" class="mt-1 flex h-10 w-full rounded-lg border border-input bg-white px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" required>
                                    <option value="">Pilih lapangan</option>
                                    <option v-for="c in filteredCourts" :key="c.id" :value="c.id">{{ c.name }} — Rp {{ c.price_per_hour }}/jam</option>
                                </select>
                            </div>

                            <div>
                                <Label for="date">Tanggal *</Label>
                                <Input id="date" v-model="form.booking_date" type="date" :min="today" required />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label for="start">Mulai</Label>
                                    <Input id="start" v-model="form.start_time" type="time" required />
                                </div>
                                <div>
                                    <Label for="end">Selesai</Label>
                                    <Input id="end" v-model="form.end_time" type="time" required />
                                </div>
                            </div>

                            <p v-if="hours <= 0 && form.start_time && form.end_time" class="text-sm text-destructive">Waktu selesai harus setelah waktu mulai.</p>
                            <p v-if="form.errors.start_time" class="text-sm text-destructive">{{ form.errors.start_time }}</p>

                            <label class="flex items-start gap-3 rounded-xl border border-border p-4">
                                <input
                                    v-model="form.recurrence_rule"
                                    type="checkbox"
                                    value="weekly"
                                    class="mt-0.5 h-4 w-4 rounded border-input text-accent focus:ring-accent"
                                />
                                <div>
                                    <span class="block text-sm font-semibold text-foreground">Booking mingguan (recurring)</span>
                                    <span class="block text-xs text-muted-foreground">Ulang otomatis setiap minggu selama 4 pekan di jam yang sama.</span>
                                </div>
                            </label>

                            <div class="flex items-center gap-4 border-t border-border pt-6">
                                <Button variant="accent" type="submit" :disabled="form.processing || !form.court_id || !form.booking_date || hours <= 0">
                                    {{ form.processing ? 'Menyimpan…' : 'Booking sekarang' }}
                                </Button>
                                <Link :href="route('bookings.index')" class="text-sm font-medium text-muted-foreground transition hover:text-foreground">Batal</Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <div class="lg:col-span-2">
                    <Card class="sticky top-6">
                        <CardContent class="p-6">
                            <h3 class="font-bold text-foreground">Ringkasan</h3>

                            <div v-if="selectedCourt && form.booking_date" class="mt-5 space-y-3 rounded-xl bg-muted p-4 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-muted-foreground">Venue</span>
                                    <span class="font-medium text-foreground">{{ selectedCourt.venue.name }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-muted-foreground">Lapangan</span>
                                    <span class="font-medium text-foreground">{{ selectedCourt.name }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-1 text-muted-foreground"><CalendarDays class="h-3.5 w-3.5" /> Tanggal</span>
                                    <span class="font-medium text-foreground">{{ form.booking_date }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-1 text-muted-foreground"><Clock class="h-3.5 w-3.5" /> Durasi</span>
                                    <span class="font-medium text-foreground">{{ hours }} jam</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-muted-foreground">Tarif</span>
                                    <span class="font-medium text-foreground">Rp {{ selectedCourt.price_per_hour }}/jam</span>
                                </div>
                            </div>
                            <p v-else class="mt-5 text-sm text-muted-foreground">Pilih lapangan dan tanggal untuk melihat ringkasan.</p>

                            <div class="mt-6 flex items-center justify-between border-t border-border pt-5">
                                <span class="font-medium text-foreground">Total</span>
                                <span class="text-2xl font-extrabold text-accent">Rp {{ total.toLocaleString('id-ID') }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
