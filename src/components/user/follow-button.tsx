"use client";

import { useState, useTransition } from "react";
import { toggleFollowAction } from "@/lib/actions/follow";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

export function FollowButton({
  userId,
  initialFollowing,
  className,
}: {
  userId: string;
  initialFollowing: boolean;
  className?: string;
}) {
  const [following, setFollowing] = useState(initialFollowing);
  const [isPending, startTransition] = useTransition();

  function toggle() {
    const previous = following;
    setFollowing(!previous);
    startTransition(async () => {
      const result = await toggleFollowAction({ userId });
      if (!result.ok) {
        setFollowing(previous);
      }
    });
  }

  return (
    <Button
      type="button"
      size="sm"
      variant={following ? "outline" : "secondary"}
      disabled={isPending}
      onClick={toggle}
      className={cn("min-w-28", className)}
    >
      {following ? "Читаю" : "Читать"}
    </Button>
  );
}
