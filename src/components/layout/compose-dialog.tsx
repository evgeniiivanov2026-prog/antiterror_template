"use client";

import { Dialog, DialogContent } from "@/components/ui/dialog";
import { PostComposer } from "@/components/post/post-composer";
import type { SessionUser } from "@/types";

export function ComposeDialog({
  open,
  onOpenChange,
  user,
}: {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  user: SessionUser;
}) {
  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent>
        <PostComposer
          user={user}
          compact
          onCreated={() => onOpenChange(false)}
        />
      </DialogContent>
    </Dialog>
  );
}
