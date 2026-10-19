"use client";

import { useInfiniteQuery } from "@tanstack/react-query";
import { Search } from "lucide-react";
import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { LoadMore } from "@/components/feed/load-more";
import { PostCard } from "@/components/post/post-card";
import { UserRow } from "@/components/user/user-row";
import type { SearchResults } from "@/types/social";

export function SearchView({
  query,
  initial,
  viewerId,
}: {
  query: string;
  initial: SearchResults | null;
  viewerId: string;
}) {
  const router = useRouter();
  const [value, setValue] = useState(query);

  useEffect(() => {
    setValue(query);
  }, [query]);

  useEffect(() => {
    const handle = window.setTimeout(() => {
      const next = value.trim();
      if (next === query) return;
      router.replace(next ? `/search?q=${encodeURIComponent(next)}` : "/search");
    }, 350);
    return () => window.clearTimeout(handle);
  }, [value, query, router]);

  const results = useInfiniteQuery({
    queryKey: ["search", query],
    enabled: query.length > 0,
    initialPageParam: null as string | null,
    queryFn: async ({ pageParam }) => {
      const params = new URLSearchParams({ q: query });
      if (pageParam) params.set("cursor", pageParam);
      const response = await fetch(`/api/search?${params}`);
      if (!response.ok) throw new Error("Ошибка поиска");
      return (await response.json()) as SearchResults;
    },
    getNextPageParam: (last) => last.posts.nextCursor,
    initialData: initial ? { pages: [initial], pageParams: [null] } : undefined,
  });

  const users = results.data?.pages[0]?.users ?? [];
  const posts = results.data?.pages.flatMap((page) => page.posts.items) ?? [];

  return (
    <div>
      <div className="sticky top-0 z-20 border-b border-border bg-black/80 px-4 py-2 backdrop-blur-md">
        <label className="flex items-center gap-3 rounded-full bg-[#202327] px-4 py-3 focus-within:bg-black focus-within:ring-1 focus-within:ring-accent">
          <Search className="size-5 text-muted" />
          <input
            value={value}
            onChange={(event) => setValue(event.target.value)}
            placeholder="Поиск людей, постов и #тем"
            aria-label="Поиск"
            className="w-full bg-transparent text-[15px] outline-none placeholder:text-muted"
          />
        </label>
      </div>
      {!query ? (
        <p className="px-8 py-12 text-center text-muted">Ищите людей, фразы или хэштеги вроде #дизайн</p>
      ) : results.isError ? (
        <p className="px-8 py-12 text-center text-muted">Не удалось выполнить поиск</p>
      ) : results.isPending ? (
        <p className="px-8 py-12 text-center text-muted">Ищем…</p>
      ) : users.length === 0 && posts.length === 0 ? (
        <p className="px-8 py-12 text-center text-muted">Ничего не найдено по запросу «{query}»</p>
      ) : (
        <div>
          {users.length > 0 ? (
            <section>
              <h2 className="px-4 py-3 text-xl font-extrabold">Люди</h2>
              {users.map((user) => (
                <div key={user.id} className="border-b border-border">
                  <UserRow user={user} />
                </div>
              ))}
            </section>
          ) : null}
          {posts.length > 0 ? (
            <section>
              <h2 className="px-4 py-3 text-xl font-extrabold">Посты</h2>
              {posts.map((post) => (
                <div key={post.id} className="border-b border-border">
                  <PostCard post={post} viewerId={viewerId} />
                </div>
              ))}
            </section>
          ) : null}
          <LoadMore
            hasMore={Boolean(results.hasNextPage)}
            loading={results.isFetchingNextPage}
            error={results.isFetchNextPageError}
            onLoad={() => void results.fetchNextPage()}
          />
        </div>
      )}
    </div>
  );
}
