import type { ComponentProps } from "react";
import { cn } from "@/lib/utils";

export function Textarea({ className, ...props }: ComponentProps<"textarea">) {
  return (
    <textarea
      className={cn(
        "min-h-12 w-full resize-none bg-transparent text-[20px] leading-6 text-foreground outline-none placeholder:text-muted",
        className,
      )}
      {...props}
    />
  );
}
