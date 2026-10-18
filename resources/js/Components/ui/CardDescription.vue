<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{ class?: string }>(), {});
</script>

<template>
    <p :class="cn('text-xs text-slate-400', props.class)">
        <slot />
    </p>
</template>
