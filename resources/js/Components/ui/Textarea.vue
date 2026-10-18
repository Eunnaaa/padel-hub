<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{ rows?: number; class?: string }>(), { rows: 3 });
</script>

<template>
    <textarea
        :rows="props.rows"
        :class="cn('flex min-h-[80px] w-full rounded-xl border border-slate-800 bg-slate-950/80 p-3.5 text-xs font-semibold text-white shadow-inner transition-colors placeholder:text-slate-500 focus-visible:outline-none focus-visible:border-emerald-500 focus-visible:ring-1 focus-visible:ring-emerald-500 disabled:cursor-not-allowed disabled:opacity-50', props.class)"
    />
</template>
