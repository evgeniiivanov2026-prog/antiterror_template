"use client";

import { useEffect, useState, useTransition } from "react";
import { toast } from "sonner";
import { followUser } from "@/actions/social";
import { cn } from "@/lib/utils";

export function FollowButton({
  userId,
  initialFollowing,
  onChange,
}: {
  userId: string;
  initialFollowing: boolean;
  onChange?: (following: boolean, followers: number) => void;
}) {
  const [following, setFollowing] = useState(initialFollowing);
  const [pending, startTransition] = useTransition();

  useEffect(() => {
    setFollowing(initialFollowing);
  }, [initialFollowing]);

  return (
    <button
      type="button"
      disabled={pending}
      onClick={() => {
        const next = !following;
        setFollowing(next);
        startTransition(async () => {
          const result = await followUser(userId);
          if (!result.ok) {
            setFollowing(!next);
            toast.error(result.error);
            return;
          }
          setFollowing(result.data.following);
          onChange?.(result.data.following, result.data.followers);
        });
      }}
      className={cn(
        "group h-9 min-w-28 rounded-full px-4 text-sm font-bold transition-colors disabled:opacity-60",
        following
          ? "border border-border bg-transparent text-foreground hover:border-[#f4212e] hover:bg-[#f4212e]/10 hover:text-[#f4212e]"
          : "bg-white text-black hover:bg-zinc-200",
      )}
    >
      {following ? (
        <>
          <span className="group-hover:hidden">Читаю</span>
          <span className="hidden group-hover:inline">Отписаться</span>
        </>
      ) : (
        "Читать"
      )}
    </button>
  );
}
