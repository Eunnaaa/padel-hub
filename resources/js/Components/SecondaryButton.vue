<script setup lang="ts">
withDefaults(
    defineProps<{
        type?: 'button' | 'submit' | 'reset';
    }>(),
    {
        type: 'button',
    },
);
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-200 shadow-sm transition duration-150 ease-in-out hover:bg-slate-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:opacity-25"
    >
        <slot />
    </button>
</template>
