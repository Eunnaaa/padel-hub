<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import PadelCourtBoard from '@/Components/PadelCourtBoard.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowDown,
    Building2,
    CalendarCheck,
    ChevronRight,
    Clock,
    Mail,
    MapPin,
    Menu,
    Phone,
    QrCode,
    Sparkles,
    Users,
    Wallet,
    X,
} from '@lucide/vue';
import { ref } from 'vue';

const props = defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    stats: { venues: number; courts: number; bookings: number; cities: number };
    selectedDate?: string;
    todayDate?: string;
    bookings?: Array<{
        court_id: number;
        start_time: string;
        end_time: string;
        status: string;
    }>;
    featuredCourts?: Array<{
        id: number;
        name: string;
        type: string;
        price_per_hour: string;
        status: string;
        maintenance_from: string | null;
        maintenance_to: string | null;
        venue: {
            id: number;
            name: string;
            city: string;
            slug: string;
            rating: string;
        };
    }>;
}>();

const fmt = (n: number) => n.toLocaleString('id-ID');

const mobileMenuOpen = ref(false);

const features = [
    {
        icon: Clock,
        title: 'Booking dalam Hitungan Detik',
        desc: 'Pilih venue, lapangan, tanggal, dan slot — selesai di bawah 60 detik.',
    },
    {
        icon: CalendarCheck,
        title: 'Ketersediaan Real-Time',
        desc: 'Status lapangan selalu terbaru dengan anti-tabrakan slot otomatis.',
    },
    {
        icon: Wallet,
        title: 'Pembayaran Online Instan',
        desc: 'QRIS, transfer bank, kartu kredit, e-wallet. Konfirmasi otomatis.',
    },
    {
        icon: QrCode,
        title: 'QR Digital Match Pass',
        desc: 'Scan QR di venue. Masuk tanpa antre di front desk lapangan.',
    },
];
</script>

