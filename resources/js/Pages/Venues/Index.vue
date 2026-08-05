<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/ui/Badge.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import Input from '@/Components/ui/Input.vue';
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
    <Head title="Venues" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-foreground">Venues</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Kelola semua venue lapangan padel.</p>
                </div>
                <Button as="link" :href="route('venues.create')" variant="accent">
                    <Plus class="h-4 w-4" />
                    Tambah venue
                </Button>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="relative max-w-md">
                    <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="search" type="search" placeholder="Cari venue atau kota…" class="pl-10" />
                </div>

                <div v-if="filtered.length" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="v in filtered" :key="v.id" class="transition hover:-translate-y-0.5 hover:shadow-md">
                        <CardContent class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                                    <Building2 class="h-6 w-6" />
                                </div>
                                <Badge :variant="statusVariant(v.status)">{{ v.status }}</Badge>
                            </div>

                            <h3 class="mt-4 text-lg font-bold text-foreground">{{ v.name }}</h3>
                            <p class="mt-1 flex items-center gap-1 text-sm text-muted-foreground">
                                <MapPin class="h-3.5 w-3.5 shrink-0" />
                                {{ v.address }}
                            </p>

                            <div class="mt-5 flex items-center justify-between border-t border-border pt-4">
                                <span class="inline-flex items-center gap-1 text-sm font-medium text-foreground">
                                    <Star class="h-4 w-4 fill-amber-400 text-amber-400" />
                                    {{ Number(v.rating).toFixed(1) }}
                                </span>
                                <span class="text-sm font-medium text-muted-foreground">{{ v.city }}</span>
                            </div>

                            <div class="mt-4 flex gap-2">
                                <Button as="link" :href="route('venues.edit', v.id)" variant="outline" size="sm" class="flex-1">
                                    Edit
                                </Button>
                                <Button variant="outline" size="sm" class="flex-1 border-destructive/30 text-destructive hover:bg-destructive/5" @click="askDelete(v)">
                                    <Trash2 class="h-3.5 w-3.5" />
                                    Hapus
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card v-else class="border-dashed">
                    <CardContent class="flex flex-col items-center py-16 text-center">
                        <p class="font-semibold text-foreground">Tidak ada venue ditemukan</p>
                        <p class="mt-1 text-sm text-muted-foreground">Coba kata kunci lain atau tambah venue baru.</p>
                    </CardContent>
                </Card>
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
