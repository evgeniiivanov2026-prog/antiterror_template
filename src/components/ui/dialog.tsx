"use client";

import * as DialogPrimitive from "@radix-ui/react-dialog";
import { X } from "lucide-react";
import { cn } from "@/lib/utils";

export const Dialog = DialogPrimitive.Root;
export const DialogTrigger = DialogPrimitive.Trigger;
export const DialogClose = DialogPrimitive.Close;

export function DialogContent({
  className,
  children,
  ...props
}: React.ComponentProps<typeof DialogPrimitive.Content>) {
  return (
    <DialogPrimitive.Portal>
      <DialogPrimitive.Overlay className="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm" />
      <DialogPrimitive.Content
        className={cn(
          "fixed left-1/2 top-12 z-50 w-full max-w-xl -translate-x-1/2 rounded-2xl border border-zinc-800 bg-black p-4 shadow-2xl",
          className,
        )}
        {...props}
      >
        <DialogPrimitive.Title className="sr-only">Новый пост</DialogPrimitive.Title>
        <DialogPrimitive.Close className="mb-2 rounded-full p-2 text-foreground hover:bg-white/10">
          <X className="h-5 w-5" />
        </DialogPrimitive.Close>
        {children}
      </DialogPrimitive.Content>
    </DialogPrimitive.Portal>
  );
}