<template>
    <Head title="PadelHub — Booking Lapangan Padel Modern Indonesia" />

    <div class="relative min-h-screen bg-slate-950 font-sans text-slate-100 selection:bg-emerald-500/25 selection:text-emerald-300">
        <!-- Ambient Sports Lighting Gradients & Watermark Grid -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute -top-40 -left-40 h-[500px] w-[500px] rounded-full bg-emerald-500/15 blur-[120px]" />
            <div class="absolute top-1/2 -right-40 h-[600px] w-[600px] rounded-full bg-green-600/10 blur-[150px]" />
            <div class="absolute -bottom-40 left-1/3 h-[450px] w-[450px] rounded-full bg-teal-500/10 blur-[130px]" />
            <!-- Dot Grid Overlay -->
            <div
                class="absolute inset-0 opacity-[0.06]"
                style="background-image: radial-gradient(#22c55e 1px, transparent 1px); background-size: 28px 28px;"
            />
        </div>

        <!-- Sticky Nav -->
        <header class="sticky top-0 z-30 border-b border-slate-800/80 bg-slate-950/80 backdrop-blur-xl">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" class="group inline-flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-green-600 p-2 shadow-lg shadow-emerald-500/20 transition-transform group-hover:scale-105">
                        <ApplicationLogo class="h-full w-full text-slate-950" />
                    </div>
                    <div>
                        <span class="text-xl font-extrabold tracking-tight text-white">Padel<span class="text-emerald-400">Hub</span></span>
                        <span class="ms-2 hidden rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold tracking-wider text-emerald-400 uppercase sm:inline-block">Indonesia</span>
                    </div>
                </Link>

                <nav class="hidden items-center gap-1.5 text-xs font-semibold text-slate-300 md:flex">
                    <a href="#courts" class="rounded-xl px-3.5 py-2 transition hover:bg-slate-900 hover:text-white">Courts</a>
                    <Link :href="route('venues.explore')" class="rounded-xl px-3.5 py-2 transition hover:bg-slate-900 hover:text-white">Cari Venue</Link>
                    <a href="#about" class="rounded-xl px-3.5 py-2 transition hover:bg-slate-900 hover:text-white">Tentang</a>
                    <a href="#features" class="rounded-xl px-3.5 py-2 transition hover:bg-slate-900 hover:text-white">Fitur</a>
                    <a href="#venues" class="rounded-xl px-3.5 py-2 transition hover:bg-slate-900 hover:text-white">Mitra Venue</a>
                </nav>

                <div class="flex items-center gap-2.5">
                    <div v-if="canLogin" class="hidden sm:flex sm:items-center sm:gap-2">
                        <template v-if="$page.props.auth.user">
                            <Link
                                :href="route('dashboard')"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-4 py-2 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition hover:scale-105"
                            >
                                <span>Dashboard User</span>
                                <ChevronRight class="h-4 w-4" />
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="rounded-xl border border-slate-800 bg-slate-900/60 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:bg-slate-800 hover:text-white"
                            >
                                Masuk
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-4 py-2 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition hover:scale-105"
                            >
                                <span>Daftar Gratis</span>
                                <ChevronRight class="h-4 w-4" />
                            </Link>
                        </template>
                    </div>

                    <!-- Mobile menu button -->
                    <button
                        type="button"
                        aria-label="Buka menu navigasi"
                        class="rounded-xl border border-slate-800 bg-slate-900 p-2 text-slate-400 hover:bg-slate-800 hover:text-white md:hidden"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
                        <X v-else class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div
                v-if="mobileMenuOpen"
                class="border-t border-slate-800 bg-slate-950/95 px-6 py-5 backdrop-blur-2xl md:hidden"
            >
                <nav class="space-y-2 text-sm font-semibold">
                    <a
                        href="#courts"
                        class="block rounded-xl px-3 py-2 text-slate-300 transition hover:bg-slate-900 hover:text-white"
                        @click="mobileMenuOpen = false"
                    >
                        Courts
                    </a>
                    <Link
                        :href="route('venues.explore')"
                        class="block rounded-xl px-3 py-2 text-slate-300 transition hover:bg-slate-900 hover:text-white"
                        @click="mobileMenuOpen = false"
                    >
                        Cari Venue
                    </Link>
                    <a
                        href="#about"
                        class="block rounded-xl px-3 py-2 text-slate-300 transition hover:bg-slate-900 hover:text-white"
                        @click="mobileMenuOpen = false"
                    >
                        Tentang
                    </a>
                    <a
                        href="#features"
                        class="block rounded-xl px-3 py-2 text-slate-300 transition hover:bg-slate-900 hover:text-white"
                        @click="mobileMenuOpen = false"
                    >
                        Fitur
                    </a>
                    <a
                        href="#venues"
                        class="block rounded-xl px-3 py-2 text-slate-300 transition hover:bg-slate-900 hover:text-white"
                        @click="mobileMenuOpen = false"
                    >
                        Mitra Venue
                    </a>
                </nav>

                <div class="mt-5 border-t border-slate-800 pt-4">
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="route('dashboard')"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 py-3 text-center text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20"
                            @click="mobileMenuOpen = false"
                        >
                            <span>Buka Dashboard</span>
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </template>
                    <template v-else>
                        <div class="grid grid-cols-2 gap-2">
                            <Link
                                :href="route('login')"
                                class="rounded-xl border border-slate-800 bg-slate-900/80 py-2.5 text-center text-xs font-semibold text-slate-200 transition hover:bg-slate-800 hover:text-white"
                                @click="mobileMenuOpen = false"
                            >
                                Masuk
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 py-2.5 text-center text-xs font-bold text-slate-950 transition hover:scale-105"
                                @click="mobileMenuOpen = false"
                            >
                                Daftar
                            </Link>
                        </div>
                    </template>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative z-10 flex min-h-[calc(100vh-4rem)] flex-col items-center justify-center px-4 py-20 text-center sm:px-6">
            <!-- Eyebrow Badge -->
            <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-emerald-400 shadow-inner">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                </span>
                <Sparkles class="h-3.5 w-3.5 text-emerald-400" />
                <span>INDONESIA'S PADEL BOOKING PLATFORM</span>
            </div>

            <!-- Bold Headline with Gradient Accent -->
            <h1 class="max-w-4xl text-5xl font-black uppercase leading-[0.92] tracking-tight text-white sm:text-7xl lg:text-8xl">
                Everything<br />
                happens<br />
                <span class="bg-gradient-to-r from-emerald-400 via-green-400 to-teal-300 bg-clip-text text-transparent">courtside.</span>
            </h1>

            <p class="mt-8 max-w-xl text-base leading-relaxed text-slate-400 sm:text-lg">
                Temukan venue, pilih lapangan, bayar online, check-in lewat QR pass.
                Platform booking padel modern untuk pemain & venue di Indonesia.
            </p>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-3.5">
                <Link
                    :href="route('venues.explore')"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-8 py-3.5 text-sm font-bold text-slate-950 shadow-xl shadow-emerald-500/25 transition hover:scale-105 hover:from-emerald-400 hover:to-green-500"
                >
                    <span>Mulai Booking</span>
                    <ChevronRight class="h-4 w-4" />
                </Link>
                <a
                    href="#features"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-900/80 px-8 py-3.5 text-sm font-semibold text-slate-200 transition hover:bg-slate-800 hover:text-white"
                >
                    Lihat Fitur
                </a>
            </div>

            <a href="#stats" class="mt-16 flex flex-col items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-500 transition hover:text-emerald-400">
                <span>Eksplorasi Lebih Lanjut</span>
                <ArrowDown class="h-4 w-4 animate-bounce" />
            </a>
        </section>

        <!-- Stats Section -->
        <section id="stats" class="relative z-10 border-y border-slate-800/80 bg-slate-900/60 py-16 backdrop-blur-md">
            <div class="mx-auto grid max-w-6xl grid-cols-2 gap-8 px-6 sm:grid-cols-4">
                <div class="text-center">
                    <p class="text-4xl font-black tracking-tight text-emerald-400 sm:text-5xl">{{ fmt(stats.venues) }}+</p>
                    <p class="mt-2 text-xs font-bold uppercase tracking-widest text-slate-400">Mitra Venues</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-black tracking-tight text-emerald-400 sm:text-5xl">{{ fmt(stats.courts) }}+</p>
                    <p class="mt-2 text-xs font-bold uppercase tracking-widest text-slate-400">Lapangan Padel</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-black tracking-tight text-emerald-400 sm:text-5xl">{{ fmt(stats.bookings) }}+</p>
                    <p class="mt-2 text-xs font-bold uppercase tracking-widest text-slate-400">Sesi Terjadwal</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-black tracking-tight text-emerald-400 sm:text-5xl">{{ fmt(stats.cities) }}+</p>
                    <p class="mt-2 text-xs font-bold uppercase tracking-widest text-slate-400">Kota di Indonesia</p>
                </div>
            </div>
        </section>

        <!-- Courts Showcase Section: THE OFFICIAL PADEL COURT BOARD -->
        <section id="courts" class="relative z-10 border-b border-slate-800/80 px-4 py-24 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-6xl space-y-6">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-400">
                        <Sparkles class="h-3.5 w-3.5" />
                        <span>PETA LAPANGAN INTERAKTIF</span>
                    </div>
                    <h2 class="mt-2 text-3xl font-black uppercase tracking-tight text-white sm:text-4xl">
                        Simulasi Visual Lapangan & Slot Ketersediaan
                    </h2>
                    <p class="mt-2 text-sm text-slate-400 max-w-2xl">
                        Setiap kotak mewakili slot waktu 1 jam pada denah lapangan standar internasional. Klik kotak hijau untuk langsung booking slot favoritmu.
                    </p>
                </div>

                <!-- Reusable Official Padel Court Board Component -->
                <PadelCourtBoard
                    :courts="featuredCourts ?? []"
                    :bookings="bookings ?? []"
                    :initial-date="selectedDate ?? todayDate"
                />

                <!-- Explore all venues footer banner -->
                <div class="flex flex-col items-center justify-between gap-4 rounded-3xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-xl backdrop-blur-md sm:flex-row sm:px-8">
                    <div class="text-center sm:text-left">
                        <p class="text-sm font-bold text-white">Ingin bermain di venue atau kota lain?</p>
                        <p class="text-xs text-slate-400">Temukan puluhan venue dan lapangan padel terverifikasi di seluruh Indonesia.</p>
                    </div>
                    <Link
                        :href="route('venues.explore')"
                        class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-slate-700 bg-slate-800/80 px-5 py-2.5 text-xs font-bold text-slate-200 transition hover:border-emerald-500/50 hover:bg-slate-800 hover:text-white"
                    >
                        <span>Lihat Semua Venue</span>
                        <ChevronRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="relative z-10 px-4 py-24 sm:px-6">
            <div class="mx-auto max-w-4xl text-center">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-400">Komunitas & Standar Industri</p>
                <h2 class="mt-3 text-4xl font-black uppercase leading-tight tracking-tight text-white sm:text-5xl">
                    By padel players,<br />for the Indonesian market.
                </h2>
                <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-slate-400 sm:text-lg">
                    Platform booking lapangan padel yang komprehensif dan sederhana —
                    untuk setiap court, ada PadelHub. Booking real-time, pembayaran online,
                    QR check-in, dan dashboard operasional venue dalam satu tempat.
                </p>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="relative z-10 border-t border-slate-800/80 px-4 py-24 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-6xl">
                <div class="text-center">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-400">Fitur Utama Platform</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-white sm:text-4xl">
                        Semua yang Kamu Butuhkan
                    </h2>
                    <p class="mt-2 text-sm text-slate-400 sm:text-base">Pengalaman bermain padel tanpa repot untuk pemain dan pengelola venue.</p>
                </div>

                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="f in features"
                        :key="f.title"
                        class="group rounded-3xl border border-slate-800/80 bg-slate-900/60 p-7 shadow-xl backdrop-blur-md transition duration-300 hover:-translate-y-1 hover:border-emerald-500/40 hover:bg-slate-900/90 hover:shadow-2xl hover:shadow-emerald-950/20"
                    >
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/15 text-emerald-400 transition duration-300 group-hover:bg-gradient-to-br group-hover:from-emerald-400 group-hover:to-green-600 group-hover:text-slate-950 shadow-md">
                            <component :is="f.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="text-base font-bold text-white">{{ f.title }}</h3>
                        <p class="mt-2 text-xs leading-relaxed text-slate-400">{{ f.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- For Venues Section -->
        <section id="venues" class="relative z-10 border-t border-slate-800/80 px-4 py-24 sm:px-6 lg:px-8">
            <div class="mx-auto grid max-w-6xl items-center gap-12 lg:grid-cols-2">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-400">Panel Pengelola</p>
                    <h2 class="mt-3 text-4xl font-black uppercase leading-tight tracking-tight text-white sm:text-5xl">
                        Manajemen Venue<br />Tanpa Ribet.
                    </h2>
                    <p class="mt-5 max-w-md text-sm leading-relaxed text-slate-400">
                        Dari booking online hingga revenue analytics — kelola venue
                        secara efisien. Tingkatkan okupansi, pantau pendapatan harian,
                        dan check-in pemain lewat QR pass.
                    </p>
                    <ul class="mt-8 space-y-3">
                        <li v-for="item in ['Manajemen lapangan & tarif fleksibel', 'Okupansi real-time anti-tabrakan', 'Grafik analitik pendapatan 7 hari', 'Verifikasi pemain QR scan instan']" :key="item" class="flex items-center gap-3 text-xs font-semibold text-slate-300">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            {{ item }}
                        </li>
                    </ul>
                    <Link
                        :href="canLogin ? route('register') : '#'"
                        class="mt-9 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-7 py-3 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition hover:scale-105"
                    >
                        <span>Daftarkan Venue Sekarang</span>
                        <ChevronRight class="h-4 w-4" />
                    </Link>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-xl backdrop-blur-md">
                            <Building2 class="h-6 w-6 text-emerald-400" />
                            <p class="mt-4 text-3xl font-black text-white">{{ fmt(stats.venues) }}</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-wider text-slate-400">Venue Aktif</p>
                        </div>
                        <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-xl backdrop-blur-md">
                            <Users class="h-6 w-6 text-emerald-400" />
                            <p class="mt-4 text-3xl font-black text-white">{{ fmt(stats.bookings) }}</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-wider text-slate-400">Total Booking</p>
                        </div>
                    </div>
                    <div class="mt-8 space-y-4">
                        <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-xl backdrop-blur-md">
                            <MapPin class="h-6 w-6 text-emerald-400" />
                            <p class="mt-4 text-3xl font-black text-white">{{ fmt(stats.cities) }}</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-wider text-slate-400">Kota Terjangkau</p>
                        </div>
                        <div class="rounded-3xl border border-emerald-500/30 bg-emerald-500/10 p-6 shadow-xl">
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-400">Okupansi Naik</p>
                            <p class="mt-2 text-4xl font-black text-white">+30%</p>
                            <p class="mt-1 text-xs text-slate-400">Rata-rata setelah bergabung</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative z-10 border-t border-slate-800/80 px-4 py-24 text-center sm:px-6">
            <h2 class="text-4xl font-black uppercase tracking-tight text-white sm:text-5xl">
                Siap Turun ke Lapangan?
            </h2>
            <p class="mx-auto mt-4 max-w-md text-sm text-slate-400">
                Daftar akun gratis sekarang. Booking lapangan padel favoritmu hanya dalam hitungan detik.
            </p>
            <Link
                :href="canLogin ? route('register') : '#'"
                class="mt-8 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-8 py-3.5 text-sm font-bold text-slate-950 shadow-xl shadow-emerald-500/25 transition hover:scale-105"
            >
                <span>Daftar Akun Gratis</span>
                <ChevronRight class="h-4 w-4" />
            </Link>
        </section>

        <!-- Footer -->
        <footer class="relative z-10 border-t border-slate-800/80 bg-slate-950/90 px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto grid max-w-6xl gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="inline-flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-green-600 p-1.5 shadow-md shadow-emerald-500/20">
                            <ApplicationLogo class="h-full w-full text-slate-950" />
                        </div>
                        <span class="text-lg font-black tracking-tight text-white">Padel<span class="text-emerald-400">Hub</span></span>
                    </div>
                    <p class="mt-4 text-xs leading-relaxed text-slate-400">
                        Platform ekosistem booking dan manajemen lapangan padel modern untuk Indonesia.
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Lokasi</p>
                    <p class="mt-3 flex items-start gap-2 text-xs text-slate-300">
                        <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-emerald-400" />
                        Jakarta & Bandung, Indonesia
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Kontak</p>
                    <p class="mt-3 flex items-center gap-2 text-xs text-slate-300">
                        <Mail class="h-4 w-4 text-emerald-400" />
                        support@padelhub.id
                    </p>
                    <p class="mt-2 flex items-center gap-2 text-xs text-slate-300">
                        <Phone class="h-4 w-4 text-emerald-400" />
                        +62 812-3456-7890
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Tautan Cepat</p>
                    <ul class="mt-3 space-y-2 text-xs text-slate-300">
                        <li><a href="#courts" class="transition hover:text-white">Denah Lapangan</a></li>
                        <li><a href="#features" class="transition hover:text-white">Fitur Lengkap</a></li>
                        <li><a href="#venues" class="transition hover:text-white">Mitra Venue</a></li>
                        <li>
                            <Link :href="route('login')" class="transition hover:text-white">Masuk Akun</Link>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mx-auto mt-12 flex max-w-6xl flex-wrap items-center justify-between gap-4 border-t border-slate-800/80 pt-8 text-xs text-slate-500">
                <p>© {{ new Date().getFullYear() }} PadelHub Indonesia. Hak Cipta Dilindungi.</p>
                <div class="flex gap-4">
                    <span>Syarat & Ketentuan</span>
                    <span>Kebijakan Privasi</span>
                </div>
            </div>
        </footer>
    </div>
</template>
