<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{ venues: Array<{ id: number; name: string }> }>();
const form = useForm({ venue_id: '', name: '', price_per_hour: '', status: 'available' });
const err = (k: string) => (form.errors as Record<string, string>)[k];
const submit = () => form.post(route('courts.store'));
</script>

<template>
    <Head title="Tambah lapangan" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-2xl font-bold tracking-tight text-foreground">Tambah lapangan</h2></template>
        <div class="py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-8">
                        <form class="space-y-5" @submit.prevent="submit">
                            <div>
                                <Label for="venue_id">Venue *</Label>
                                <select
                                    id="venue_id"
                                    v-model="form.venue_id"
                                    class="mt-1 flex h-10 w-full rounded-lg border border-input bg-white px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                >
                                    <option value="">Pilih venue</option>
                                    <option v-for="venue in venues" :key="venue.id" :value="venue.id">{{ venue.name }}</option>
                                </select>
                                <p v-if="err('venue_id')" class="mt-1 text-sm text-destructive">{{ err('venue_id') }}</p>
                            </div>
                            <div>
                                <Label for="name">Nama lapangan *</Label>
                                <Input id="name" v-model="form.name" placeholder="Court 1" />
                                <p v-if="err('name')" class="mt-1 text-sm text-destructive">{{ err('name') }}</p>
                            </div>
                            <div>
                                <Label for="price">Tarif per jam (Rp) *</Label>
                                <Input id="price" v-model="form.price_per_hour" type="number" min="0" step="1000" placeholder="150000" />
                                <p v-if="err('price_per_hour')" class="mt-1 text-sm text-destructive">{{ err('price_per_hour') }}</p>
                            </div>
                            <div>
                                <Label for="status">Status</Label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="mt-1 flex h-10 w-full rounded-lg border border-input bg-white px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                >
                                    <option value="available">Available</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-4 border-t border-border pt-6">
                                <Button variant="accent" type="submit" :disabled="form.processing">
                                    {{ form.processing ? 'Menyimpan…' : 'Simpan lapangan' }}
                                </Button>
                                <Link :href="route('courts.index')" class="text-sm font-medium text-muted-foreground transition hover:text-foreground">Batal</Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
