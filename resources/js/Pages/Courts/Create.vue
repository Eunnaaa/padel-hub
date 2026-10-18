<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ArrowLeft, CheckCircle2, Layers } from '@lucide/vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{ venues: Array<{ id: number; name: string }> }>();
const form = useForm({ venue_id: '', name: '', price_per_hour: '', status: 'available' });
const err = (k: string) => (form.errors as Record<string, string>)[k];
const submit = () => form.post(route('courts.store'));
</script>

<template>
    <Head title="Tambah Lapangan — PadelHub" />
    <AuthenticatedLayout :dark="true">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-400">
                        <Layers class="h-3.5 w-3.5" />
                        <span>FASILITAS LAPANGAN</span>
                    </div>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">Tambah Lapangan Baru</h2>
                    <p class="mt-1 text-sm text-slate-400">Daftarkan lapangan baru pada venue dan atur tarif per jam.</p>
                </div>
                <Link
                    :href="route('courts.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-xs font-semibold text-slate-300 transition hover:bg-slate-700 hover:text-white"
                >
                    <ArrowLeft class="h-4 w-4" />
                    <span>Kembali ke Daftar</span>
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md sm:p-8">
                    <form class="space-y-6" @submit.prevent="submit">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Venue *</label>
                            <select
                                id="venue_id"
                                v-model="form.venue_id"
                                class="mt-2 flex h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 [&>option]:bg-slate-900 [&>option]:text-white"
                            >
                                <option value="">Pilih Venue</option>
                                <option v-for="venue in venues" :key="venue.id" :value="venue.id">{{ venue.name }}</option>
                            </select>
                            <p v-if="err('venue_id')" class="mt-1.5 text-xs font-semibold text-rose-400">{{ err('venue_id') }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Nama Lapangan *</label>
                            <input
                                id="name"
                                v-model="form.name"
                                placeholder="Contoh: Court 1 Indoor Panoramik"
                                class="mt-2 flex h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3 text-xs font-semibold text-white placeholder-slate-400 shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            />
                            <p v-if="err('name')" class="mt-1.5 text-xs font-semibold text-rose-400">{{ err('name') }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Tarif Sewa per Jam (Rp) *</label>
                            <input
                                id="price"
                                v-model="form.price_per_hour"
                                type="number"
                                min="0"
                                step="1000"
                                placeholder="150000"
                                class="mt-2 flex h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3 text-xs font-semibold text-white placeholder-slate-400 shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            />
                            <p v-if="err('price_per_hour')" class="mt-1.5 text-xs font-semibold text-rose-400">{{ err('price_per_hour') }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Status Operasional</label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-2 flex h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 [&>option]:bg-slate-900 [&>option]:text-white"
                            >
                                <option value="available">Tersedia (Available)</option>
                                <option value="maintenance">Perawatan (Maintenance)</option>
                                <option value="inactive">Nonaktif (Inactive)</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-4 border-t border-slate-800/80 pt-6">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-2.5 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition hover:scale-105 disabled:opacity-50"
                            >
                                <CheckCircle2 class="h-4 w-4" />
                                <span>{{ form.processing ? 'Menyimpan…' : 'Simpan Lapangan' }}</span>
                            </button>
                            <Link :href="route('courts.index')" class="text-xs font-semibold text-slate-400 transition hover:text-white">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
