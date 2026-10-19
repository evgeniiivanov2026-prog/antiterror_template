import type { ComponentProps } from "react";
import { cn } from "@/lib/utils";

export function Input({ className, ...props }: ComponentProps<"input">) {
  return (
    <input
      className={cn(
        "h-14 w-full rounded border border-border bg-transparent px-3 text-[17px] text-foreground outline-none placeholder:text-muted focus:border-accent",
        className,
      )}
      {...props}
    />
  );
}
