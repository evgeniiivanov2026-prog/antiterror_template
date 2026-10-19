"use client";

import { useEffect, useRef } from "react";
import { useInfiniteList } from "@/hooks/use-infinite-list";
import { PostCard } from "@/components/post/post-card";
import type { SessionUser } from "@/types";

type InfiniteFeedProps = {
  queryKey: readonly unknown[];
  path: string;
  currentUser?: SessionUser | null;
  emptyTitle: string;
  emptyDescription: string;
};

export function InfiniteFeed({
  queryKey,
  path,
  currentUser,
  emptyTitle,
  emptyDescription,
}: InfiniteFeedProps) {
  const { data, fetchNextPage, hasNextPage, isFetchingNextPage, isPending, isError } =
    useInfiniteList(queryKey, path);
  const sentinelRef = useRef<HTMLDivElement | null>(null);

  useEffect(() => {
    const node = sentinelRef.current;
    if (!node) return;
    const observer = new IntersectionObserver((entries) => {
      if (entries[0]?.isIntersecting && hasNextPage && !isFetchingNextPage) {
        void fetchNextPage();
      }
    });
    observer.observe(node);
    return () => observer.disconnect();
  }, [fetchNextPage, hasNextPage, isFetchingNextPage]);

  const posts = data?.pages.flatMap((page) => page.items) ?? [];

  if (isPending) {
    return (
      <div className="space-y-0">
        {Array.from({ length: 5 }).map((_, index) => (
          <div key={index} className="border-b border-zinc-800 px-4 py-4">
            <div className="flex gap-3">
              <div className="h-10 w-10 animate-pulse rounded-full bg-zinc-800" />
              <div className="flex-1 space-y-2">
                <div className="h-4 w-1/3 animate-pulse rounded bg-zinc-800" />
                <div className="h-4 w-full animate-pulse rounded bg-zinc-800" />
                <div className="h-4 w-2/3 animate-pulse rounded bg-zinc-800" />
              </div>
            </div>
          </div>
        ))}
      </div>
    );
  }

  if (isError) {
    return (
      <div className="px-6 py-12 text-center text-muted">
        Не удалось загрузить ленту. Обновите страницу.
      </div>
    );
  }

  if (posts.length === 0) {
    return (
      <div className="px-8 py-16 text-center">
        <h2 className="text-2xl font-extrabold">{emptyTitle}</h2>
        <p className="mt-2 text-muted">{emptyDescription}</p>
      </div>
    );
  }

  return (
    <div>
      {posts.map((post) => (
        <PostCard key={post.id} post={post} currentUser={currentUser} />
      ))}
      <div ref={sentinelRef} className="h-10" />
      {isFetchingNextPage && (
        <p className="py-4 text-center text-sm text-muted">Загрузка...</p>
      )}
    </div>
  );
}
