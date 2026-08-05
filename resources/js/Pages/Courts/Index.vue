<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import Input from '@/Components/ui/Input.vue';
import { Pencil, Plus, Ruler, Search, Trash2 } from '@lucide/vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    courts: Array<{ id: number; name: string; price_per_hour: string; status: string; venue: { name: string } }>;
}>();

const search = ref('');
const filtered = computed(() =>
    props.courts.filter(
        (c) => !search.value || `${c.name} ${c.venue.name}`.toLowerCase().includes(search.value.toLowerCase()),
    ),
);

const statusVariant = (s: string): 'success' | 'warning' | 'muted' =>
    ({ available: 'success', maintenance: 'warning', inactive: 'muted' } as Record<string, 'success' | 'warning' | 'muted'>)[s] ?? 'muted';

const fmt = (n: string) => Number(n).toLocaleString('id-ID');

const dialog = ref<{ open: boolean; id: number; label: string }>({ open: false, id: 0, label: '' });
const askDelete = (c: { id: number; name: string }) => {
    dialog.value = { open: true, id: c.id, label: c.name };
};
const confirmDelete = () => {
    router.delete(route('courts.destroy', dialog.value.id));
    dialog.value.open = false;
};
</script>

<template>
    <Head title="Courts" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-foreground">Courts</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Kelola lapangan dan tarif per jam.</p>
                </div>
                <Button as="link" :href="route('courts.create')" variant="accent">
                    <Plus class="h-4 w-4" />
                    Tambah lapangan
                </Button>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="relative max-w-md">
                    <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="search" type="search" placeholder="Cari lapangan atau venue…" class="pl-10" />
                </div>

                <div v-if="filtered.length" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="c in filtered" :key="c.id" class="transition hover:-translate-y-0.5 hover:shadow-md">
                        <CardContent class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-accent/10 text-accent">
                                    <Ruler class="h-6 w-6" />
                                </div>
                                <Badge :variant="statusVariant(c.status)">{{ c.status }}</Badge>
                            </div>

                            <h3 class="mt-4 text-lg font-bold text-foreground">{{ c.name }}</h3>
                            <p class="mt-1 text-sm text-muted-foreground">{{ c.venue.name }}</p>

                            <div class="mt-5 flex items-end justify-between border-t border-border pt-4">
                                <div>
                                    <p class="text-xs text-muted-foreground">Tarif / jam</p>
                                    <p class="text-xl font-extrabold text-accent">Rp {{ fmt(c.price_per_hour) }}</p>
                                </div>
                            </div>

                            <div class="mt-4 flex gap-2">
                                <Button as="link" :href="route('courts.edit', c.id)" variant="outline" size="sm" class="flex-1">
                                    <Pencil class="h-3.5 w-3.5" />
                                    Edit
                                </Button>
                                <Button variant="outline" size="sm" class="flex-1 border-destructive/30 text-destructive hover:bg-destructive/5" @click="askDelete(c)">
                                    <Trash2 class="h-3.5 w-3.5" />
                                    Hapus
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card v-else class="border-dashed">
                    <CardContent class="flex flex-col items-center py-16 text-center">
                        <p class="font-semibold text-foreground">Tidak ada lapangan ditemukan</p>
                        <p class="mt-1 text-sm text-muted-foreground">Coba kata kunci lain atau tambah lapangan baru.</p>
                    </CardContent>
                </Card>
            </div>
        </div>

        <ConfirmDialog
            v-model:open="dialog.open"
            title="Hapus lapangan"
            :description="`Yakin ingin menghapus ${dialog.label}? Tindakan ini tidak bisa dibatalkan.`"
            confirm-label="Hapus"
            destructive
            @confirm="confirmDelete"
        />
    </AuthenticatedLayout>
</template>
