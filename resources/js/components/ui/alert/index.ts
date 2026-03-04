import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Alert } from "./Alert.vue"
export { default as AlertDescription } from "./AlertDescription.vue"
export { default as AlertTitle } from "./AlertTitle.vue"

export const alertVariants = cva(
  "relative w-full rounded-xl border px-4 py-3.5 text-sm grid has-[>svg]:grid-cols-[calc(var(--spacing)*4)_1fr] grid-cols-[0_1fr] has-[>svg]:gap-x-3 gap-y-0.5 items-start [&>svg]:size-4 [&>svg]:translate-y-0.5 [&>svg]:text-current shadow-sm",
  {
    variants: {
      variant: {
        default: "bg-card text-card-foreground border-border/60",
        destructive:
          "text-red-800 bg-red-50 border-red-200 [&>svg]:text-red-600 *:data-[slot=alert-description]:text-red-700 dark:text-red-200 dark:bg-red-950/50 dark:border-red-900/50",
        success:
          "text-emerald-800 bg-emerald-50 border-emerald-200 [&>svg]:text-emerald-600 *:data-[slot=alert-description]:text-emerald-700 dark:text-emerald-200 dark:bg-emerald-950/50 dark:border-emerald-900/50",
        warning:
          "text-amber-800 bg-amber-50 border-amber-200 [&>svg]:text-amber-600 *:data-[slot=alert-description]:text-amber-700 dark:text-amber-200 dark:bg-amber-950/50 dark:border-amber-900/50",
        info:
          "text-blue-800 bg-blue-50 border-blue-200 [&>svg]:text-blue-600 *:data-[slot=alert-description]:text-blue-700 dark:text-blue-200 dark:bg-blue-950/50 dark:border-blue-900/50",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  },
)

export type AlertVariants = VariantProps<typeof alertVariants>
