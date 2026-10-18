<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{ class?: string }>(), {});
</script>

<template>
    <label
        :class="cn('mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-300 peer-disabled:cursor-not-allowed peer-disabled:opacity-70', props.class)"
    >
        <slot />
    </label>
</template>
