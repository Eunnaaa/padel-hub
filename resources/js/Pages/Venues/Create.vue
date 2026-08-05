<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import Textarea from '@/Components/ui/Textarea.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({ name: '', slug: '', description: '', address: '', city: '', latitude: '', longitude: '', status: 'pending' });
const slugTouched = ref(false);

const slugify = (s: string) =>
    s.toLowerCase().trim().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');

const onName = () => {
    if (!slugTouched.value) form.slug = slugify(form.name);
};

const err = (k: string) => (form.errors as Record<string, string>)[k];
const submit = () => form.post(route('venues.store'));
</script>

<template>
    <Head title="Tambah venue" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold tracking-tight text-foreground">Tambah venue</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-8">
                        <form class="space-y-6" @submit.prevent="submit">
                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <Label for="name">Nama venue *</Label>
                                    <Input id="name" v-model="form.name" @input="onName" placeholder="PadelHub Center" />
                                    <p v-if="err('name')" class="mt-1 text-sm text-destructive">{{ err('name') }}</p>
                                </div>
                                <div>
                                    <Label for="slug">Slug *</Label>
                                    <Input id="slug" v-model="form.slug" @input="slugTouched = true" placeholder="padelhub-center" />
                                    <p v-if="err('slug')" class="mt-1 text-sm text-destructive">{{ err('slug') }}</p>
                                </div>
                                <div>
                                    <Label for="city">Kota *</Label>
                                    <Input id="city" v-model="form.city" placeholder="Jakarta" />
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
                                <Input id="address" v-model="form.address" placeholder="Jl. Sudirman No. 1" />
                                <p v-if="err('address')" class="mt-1 text-sm text-destructive">{{ err('address') }}</p>
                            </div>

                            <div>
                                <Label for="description">Deskripsi</Label>
                                <Textarea id="description" v-model="form.description" placeholder="Deskripsi singkat venue…" />
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <Label for="latitude">Latitude</Label>
                                    <Input id="latitude" v-model="form.latitude" type="number" step="any" placeholder="-6.200000" />
                                    <p v-if="err('latitude')" class="mt-1 text-sm text-destructive">{{ err('latitude') }}</p>
                                </div>
                                <div>
                                    <Label for="longitude">Longitude</Label>
                                    <Input id="longitude" v-model="form.longitude" type="number" step="any" placeholder="106.816666" />
                                    <p v-if="err('longitude')" class="mt-1 text-sm text-destructive">{{ err('longitude') }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 border-t border-border pt-6">
                                <Button variant="accent" type="submit" :disabled="form.processing">
                                    {{ form.processing ? 'Menyimpan…' : 'Simpan venue' }}
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
