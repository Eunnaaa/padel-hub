<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{ class?: string }>(), {});
</script>

<template>
    <h3 :class="cn('text-lg font-black tracking-tight text-white', props.class)">
        <slot />
    </h3>
</template>
