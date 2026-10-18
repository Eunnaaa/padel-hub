<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Clock,
    MapPin,
    QrCode,
    Sparkles,
    Star,
} from '@lucide/vue';
</script>

<template>
    <div class="relative min-h-screen bg-slate-950 font-sans text-slate-100 selection:bg-emerald-500/25 selection:text-emerald-300">
        <!-- Ambient Sports Lighting Gradients -->
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

        <!-- Sticky Header / Top Nav Bar -->
        <header class="relative z-20 border-b border-slate-800/60 bg-slate-950/70 backdrop-blur-md">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" class="group inline-flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-green-600 p-2 shadow-lg shadow-emerald-500/20 transition-transform group-hover:scale-105">
                        <ApplicationLogo class="h-full w-full text-slate-950" />
                    </div>
                    <div>
                        <span class="text-xl font-extrabold tracking-tight text-white">Padel<span class="text-emerald-400">Hub</span></span>
                        <span class="ms-2 hidden rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold tracking-wider text-emerald-400 uppercase sm:inline-block">Indonesia</span>
                    </div>
                </Link>

                <Link
                    href="/"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-900/60 px-3.5 py-1.5 text-xs font-semibold text-slate-300 transition-all hover:border-slate-700 hover:bg-slate-800 hover:text-white"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    <span>Kembali ke Beranda</span>
                </Link>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="relative z-10 flex min-h-[calc(100vh-4rem)] items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
            <div class="mx-auto grid w-full max-w-6xl grid-cols-1 items-center gap-12 lg:grid-cols-12">
                <!-- Left Showcase Panel (Hero Presentation) -->
                <div class="hidden lg:col-span-6 lg:flex lg:flex-col lg:space-y-8">
                    <!-- Eyebrow Badge -->
                    <div class="inline-flex items-center gap-2 self-start rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3.5 py-1.5 text-xs font-bold tracking-wide text-emerald-400 uppercase">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                        Komunitas Padel & Booking Terpadu
                    </div>

                    <!-- Bold Headline -->
                    <div>
                        <h1 class="text-4xl font-extrabold tracking-tight text-white xl:text-5xl">
                            Booking Cepat,<br />
                            <span class="bg-gradient-to-r from-emerald-400 via-green-400 to-teal-300 bg-clip-text text-transparent">
                                Langsung Siap Tanding.
                            </span>
                        </h1>
                        <p class="mt-4 max-w-lg text-base leading-relaxed text-slate-400">
                            Pilih lapangan indoor & outdoor favoritmu di Jakarta dan Bandung. Dapatkan slot real-time, bayar instan, dan masuk gate dengan QR match pass.
                        </p>
                    </div>

                    <!-- Padel Match Pass Graphic Showcase Card -->
                    <div class="relative overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-b from-slate-900/90 to-slate-950 p-6 shadow-2xl shadow-emerald-950/40 backdrop-blur-xl">
                        <!-- Court Line Graphic Watermark -->
                        <div class="pointer-events-none absolute -right-10 -bottom-10 h-44 w-44 rounded-full border border-emerald-500/10 bg-emerald-500/5" />
                        <div class="pointer-events-none absolute right-16 top-6 h-32 w-32 rounded-full border border-dashed border-emerald-500/15" />

                        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400">
                                    <Sparkles class="h-5 w-5" />
                                </div>
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Digital Match Pass</div>
                                    <div class="text-sm font-bold text-white">Court 1 (Panoramic Glass)</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/20 px-2.5 py-1 text-[11px] font-bold text-emerald-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                CONFIRMED
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-4 text-xs">
                            <div class="rounded-xl border border-slate-800/80 bg-slate-900/60 p-3">
                                <span class="mb-1 flex items-center gap-1.5 text-slate-400">
                                    <MapPin class="h-3.5 w-3.5 text-emerald-400" /> Venue
                                </span>
                                <span class="font-bold text-slate-200">PadelHub Center, Jakarta</span>
                            </div>
                            <div class="rounded-xl border border-slate-800/80 bg-slate-900/60 p-3">
                                <span class="mb-1 flex items-center gap-1.5 text-slate-400">
                                    <Clock class="h-3.5 w-3.5 text-emerald-400" /> Jam Main
                                </span>
                                <span class="font-bold text-emerald-300">19:00 - 20:00 WIB</span>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between rounded-xl border border-slate-800/50 bg-slate-800/40 px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <QrCode class="h-7 w-7 text-emerald-400" />
                                <div>
                                    <p class="text-[11px] font-bold text-white">Instant Check-in Ready</p>
                                    <p class="text-[10px] text-slate-400">Tunjukkan barcode saat tiba di lokasi</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] text-slate-400">Tarif Promo</div>
                                <div class="text-sm font-extrabold text-emerald-400">Rp 150.000</div>
                            </div>
                        </div>
                    </div>

                    <!-- Highlight Features -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-2xl border border-slate-800/80 bg-slate-900/40 p-3 text-center">
                            <div class="text-base font-extrabold text-emerald-400">07:00–22:00</div>
                            <div class="mt-0.5 text-[11px] text-slate-400">Live Slot View</div>
                        </div>
                        <div class="rounded-2xl border border-slate-800/80 bg-slate-900/40 p-3 text-center">
                            <div class="text-base font-extrabold text-white">5 Menit</div>
                            <div class="mt-0.5 text-[11px] text-slate-400">Anti-Tabrakan Slot</div>
                        </div>
                        <div class="rounded-2xl border border-slate-800/80 bg-slate-900/40 p-3 text-center">
                            <div class="text-base font-extrabold text-emerald-400">QRIS / VA</div>
                            <div class="mt-0.5 text-[11px] text-slate-400">Midtrans Terintegrasi</div>
                        </div>
                    </div>

                    <!-- Social Proof Stars -->
                    <div class="flex items-center gap-3 pt-2">
                        <div class="flex text-amber-400">
                            <Star v-for="i in 5" :key="i" class="h-4 w-4 fill-amber-400" />
                        </div>
                        <p class="text-xs text-slate-400">
                            <strong class="text-white">4.9/5</strong> dari 2.400+ pemain padel aktif di Jakarta & Bandung
                        </p>
                    </div>
                </div>

                <!-- Right Form Column -->
                <div class="flex w-full justify-center lg:col-span-6">
                    <div class="w-full max-w-md">
                        <div class="relative overflow-hidden rounded-3xl border border-slate-800/80 bg-slate-900/90 p-7 shadow-2xl shadow-black/80 backdrop-blur-2xl sm:p-9">
                            <!-- Glow highlight on card top border -->
                            <div class="pointer-events-none absolute -top-px left-1/2 h-[2px] w-3/4 -translate-x-1/2 bg-gradient-to-r from-transparent via-emerald-500/60 to-transparent" />
                            <slot />
                        </div>

                        <!-- Footer copyright note -->
                        <p class="mt-6 text-center text-xs text-slate-500">
                            © {{ new Date().getFullYear() }} PadelHub Indonesia. Hak Cipta Dilindungi.
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
