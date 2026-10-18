<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import { Building2, MapPin, Plus, Search, Star, Trash2 } from '@lucide/vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    venues: Array<{ id: number; name: string; city: string; address: string; status: string; rating: string }>;
}>();

const search = ref('');
const filtered = computed(() =>
    props.venues.filter(
        (v) => !search.value || `${v.name} ${v.city}`.toLowerCase().includes(search.value.toLowerCase()),
    ),
);

const statusVariant = (s: string): 'success' | 'warning' | 'destructive' | 'muted' =>
    ({ approved: 'success', pending: 'warning', rejected: 'destructive' } as Record<string, 'success' | 'warning' | 'destructive' | 'muted'>)[s] ?? 'muted';

const dialog = ref<{ open: boolean; id: number; label: string }>({ open: false, id: 0, label: '' });
const askDelete = (v: { id: number; name: string }) => {
    dialog.value = { open: true, id: v.id, label: v.name };
};
const confirmDelete = () => {
    router.delete(route('venues.destroy', dialog.value.id));
    dialog.value.open = false;
};
</script>

<template>
    <Head title="Manajemen Venue — PadelHub" />
    <AuthenticatedLayout :dark="true">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-400">
                        <Building2 class="h-3.5 w-3.5" />
                        <span>MANAJEMEN FASILITAS</span>
                    </div>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">Daftar Venue Padel</h2>
                    <p class="mt-1 text-sm text-slate-400">Kelola lokasi venue, status operasional, dan info lapangan.</p>
                </div>
                <Link
                    :href="route('venues.create')"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-4 py-2.5 text-xs font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:scale-105"
                >
                    <Plus class="h-4 w-4" />
                    <span>Tambah Venue Baru</span>
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Search Bar -->
                <div class="relative max-w-md">
                    <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Cari venue atau kota…"
                        class="h-10 w-full rounded-xl border border-slate-800 bg-slate-950/80 pl-9 pr-3 text-xs text-white placeholder-slate-400 shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    />
                </div>

                <div v-if="filtered.length" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="v in filtered"
                        :key="v.id"
                        class="group relative overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-xl backdrop-blur-md transition-all hover:border-emerald-500/40 hover:shadow-2xl hover:shadow-emerald-950/20"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 text-emerald-400 ring-1 ring-emerald-500/30">
                                <Building2 class="h-6 w-6" />
                            </div>
                            <Badge :variant="statusVariant(v.status)">{{ v.status }}</Badge>
                        </div>

                        <h3 class="mt-4 text-lg font-black text-white">{{ v.name }}</h3>
                        <p class="mt-1 flex items-center gap-1 text-xs text-slate-400">
                            <MapPin class="h-3.5 w-3.5 shrink-0 text-emerald-400" />
                            {{ v.address }}
                        </p>

                        <div class="mt-5 flex items-center justify-between border-t border-slate-800/80 pt-4 text-xs">
                            <span class="inline-flex items-center gap-1 font-bold text-white">
                                <Star class="h-3.5 w-3.5 fill-amber-400 text-amber-400" />
                                {{ Number(v.rating).toFixed(1) }}
                            </span>
                            <span class="rounded-md border border-slate-800 bg-slate-950/60 px-2 py-0.5 text-slate-400">{{ v.city }}</span>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <Link
                                :href="route('venues.edit', v.id)"
                                class="flex-1 rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-center text-xs font-semibold text-slate-200 transition hover:bg-slate-700 hover:text-white"
                            >
                                Edit
                            </Link>
                            <button
                                type="button"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-xs font-semibold text-rose-300 transition hover:bg-rose-500/20 hover:text-white"
                                @click="askDelete(v)"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                                <span>Hapus</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="rounded-3xl border border-dashed border-slate-800 bg-slate-900/40 py-16 text-center">
                    <p class="font-bold text-white">Tidak ada venue ditemukan</p>
                    <p class="mt-1 text-xs text-slate-400">Coba gunakan kata kunci lain atau tambahkan venue baru.</p>
                </div>
            </div>
        </div>

        <ConfirmDialog
            v-model:open="dialog.open"
            title="Hapus venue"
            :description="`Yakin ingin menghapus ${dialog.label}? Tindakan ini tidak bisa dibatalkan.`"
            confirm-label="Hapus"
            destructive
            @confirm="confirmDelete"
        />
    </AuthenticatedLayout>
</template>
