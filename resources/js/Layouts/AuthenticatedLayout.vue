<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Toaster, toast } from 'vue-sonner';

const showingSidebar = ref(false);

const page = usePage();
watch(
    () => (page.props.flash as { success?: string; error?: string } | undefined),
    (flash) => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
    },
);

const nav = computed(() => {
    const role = (page.props.auth.user as { role?: string } | null | undefined)?.role;
    const isStaff = role === 'admin' || role === 'venue_owner';

    const items = [
        { label: 'Dashboard', route: 'dashboard', pattern: 'dashboard' },
        ...(isStaff
            ? [
                  { label: 'Venues', route: 'venues.index', pattern: 'venues.*' },
                  { label: 'Courts', route: 'courts.index', pattern: 'courts.*' },
              ]
            : [{ label: 'Cari venue', route: 'venues.explore', pattern: 'venues.*' }]),
        { label: 'Ketersediaan', route: 'availability.index', pattern: 'availability.*' },
        { label: 'Bookings', route: 'bookings.index', pattern: 'bookings.*' },
        { label: 'Pembayaran', route: 'payments.index', pattern: 'payments.*' },
        ...(isStaff
            ? [
                  { label: 'Check-in', route: 'checkin.index', pattern: 'checkin.*' },
                  { label: 'Analytics', route: 'venue-dashboard.index', pattern: 'venue-dashboard.*' },
              ]
            : []),
    ];
    if (role === 'admin') {
        items.push({ label: 'Admin', route: 'admin.dashboard', pattern: 'admin.*' });
    }
    return items;
});
</script>

<template>
    <div class="min-h-screen bg-background">
        <Toaster position="top-right" richColors closeButton />
        <!-- Desktop sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col bg-brand text-white lg:flex"
        >
            <div class="flex h-16 items-center gap-3 px-6">
                <Link href="/" class="inline-flex items-center gap-3">
                    <ApplicationLogo class="h-9 w-9 text-white" />
                    <span class="text-lg font-bold tracking-tight">PadelHub</span>
                </Link>
            </div>

            <nav class="flex-1 space-y-1 px-3 py-4">
                <Link
                    v-for="item in nav"
                    :key="item.route"
                    :href="route(item.route)"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                    :class="route().current(item.pattern)
                        ? 'bg-white/10 text-white'
                        : 'text-white/50 hover:bg-white/5 hover:text-white'"
                >
                    <span
                        class="h-1.5 w-1.5 rounded-full"
                        :class="route().current(item.pattern) ? 'bg-brand-accent' : 'bg-transparent'"
                    />
                    {{ item.label }}
                </Link>
            </nav>

            <div class="border-t border-white/10 px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white/10">
                            <img
                                v-if="$page.props.auth.user.avatar_url"
                                :src="$page.props.auth.user.avatar_url"
                                alt="Avatar"
                                class="h-full w-full object-cover"
                            />
                            <span v-else class="text-sm font-bold text-white/60">{{ $page.props.auth.user.name.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">{{ $page.props.auth.user.name }}</p>
                            <p class="truncate text-xs text-white/40">{{ $page.props.auth.user.email }}</p>
                        </div>
                    </div>
                    <Dropdown align="right" width="48" open-up>
                        <template #trigger>
                            <button class="text-white/60 transition hover:text-white">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </aside>

        <!-- Mobile topbar -->
        <div class="sticky top-0 z-30 flex h-16 items-center justify-between bg-brand px-4 text-white lg:hidden">
            <Link href="/" class="inline-flex items-center gap-2">
                <ApplicationLogo class="h-8 w-8 text-white" />
                <span class="font-bold tracking-tight">PadelHub</span>
            </Link>
            <button @click="showingSidebar = true" class="text-white/70 hover:text-white">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <!-- Mobile sidebar -->
        <div
            v-if="showingSidebar"
            class="fixed inset-0 z-50 lg:hidden"
        >
            <div class="absolute inset-0 bg-black/50" @click="showingSidebar = false" />
            <div class="absolute inset-y-0 left-0 flex w-72 flex-col bg-brand text-white shadow-xl">
                <div class="flex h-16 items-center justify-between px-6">
                    <div class="inline-flex items-center gap-3">
                        <ApplicationLogo class="h-9 w-9 text-white" />
                        <span class="text-lg font-bold tracking-tight">PadelHub</span>
                    </div>
                    <button @click="showingSidebar = false" class="text-white/60 hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <nav class="flex-1 space-y-1 px-3 py-4">
                    <Link
                        v-for="item in nav"
                        :key="item.route"
                        :href="route(item.route)"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium"
                        :class="route().current(item.pattern) ? 'bg-white/10 text-white' : 'text-white/50'"
                        @click="showingSidebar = false"
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full"
                            :class="route().current(item.pattern) ? 'bg-brand-accent' : 'bg-transparent'"
                        />
                        {{ item.label }}
                    </Link>
                </nav>
                <div class="border-t border-white/10 px-4 py-4">
                    <p class="truncate text-sm font-semibold">{{ $page.props.auth.user.name }}</p>
                    <p class="truncate text-xs text-white/40">{{ $page.props.auth.user.email }}</p>
                </div>
            </div>
        </div>

        <!-- Main -->
        <div class="lg:pl-64">
            <header v-if="$slots.header" class="border-b border-brand-border bg-white">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
