<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{ type?: string; class?: string }>(), { type: 'text' });
</script>

<template>
    <input
        :type="props.type"
        :class="cn('flex h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3.5 py-2 text-xs font-semibold text-white shadow-inner transition-colors file:border-0 file:bg-transparent file:text-xs file:font-medium placeholder:text-slate-500 focus-visible:outline-none focus-visible:border-emerald-500 focus-visible:ring-1 focus-visible:ring-emerald-500 disabled:cursor-not-allowed disabled:opacity-50', props.class)"
    />
</template>
