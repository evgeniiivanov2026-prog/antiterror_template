"use client";

import * as DialogPrimitive from "@radix-ui/react-dialog";
import { X } from "lucide-react";
import type { ComponentProps } from "react";
import { cn } from "@/lib/utils";

export const Dialog = DialogPrimitive.Root;
export const DialogTrigger = DialogPrimitive.Trigger;
export const DialogClose = DialogPrimitive.Close;

export function DialogContent({
  className,
  children,
  title,
  ...props
}: ComponentProps<typeof DialogPrimitive.Content> & { title: string }) {
  return (
    <DialogPrimitive.Portal>
      <DialogPrimitive.Overlay className="fixed inset-0 z-50 bg-[#5b7083]/40" />
      <DialogPrimitive.Content
        className={cn(
          "fixed left-1/2 top-10 z-50 w-[min(600px,calc(100%-1.5rem))] -translate-x-1/2 rounded-2xl border border-border bg-black shadow-2xl focus:outline-none",
          className,
        )}
        {...props}
      >
        <div className="flex items-center gap-6 border-b border-border px-3 py-2">
          <DialogPrimitive.Close
            className="rounded-full p-2 text-foreground hover:bg-white/10"
            aria-label="Закрыть"
          >
            <X className="size-5" />
          </DialogPrimitive.Close>
          <DialogPrimitive.Title className="text-xl font-bold">{title}</DialogPrimitive.Title>
        </div>
        {children}
      </DialogPrimitive.Content>
    </DialogPrimitive.Portal>
  );
}
