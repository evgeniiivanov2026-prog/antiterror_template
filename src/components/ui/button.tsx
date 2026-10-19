import { Slot } from "@radix-ui/react-slot";
import { cva, type VariantProps } from "class-variance-authority";
import type { ComponentProps } from "react";
import { cn } from "@/lib/utils";

const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full font-bold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent disabled:pointer-events-none disabled:opacity-50",
  {
    variants: {
      variant: {
        default: "bg-accent text-white hover:bg-[#1a8cd8]",
        white: "bg-white text-black hover:bg-zinc-200",
        outline: "border border-border bg-transparent text-foreground hover:bg-white/10",
        ghost: "text-foreground hover:bg-white/10",
        danger: "border border-[#f4212e] bg-transparent text-[#f4212e] hover:bg-[#f4212e]/10",
      },
      size: {
        default: "h-10 px-4 text-[15px]",
        sm: "h-8 px-4 text-sm",
        lg: "h-12 px-6 text-[17px]",
        icon: "size-10",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
);

export function Button({
  className,
  variant,
  size,
  asChild = false,
  ...props
}: ComponentProps<"button"> & VariantProps<typeof buttonVariants> & { asChild?: boolean }) {
  const Comp = asChild ? Slot : "button";
  return <Comp className={cn(buttonVariants({ variant, size }), className)} {...props} />;
}

export { buttonVariants };
