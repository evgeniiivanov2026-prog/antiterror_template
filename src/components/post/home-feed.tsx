"use client";

import { useState } from "react";
import { queryKeys } from "@/lib/query-keys";
import { PostComposer } from "@/components/post/post-composer";
import { InfiniteFeed } from "@/components/post/infinite-feed";
import { cn } from "@/lib/utils";
import type { SessionUser } from "@/types";

export function HomeFeed({ user }: { user: SessionUser }) {
  const [tab, setTab] = useState<"for-you" | "following">("for-you");

  return (
    <div>
      <header className="sticky top-0 z-20 border-b border-zinc-800 bg-black/75 backdrop-blur-md">
        <div className="flex h-[53px] items-center px-4 text-xl font-bold">Главная</div>
        <div className="grid grid-cols-2">
          {(
            [
              ["for-you", "Для вас"],
              ["following", "Подписки"],
            ] as const
          ).map(([value, label]) => (
            <button
              key={value}
              type="button"
              onClick={() => setTab(value)}
              className={cn(
                "relative h-[53px] text-sm font-medium hover:bg-white/5",
                tab === value ? "text-foreground" : "text-muted",
              )}
            >
              {label}
              {tab === value && (
                <span className="absolute bottom-0 left-1/2 h-1 w-14 -translate-x-1/2 rounded-full bg-accent" />
              )}
            </button>
          ))}
        </div>
      </header>
      <div className="hidden sm:block">
        <PostComposer user={user} />
      </div>
      <InfiniteFeed
        queryKey={queryKeys.feed(tab)}
        path={`/api/feed?tab=${tab}`}
        currentUser={user}
        emptyTitle={tab === "following" ? "Пока тихо" : "Лента пуста"}
        emptyDescription={
          tab === "following"
            ? "Подпишитесь на авторов, чтобы видеть их посты здесь."
            : "Опубликуйте первый пост — и лента оживёт."
        }
      />
    </div>
  );
}
