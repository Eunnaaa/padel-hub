<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Badge from '@/Components/ui/Badge.vue';
import { ArrowLeft, ChevronRight, Clock, MapPin, Star, Users } from '@lucide/vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    venue: {
        id: number;
        name: string;
        description: string | null;
        address: string;
        city: string;
        rating: string;
        settings: Record<string, unknown> | null;
        courts: Array<{
            id: number;
            name: string;
            type: string;
            price_per_hour: string;
            peak_price_per_hour: string | null;
            status: string;
            maintenance_from: string | null;
            maintenance_to: string | null;
        }>;
    };
}>();

const fmt = (n: string | null) => (n === null ? '—' : `Rp ${Number(n).toLocaleString('id-ID')}`);
const typeLabel = (t: string) => (t === 'indoor' ? 'Indoor' : 'Outdoor');
const maintenance = (c: (typeof props.venue.courts)[number]) =>
    c.maintenance_from ? `${c.maintenance_from.slice(0, 5)}–${c.maintenance_to?.slice(0, 5)}` : null;
const courtStatus = (c: (typeof props.venue.courts)[number]) =>
    c.status === 'maintenance' ? 'maintenance' : 'ready';
</script>

<template>
    <Head :title="`${venue.name} — PadelHub`" />

    <div class="min-h-screen bg-brand text-white">
        <div
            class="pointer-events-none fixed inset-0 opacity-[0.07]"
            style="background-image: radial-gradient(#22C55E 1px, transparent 1px); background-size: 28px 28px"
        />

        <header class="relative z-20 border-b border-white/5">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
                <Link href="/" class="inline-flex items-center gap-2.5">
                    <ApplicationLogo class="h-8 w-8 text-white" />
                    <span class="text-lg font-bold tracking-tight">PadelHub</span>
                </Link>

                <div class="flex items-center gap-2">
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="route('dashboard')"
                            class="inline-flex items-center gap-1.5 rounded-full bg-brand-accent px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-500"
                        >
                            Dashboard
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="hidden rounded-full px-4 py-2 text-sm font-medium text-white/70 transition hover:text-white sm:inline-flex"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="inline-flex items-center gap-1.5 rounded-full bg-brand-accent px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-500"
                        >
                            Daftar gratis
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <main class="relative z-10 mx-auto max-w-4xl px-6 py-12">
            <Link
                :href="route('venues.explore')"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-white/50 transition hover:text-white"
            >
                <ArrowLeft class="h-4 w-4" />
                Semua venue
            </Link>

            <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-extrabold uppercase tracking-tight">{{ venue.name }}</h1>
                        <p class="mt-2 flex items-center gap-1.5 text-sm text-white/55">
                            <MapPin class="h-4 w-4" />
                            {{ venue.address }}, {{ venue.city }}
                        </p>
                        <div v-if="Number(venue.rating) > 0" class="mt-2 inline-flex items-center gap-1 rounded-full bg-brand-accent/15 px-2.5 py-1 text-xs font-semibold text-brand-accent">
                            <Star class="h-3 w-3 fill-current" />
                            {{ Number(venue.rating).toFixed(1) }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2 rounded-xl bg-white/5 px-4 py-2.5 text-sm text-white/60">
                        <Users class="h-4 w-4 text-brand-accent" />
                        {{ venue.courts.length }} lapangan
                    </div>
                </div>

                <p v-if="venue.description" class="mt-5 text-sm leading-relaxed text-white/60">{{ venue.description }}</p>
            </div>

            <h2 class="mt-10 text-xl font-bold uppercase tracking-tight">Pilih lapangan</h2>

            <div class="mt-5 space-y-4">
                <div
                    v-for="c in venue.courts"
                    :key="c.id"
                    class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-white/10 bg-white/5 p-6"
                >
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-lg font-bold">{{ c.name }}</p>
                            <Badge variant="outline" class="border-white/15 text-white/60">{{ typeLabel(c.type) }}</Badge>
                        </div>
                        <p v-if="maintenance(c)" class="mt-2 flex items-center gap-1.5 text-sm text-amber-300/80">
                            <Clock class="h-4 w-4" />
                            Maintenance {{ maintenance(c) }}
                        </p>
                    </div>

                    <div class="flex items-center gap-5">
                        <div class="text-right">
                            <p class="text-2xl font-extrabold text-brand-accent">{{ fmt(c.price_per_hour) }}</p>
                            <p class="text-xs text-white/40">per jam</p>
                        </div>
                        <Link
                            :href="route('bookings.create', { court_id: c.id })"
                            class="inline-flex items-center gap-1.5 rounded-full bg-brand-accent px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-green-500"
                            :class="courtStatus(c) === 'maintenance' ? 'pointer-events-none opacity-40' : ''"
                        >
                            Booking
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </div>
                </div>

                <p v-if="!venue.courts.length" class="rounded-2xl border border-dashed border-white/15 py-16 text-center text-white/50">
                    Belum ada lapangan tersedia.
                </p>
            </div>
        </main>
    </div>
</template>
