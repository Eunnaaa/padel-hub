<script setup lang="ts">
import { cn } from '@/lib/utils';
import { cva, type VariantProps } from 'class-variance-authority';
import { computed } from 'vue';

const badgeVariants = cva(
    'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
    {
        variants: {
            variant: {
                default: 'border-transparent bg-primary text-primary-foreground',
                accent: 'border-transparent bg-accent text-accent-foreground',
                secondary: 'border-transparent bg-secondary text-secondary-foreground',
                destructive: 'border-transparent bg-destructive text-destructive-foreground',
                outline: 'text-foreground',
                success: 'border-emerald-200 bg-emerald-50 text-emerald-700',
                warning: 'border-amber-200 bg-amber-50 text-amber-700',
                info: 'border-sky-200 bg-sky-50 text-sky-700',
                muted: 'border-gray-200 bg-gray-100 text-gray-500',
            },
        },
        defaultVariants: { variant: 'default' },
    },
);

type BadgeVariants = VariantProps<typeof badgeVariants>;

const props = withDefaults(
    defineProps<{ variant?: BadgeVariants['variant']; class?: string }>(),
    { variant: 'default' },
);

const classes = computed(() => badgeVariants({ variant: props.variant, className: cn(props.class) }));
</script>

<template>
    <span :class="classes"><slot /></span>
</template>
