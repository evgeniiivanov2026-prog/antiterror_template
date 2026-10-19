"use client";

import { useState } from "react";
import { PenLine } from "lucide-react";
import { ComposeForm } from "@/components/post/compose-form";
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent } from "@/components/ui/dialog";
import { cn } from "@/lib/utils";
import type { AuthorView } from "@/types/social";

export function ComposeButton({
  me,
  className,
  iconOnly = false,
}: {
  me: Pick<AuthorView, "displayName" | "username" | "avatarUrl">;
  className?: string;
  iconOnly?: boolean;
}) {
  const [open, setOpen] = useState(false);
  return (
    <>
      <Button
        type="button"
        aria-label="Опубликовать"
        className={cn(iconOnly ? "size-12 px-0" : "h-[52px] w-[90%] text-[17px]", className)}
        onClick={() => setOpen(true)}
      >
        {iconOnly ? <PenLine className="size-5" /> : "Опубликовать"}
      </Button>
      <Dialog open={open} onOpenChange={setOpen}>
        <DialogContent title="Новый пост">
          <div className="px-4 py-4">
            <ComposeForm me={me} autoFocus onPosted={() => setOpen(false)} />
          </div>
        </DialogContent>
      </Dialog>
    </>
  );
}

export function MobileCompose({ me }: { me: Pick<AuthorView, "displayName" | "username" | "avatarUrl"> }) {
  const [open, setOpen] = useState(false);
  return (
    <>
      <button
        type="button"
        aria-label="Опубликовать"
        className="fixed bottom-20 right-4 z-40 flex size-14 items-center justify-center rounded-full bg-accent text-white shadow-lg md:hidden"
        onClick={() => setOpen(true)}
      >
        <PenLine className="size-6" />
      </button>
      <Dialog open={open} onOpenChange={setOpen}>
        <DialogContent title="Новый пост">
          <div className="px-4 py-4">
            <ComposeForm me={me} autoFocus onPosted={() => setOpen(false)} />
          </div>
        </DialogContent>
      </Dialog>
    </>
  );
}
