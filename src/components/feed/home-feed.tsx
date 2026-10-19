"use client";

import { useInfiniteQuery } from "@tanstack/react-query";
import { useState } from "react";
import { LoadMore } from "@/components/feed/load-more";
import { ComposeForm } from "@/components/post/compose-form";
import { PostCard } from "@/components/post/post-card";
import { cn } from "@/lib/utils";
import type { AuthorView, FeedPage, FeedTab } from "@/types/social";

async function fetchFeed(tab: FeedTab, cursor: string | null): Promise<FeedPage> {
  const params = new URLSearchParams({ tab });
  if (cursor) params.set("cursor", cursor);
  const response = await fetch(`/api/feed?${params}`);
  if (!response.ok) throw new Error("Не удалось загрузить ленту");
  return (await response.json()) as FeedPage;
}

export function HomeFeed({
  me,
  initialForYou,
}: {
  me: Pick<AuthorView, "id" | "displayName" | "username" | "avatarUrl">;
  initialForYou: FeedPage;
}) {
  const [tab, setTab] = useState<FeedTab>("for-you");
  const [freshAt] = useState(() => Date.now());
  const query = useInfiniteQuery({
    queryKey: ["feed", tab],
    initialPageParam: null as string | null,
    queryFn: ({ pageParam }) => fetchFeed(tab, pageParam),
    getNextPageParam: (last) => last.nextCursor,
    initialData: tab === "for-you" ? { pages: [initialForYou], pageParams: [null] } : undefined,
    initialDataUpdatedAt: tab === "for-you" ? freshAt : undefined,
  });

  const items = query.data?.pages.flatMap((page) => page.items) ?? [];

  return (
    <div>
      <div className="flex border-b border-border">
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
            className="relative flex-1 py-4 text-[15px] font-medium hover:bg-white/5"
          >
            <span className={cn(tab === value ? "font-bold text-foreground" : "text-muted")}>{label}</span>
            {tab === value ? <span className="absolute inset-x-[30%] bottom-0 h-1 rounded-full bg-accent" /> : null}
          </button>
        ))}
      </div>
      <div className="hidden border-b border-border px-4 py-3 md:block">
        <ComposeForm me={me} />
      </div>
      {query.isError ? (
        <div className="px-6 py-10 text-center">
          <p className="text-muted">Не удалось загрузить ленту</p>
          <button type="button" className="mt-3 text-accent" onClick={() => void query.refetch()}>
            Повторить
          </button>
        </div>
      ) : query.isPending ? (
        <FeedSkeleton />
      ) : items.length === 0 ? (
        <p className="px-8 py-12 text-center text-[15px] leading-6 text-muted">
          {tab === "following"
            ? "В подписках пока пусто. Найдите людей справа или через поиск."
            : "Лента пуста. Опубликуйте первый пост."}
        </p>
      ) : (
        items.map((item) => (
          <div key={item.id} className="border-b border-border">
            <PostCard post={item.post} viewerId={me.id} repostedBy={item.repostedBy} />
          </div>
        ))
      )}
      <LoadMore
        hasMore={Boolean(query.hasNextPage)}
        loading={query.isFetchingNextPage}
        error={query.isFetchNextPageError}
        onLoad={() => void query.fetchNextPage()}
      />
    </div>
  );
}

function FeedSkeleton() {
  return (
    <div className="space-y-px">
      {Array.from({ length: 4 }, (_, index) => (
        <div key={index} className="flex gap-3 border-b border-border px-4 py-4">
          <div className="size-10 animate-pulse rounded-full bg-white/10" />
          <div className="flex-1 space-y-2">
            <div className="h-4 w-40 animate-pulse rounded bg-white/10" />
            <div className="h-4 w-full animate-pulse rounded bg-white/10" />
            <div className="h-4 w-2/3 animate-pulse rounded bg-white/10" />
          </div>
        </div>
      ))}
    </div>
  );
}
