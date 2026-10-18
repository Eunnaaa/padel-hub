<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ArrowLeft, Building2, CheckCircle2 } from '@lucide/vue';
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
    <Head title="Tambah Venue — PadelHub" />
    <AuthenticatedLayout :dark="true">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-400">
                        <Building2 class="h-3.5 w-3.5" />
                        <span>PENDAFTARAN FASILITAS</span>
                    </div>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">Tambah Venue Baru</h2>
                    <p class="mt-1 text-sm text-slate-400">Daftarkan venue lapangan padel baru beserta alamat dan koordinat peta.</p>
                </div>
                <Link
                    :href="route('venues.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-xs font-semibold text-slate-300 transition hover:bg-slate-700 hover:text-white"
                >
                    <ArrowLeft class="h-4 w-4" />
                    <span>Kembali ke Daftar</span>
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md sm:p-8">
                    <form class="space-y-6" @submit.prevent="submit">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Nama Venue *</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    @input="onName"
                                    placeholder="Contoh: PadelHub Arena Senayan"
                                    class="mt-2 flex h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3.5 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                />
                                <p v-if="err('name')" class="mt-1.5 text-xs font-semibold text-rose-400">{{ err('name') }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Slug URL *</label>
                                <input
                                    id="slug"
                                    v-model="form.slug"
                                    @input="slugTouched = true"
                                    placeholder="padelhub-arena-senayan"
                                    class="mt-2 flex h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3.5 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                />
                                <p v-if="err('slug')" class="mt-1.5 text-xs font-semibold text-rose-400">{{ err('slug') }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Kota *</label>
                                <input
                                    id="city"
                                    v-model="form.city"
                                    placeholder="Jakarta Selatan"
                                    class="mt-2 flex h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3.5 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                />
                                <p v-if="err('city')" class="mt-1.5 text-xs font-semibold text-rose-400">{{ err('city') }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Status Operasional</label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="mt-2 flex h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 [&>option]:bg-slate-900 [&>option]:text-white"
                                >
                                    <option value="pending">Menunggu (Pending)</option>
                                    <option value="approved">Disetujui (Approved)</option>
                                    <option value="rejected">Ditolak (Rejected)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Alamat Lengkap *</label>
                            <input
                                id="address"
                                v-model="form.address"
                                placeholder="Jl. Pintu Satu Senayan No. 1A"
                                class="mt-2 flex h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3.5 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            />
                            <p v-if="err('address')" class="mt-1.5 text-xs font-semibold text-rose-400">{{ err('address') }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Deskripsi Venue</label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                placeholder="Fasilitas lapangan panoramic WPT, loker, shower air panas, pro shop, dan café…"
                                class="mt-2 flex w-full rounded-xl border border-slate-800 bg-slate-950/80 p-3.5 text-xs font-semibold text-white placeholder-slate-400 shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            />
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Latitude</label>
                                <input
                                    id="latitude"
                                    v-model="form.latitude"
                                    type="number"
                                    step="any"
                                    placeholder="-6.2185"
                                    class="mt-2 flex h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3.5 text-xs font-semibold text-white placeholder-slate-400 shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                />
                                <p v-if="err('latitude')" class="mt-1.5 text-xs font-semibold text-rose-400">{{ err('latitude') }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Longitude</label>
                                <input
                                    id="longitude"
                                    v-model="form.longitude"
                                    type="number"
                                    step="any"
                                    placeholder="106.8025"
                                    class="mt-2 flex h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3.5 text-xs font-semibold text-white placeholder-slate-400 shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                />
                                <p v-if="err('longitude')" class="mt-1.5 text-xs font-semibold text-rose-400">{{ err('longitude') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 border-t border-slate-800/80 pt-6">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-2.5 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition hover:scale-105 disabled:opacity-50"
                            >
                                <CheckCircle2 class="h-4 w-4" />
                                <span>{{ form.processing ? 'Menyimpan…' : 'Simpan Venue' }}</span>
                            </button>
                            <Link :href="route('venues.index')" class="text-xs font-semibold text-slate-400 transition hover:text-white">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
