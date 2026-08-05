<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{ rows?: number; class?: string }>(), { rows: 3 });
</script>

<template>
    <textarea
        :rows="props.rows"
        :class="cn('flex min-h-[60px] w-full rounded-lg border border-input bg-white px-3 py-2 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50', props.class)"
    />
</template>
