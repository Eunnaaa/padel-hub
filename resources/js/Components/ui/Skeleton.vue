<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{ class?: string }>(), {});
</script>

<template>
    <div :class="cn('animate-pulse rounded-xl bg-slate-800/80', props.class)" />
</template>
