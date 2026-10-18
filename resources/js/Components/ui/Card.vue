<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{ class?: string }>(), {});
</script>

<template>
    <div :class="cn('rounded-3xl border border-slate-800/80 bg-slate-900/60 text-white shadow-xl backdrop-blur-md', props.class)">
        <slot />
    </div>
</template>
