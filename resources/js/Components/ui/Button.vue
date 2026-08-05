<script setup lang="ts">
import { cn } from '@/lib/utils';
import { cva, type VariantProps } from 'class-variance-authority';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const buttonVariants = cva(
    'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
    {
        variants: {
            variant: {
                default: 'bg-primary text-primary-foreground shadow hover:bg-primary/90',
                accent: 'bg-accent text-accent-foreground shadow hover:bg-green-600',
                destructive: 'bg-destructive text-destructive-foreground shadow-sm hover:bg-red-600',
                outline: 'border border-border bg-transparent hover:bg-muted',
                secondary: 'bg-secondary text-secondary-foreground shadow-sm hover:bg-secondary/80',
                ghost: 'hover:bg-muted',
                link: 'text-foreground underline-offset-4 hover:underline',
            },
            size: {
                default: 'h-10 px-4 py-2',
                sm: 'h-8 rounded-md px-3 text-xs',
                lg: 'h-11 rounded-lg px-8',
                icon: 'h-10 w-10',
            },
        },
        defaultVariants: { variant: 'default', size: 'default' },
    },
);

type ButtonVariants = VariantProps<typeof buttonVariants>;

const props = withDefaults(
    defineProps<{
        variant?: ButtonVariants['variant'];
        size?: ButtonVariants['size'];
        as?: 'button' | 'link';
        href?: string;
        type?: 'button' | 'submit' | 'reset';
        class?: string;
    }>(),
    { variant: 'default', size: 'default', as: 'button', type: 'button' },
);

const classes = computed(() =>
    buttonVariants({ variant: props.variant, size: props.size, className: cn(props.class) }),
);
</script>

<template>
    <Link v-if="as === 'link'" :href="href ?? '#'" :class="classes">
        <slot />
    </Link>
    <button v-else :type="type" :class="classes">
        <slot />
    </button>
</template>
