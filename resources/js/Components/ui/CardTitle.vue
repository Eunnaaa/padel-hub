<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{ class?: string }>(), {});
</script>

<template>
    <h3 :class="cn('text-lg font-bold leading-none tracking-tight', props.class)">
        <slot />
    </h3>
</template>
