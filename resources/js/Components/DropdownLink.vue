<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    href: string;
}>();
</script>

<template>
    <Link
        :href="href"
        class="block w-full px-4 py-2 text-start text-xs font-semibold leading-5 text-slate-300 transition duration-150 ease-in-out hover:bg-slate-800 hover:text-white focus:bg-slate-800 focus:outline-none"
    >
        <slot />
    </Link>
</template>
