<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import Textarea from '@/Components/ui/Textarea.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{ venue: { id: number; name: string; slug: string; description: string | null; address: string; city: string; latitude: number | null; longitude: number | null; status: string } }>();

const form = useForm({
    name: props.venue.name,
    slug: props.venue.slug,
    description: props.venue.description ?? '',
    address: props.venue.address,
    city: props.venue.city,
    latitude: props.venue.latitude ?? '',
    longitude: props.venue.longitude ?? '',
    status: props.venue.status,
});

const err = (k: string) => (form.errors as Record<string, string>)[k];
const submit = () => form.put(route('venues.update', props.venue.id));
</script>

<template>
    <Head title="Edit venue" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold tracking-tight text-foreground">Edit venue</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-8">
                        <form class="space-y-6" @submit.prevent="submit">
                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <Label for="name">Nama venue *</Label>
                                    <Input id="name" v-model="form.name" />
                                    <p v-if="err('name')" class="mt-1 text-sm text-destructive">{{ err('name') }}</p>
                                </div>
                                <div>
                                    <Label for="slug">Slug *</Label>
                                    <Input id="slug" v-model="form.slug" />
                                    <p v-if="err('slug')" class="mt-1 text-sm text-destructive">{{ err('slug') }}</p>
                                </div>
                                <div>
                                    <Label for="city">Kota *</Label>
                                    <Input id="city" v-model="form.city" />
                                    <p v-if="err('city')" class="mt-1 text-sm text-destructive">{{ err('city') }}</p>
                                </div>
                                <div>
                                    <Label for="status">Status</Label>
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        class="mt-1 flex h-10 w-full rounded-lg border border-input bg-white px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                    >
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <Label for="address">Alamat *</Label>
                                <Input id="address" v-model="form.address" />
                                <p v-if="err('address')" class="mt-1 text-sm text-destructive">{{ err('address') }}</p>
                            </div>

                            <div>
                                <Label for="description">Deskripsi</Label>
                                <Textarea id="description" v-model="form.description" />
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <Label for="latitude">Latitude</Label>
                                    <Input id="latitude" v-model="form.latitude" type="number" step="any" />
                                    <p v-if="err('latitude')" class="mt-1 text-sm text-destructive">{{ err('latitude') }}</p>
                                </div>
                                <div>
                                    <Label for="longitude">Longitude</Label>
                                    <Input id="longitude" v-model="form.longitude" type="number" step="any" />
                                    <p v-if="err('longitude')" class="mt-1 text-sm text-destructive">{{ err('longitude') }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 border-t border-border pt-6">
                                <Button variant="accent" type="submit" :disabled="form.processing">
                                    {{ form.processing ? 'Menyimpan…' : 'Simpan perubahan' }}
                                </Button>
                                <Link :href="route('venues.index')" class="text-sm font-medium text-muted-foreground transition hover:text-foreground">Batal</Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
