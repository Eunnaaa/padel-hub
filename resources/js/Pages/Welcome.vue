<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowDown,
    Building2,
    CalendarCheck,
    ChevronRight,
    Clock,
    Mail,
    MapPin,
    Phone,
    QrCode,
    Sparkles,
    Users,
    Wallet,
} from '@lucide/vue';

const props = defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    stats: { venues: number; courts: number; bookings: number; cities: number };
}>();

const fmt = (n: number) => n.toLocaleString('id-ID');

const features = [
    {
        icon: Clock,
        title: 'Booking dalam hitungan detik',
        desc: 'Pilih venue, lapangan, tanggal, dan slot — selesai di bawah 60 detik.',
    },
    {
        icon: CalendarCheck,
        title: 'Ketersediaan real-time',
        desc: 'Status lapangan selalu terbaru. Tidak ada double booking.',
    },
    {
        icon: Wallet,
        title: 'Pembayaran online',
        desc: 'QRIS, transfer bank, kartu kredit, e-wallet. Konfirmasi otomatis.',
    },
    {
        icon: QrCode,
        title: 'QR check-in',
        desc: 'Scan QR di venue. Masuk tanpa antre di front desk.',
    },
];
</script>

<template>
    <Head title="PadelHub — Booking lapangan padel modern" />

    <div class="min-h-screen bg-brand text-white">
        <!-- Ambient dots -->
        <div
            class="pointer-events-none fixed inset-0 opacity-[0.07]"
            style="background-image: radial-gradient(#22C55E 1px, transparent 1px); background-size: 28px 28px"
        />

        <!-- Nav -->
        <header class="relative z-20 border-b border-white/5">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
                <Link href="/" class="inline-flex items-center gap-2.5">
                    <ApplicationLogo class="h-8 w-8 text-white" />
                    <span class="text-lg font-bold tracking-tight">PadelHub</span>
                </Link>

                <nav class="hidden items-center gap-8 text-sm font-medium text-white/60 md:flex">
                    <a href="#about" class="transition hover:text-white">About</a>
                    <a href="#features" class="transition hover:text-white">Features</a>
                    <a href="#venues" class="transition hover:text-white">For Venues</a>
                </nav>

                <div v-if="canLogin" class="flex items-center gap-2">
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
                            v-if="canRegister"
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

        <!-- Hero -->
        <section class="relative z-10 flex min-h-[calc(100vh-4rem)] flex-col items-center justify-center px-6 py-20 text-center">
            <p class="mb-8 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1.5 text-xs font-medium uppercase tracking-widest text-white/60">
                <Sparkles class="h-3.5 w-3.5 text-brand-accent" />
                Indonesia's padel booking platform
            </p>

            <h1 class="max-w-4xl text-5xl font-extrabold uppercase leading-[0.95] tracking-tight sm:text-7xl lg:text-8xl">
                Everything<br />
                happens<br />
                <span class="text-brand-accent">courtside.</span>
            </h1>

            <p class="mt-8 max-w-lg text-base text-white/55 sm:text-lg">
                Temukan venue, pilih lapangan, bayar online, check-in lewat QR.
                Platform booking padel modern untuk pemain & venue di Indonesia.
            </p>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                <Link
                    :href="route('venues.explore')"
                    class="inline-flex items-center gap-2 rounded-full bg-brand-accent px-8 py-3.5 text-sm font-semibold text-white shadow-lg shadow-brand-accent/25 transition hover:bg-green-500"
                >
                    Mulai booking
                    <ChevronRight class="h-4 w-4" />
                </Link>
                <a
                    href="#features"
                    class="inline-flex items-center gap-2 rounded-full border border-white/15 px-8 py-3.5 text-sm font-medium text-white/80 transition hover:bg-white/5 hover:text-white"
                >
                    Lihat fitur
                </a>
            </div>

            <a href="#stats" class="mt-16 flex flex-col items-center gap-2 text-xs font-medium uppercase tracking-widest text-white/40 transition hover:text-white/70">
                Scroll for more
                <ArrowDown class="h-5 w-5 animate-bounce" />
            </a>
        </section>

        <!-- Stats -->
        <section id="stats" class="relative z-10 border-y border-white/10 bg-brand/80 py-16 backdrop-blur">
            <div class="mx-auto grid max-w-5xl grid-cols-2 gap-8 px-6 sm:grid-cols-4">
                <div class="text-center">
                    <p class="text-4xl font-extrabold tracking-tight text-brand-accent sm:text-5xl">{{ fmt(stats.venues) }}+</p>
                    <p class="mt-2 text-xs font-medium uppercase tracking-widest text-white/45">Venues</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-extrabold tracking-tight text-brand-accent sm:text-5xl">{{ fmt(stats.courts) }}+</p>
                    <p class="mt-2 text-xs font-medium uppercase tracking-widest text-white/45">Courts</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-extrabold tracking-tight text-brand-accent sm:text-5xl">{{ fmt(stats.bookings) }}+</p>
                    <p class="mt-2 text-xs font-medium uppercase tracking-widest text-white/45">Bookings</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-extrabold tracking-tight text-brand-accent sm:text-5xl">{{ fmt(stats.cities) }}+</p>
                    <p class="mt-2 text-xs font-medium uppercase tracking-widest text-white/45">Cities</p>
                </div>
            </div>
        </section>

        <!-- About -->
        <section id="about" class="relative z-10 px-6 py-24">
            <div class="mx-auto max-w-4xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">This is PadelHub</p>
                <h2 class="mt-4 text-4xl font-extrabold uppercase leading-tight tracking-tight sm:text-5xl">
                    By padel players,<br />for the Indonesian market.
                </h2>
                <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-white/55 sm:text-lg">
                    Platform booking lapangan padel yang komprehensif dan sederhana —
                    untuk setiap court, ada PadelHub. Booking real-time, pembayaran online,
                    QR check-in, dan dashboard operasional venue dalam satu tempat.
                </p>
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="relative z-10 border-t border-white/10 bg-white px-6 py-24 text-brand">
            <div class="mx-auto max-w-6xl">
                <div class="text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">What you can do</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">
                        Semua yang kamu butuhkan
                    </h2>
                    <p class="mt-3 text-muted-foreground">Platform lengkap untuk pemain dan venue.</p>
                </div>

                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="f in features"
                        :key="f.title"
                        class="group rounded-2xl border border-brand-border bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:border-brand-accent/30 hover:shadow-lg"
                    >
                        <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-brand-accent/10 text-brand-accent transition group-hover:bg-brand-accent group-hover:text-white">
                            <component :is="f.icon" class="h-5 w-5" />
                        </div>
                        <h3 class="text-base font-bold">{{ f.title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ f.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- For venues -->
        <section id="venues" class="relative z-10 border-t border-white/10 px-6 py-24">
            <div class="mx-auto grid max-w-6xl items-center gap-12 lg:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">The Panel</p>
                    <h2 class="mt-4 text-4xl font-extrabold uppercase leading-tight tracking-tight sm:text-5xl">
                        Management<br />without the fuss.
                    </h2>
                    <p class="mt-6 max-w-md text-base leading-relaxed text-white/55">
                        Dari booking online hingga revenue analytics — kelola venue
                        seperti mesin yang well-oiled. Tingkatkan okupansi, pantau pendapatan,
                        check-in pemain lewat QR.
                    </p>
                    <ul class="mt-8 space-y-3">
                        <li v-for="item in ['Court & pricing management', 'Real-time occupancy', 'Revenue analytics', 'QR staff check-in']" :key="item" class="flex items-center gap-3 text-sm text-white/70">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-accent/20 text-brand-accent">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            {{ item }}
                        </li>
                    </ul>
                    <Link
                        :href="canLogin ? route('register') : '#'"
                        class="mt-10 inline-flex items-center gap-2 rounded-full bg-brand-accent px-7 py-3 text-sm font-semibold text-white transition hover:bg-green-500"
                    >
                        Daftarkan venue
                        <ChevronRight class="h-4 w-4" />
                    </Link>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                            <Building2 class="h-6 w-6 text-brand-accent" />
                            <p class="mt-4 text-3xl font-bold">{{ fmt(stats.venues) }}</p>
                            <p class="mt-1 text-xs uppercase tracking-widest text-white/40">Active venues</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                            <Users class="h-6 w-6 text-brand-accent" />
                            <p class="mt-4 text-3xl font-bold">{{ fmt(stats.bookings) }}</p>
                            <p class="mt-1 text-xs uppercase tracking-widest text-white/40">Total bookings</p>
                        </div>
                    </div>
                    <div class="mt-8 space-y-4">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                            <MapPin class="h-6 w-6 text-brand-accent" />
                            <p class="mt-4 text-3xl font-bold">{{ fmt(stats.cities) }}</p>
                            <p class="mt-1 text-xs uppercase tracking-widest text-white/40">Cities covered</p>
                        </div>
                        <div class="rounded-2xl border border-brand-accent/30 bg-brand-accent/10 p-6">
                            <p class="text-sm font-medium text-brand-accent">Okupansi naik</p>
                            <p class="mt-2 text-4xl font-extrabold text-white">+30%</p>
                            <p class="mt-1 text-xs text-white/50">Target setelah live</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="relative z-10 border-t border-white/10 px-6 py-24 text-center">
            <h2 class="text-4xl font-extrabold uppercase tracking-tight sm:text-5xl">
                Ready to play?
            </h2>
            <p class="mx-auto mt-4 max-w-md text-white/55">
                Daftar gratis. Booking lapangan padel favoritmu dalam hitungan detik.
            </p>
            <Link
                :href="canLogin ? route('register') : '#'"
                class="mt-8 inline-flex items-center gap-2 rounded-full bg-brand-accent px-10 py-4 text-base font-semibold text-white shadow-lg shadow-brand-accent/25 transition hover:bg-green-500"
            >
                Daftar sekarang
                <ChevronRight class="h-5 w-5" />
            </Link>
        </section>

        <!-- Footer -->
        <footer class="relative z-10 border-t border-white/10 bg-black/40 px-6 py-16">
            <div class="mx-auto grid max-w-6xl gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="inline-flex items-center gap-2.5">
                        <ApplicationLogo class="h-8 w-8 text-white" />
                        <span class="text-lg font-bold tracking-tight">PadelHub</span>
                    </div>
                    <p class="mt-4 text-sm leading-relaxed text-white/45">
                        Platform booking padel modern untuk Indonesia.
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-white/40">Visit us</p>
                    <p class="mt-3 flex items-start gap-2 text-sm text-white/60">
                        <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-brand-accent" />
                        Jakarta, Indonesia
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-white/40">Contact</p>
                    <p class="mt-3 flex items-center gap-2 text-sm text-white/60">
                        <Mail class="h-4 w-4 text-brand-accent" />
                        hello@padelhub.id
                    </p>
                    <p class="mt-2 flex items-center gap-2 text-sm text-white/60">
                        <Phone class="h-4 w-4 text-brand-accent" />
                        +62 800-0000-000
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-white/40">Product</p>
                    <ul class="mt-3 space-y-2 text-sm text-white/60">
                        <li><a href="#features" class="transition hover:text-white">Features</a></li>
                        <li><a href="#venues" class="transition hover:text-white">For Venues</a></li>
                        <li>
                            <Link :href="route('login')" class="transition hover:text-white">Log in</Link>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mx-auto mt-12 flex max-w-6xl flex-wrap items-center justify-between gap-4 border-t border-white/10 pt-8 text-xs text-white/30">
                <p>© {{ new Date().getFullYear() }} PadelHub. All rights reserved.</p>
                <div class="flex gap-4">
                    <span>Terms</span>
                    <span>Privacy</span>
                </div>
            </div>
        </footer>
    </div>
</template>
