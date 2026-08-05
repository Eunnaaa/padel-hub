<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    booking: { id: number; court_id: number; booking_date: string; start_time: string; end_time: string };
    courts: Array<{ id: number; name: string; price_per_hour: string; venue: { name: string } }>;
}>();

const form = useForm({
    court_id: String(props.booking.court_id),
    booking_date: props.booking.booking_date.slice(0, 10),
    start_time: props.booking.start_time.slice(0, 5),
    end_time: props.booking.end_time.slice(0, 5),
});

const err = (k: string) => (form.errors as Record<string, string>)[k];
const submit = () => form.put(route('bookings.update', props.booking.id));
</script>

<template>
    <Head title="Edit booking" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-2xl font-bold tracking-tight text-foreground">Edit booking</h2></template>
        <div class="py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-8">
                        <form class="space-y-5" @submit.prevent="submit">
                            <div>
                                <Label for="court">Lapangan *</Label>
                                <select
                                    id="court"
                                    v-model="form.court_id"
                                    class="mt-1 flex h-10 w-full rounded-lg border border-input bg-white px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                    required
                                >
                                    <option v-for="c in courts" :key="c.id" :value="c.id">{{ c.venue.name }} — {{ c.name }} (Rp {{ c.price_per_hour }}/jam)</option>
                                </select>
                            </div>
                            <div>
                                <Label for="date">Tanggal *</Label>
                                <Input id="date" v-model="form.booking_date" type="date" required />
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
                            <p v-if="err('start_time')" class="text-sm text-destructive">{{ err('start_time') }}</p>
                            <div class="flex items-center gap-4 border-t border-border pt-6">
                                <Button variant="accent" type="submit" :disabled="form.processing">
                                    {{ form.processing ? 'Menyimpan…' : 'Simpan perubahan' }}
                                </Button>
                                <Link :href="route('bookings.index')" class="text-sm font-medium text-muted-foreground transition hover:text-foreground">Batal</Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
