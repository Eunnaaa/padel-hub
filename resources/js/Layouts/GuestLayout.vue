<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen">
        <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-brand p-12 text-white lg:flex">
            <Link href="/" class="inline-flex items-center gap-3">
                <ApplicationLogo class="h-9 w-9 text-white" />
                <span class="text-xl font-bold tracking-tight">PadelHub</span>
            </Link>

            <div>
                <h2 class="max-w-md text-4xl font-bold leading-tight tracking-tight">
                    Booking padel, <span class="text-brand-accent">tanpa ribet.</span>
                </h2>
                <p class="mt-4 max-w-md text-white/60">
                    Temukan venue, pilih lapangan, bayar online, dan check-in lewat QR — semua dalam satu platform.
                </p>
            </div>

            <p class="text-sm text-white/40">© {{ new Date().getFullYear() }} PadelHub</p>

            <div
                class="pointer-events-none absolute inset-0 opacity-10"
                style="background-image: radial-gradient(#22C55E 1px, transparent 1px); background-size: 24px 24px"
            />
        </div>

        <div class="flex w-full flex-col items-center justify-center bg-brand-surface px-6 py-12 lg:w-1/2">
            <Link href="/" class="mb-10 inline-flex items-center gap-3 lg:hidden">
                <ApplicationLogo class="h-9 w-9 text-brand" />
                <span class="text-xl font-bold tracking-tight text-brand">PadelHub</span>
            </Link>

            <div class="w-full max-w-sm">
                <slot />
            </div>
        </div>
    </div>
</template>
