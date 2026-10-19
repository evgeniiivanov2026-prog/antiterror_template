"use client";

import { useInfiniteQuery } from "@tanstack/react-query";
import { LoadMore } from "@/components/feed/load-more";
import { PostCard } from "@/components/post/post-card";
import type { CursorPage, PostView } from "@/types/social";

async function fetchPage(endpoint: string, cursor: string | null): Promise<CursorPage<PostView>> {
  const url = new URL(endpoint, window.location.origin);
  if (cursor) url.searchParams.set("cursor", cursor);
  const response = await fetch(url);
  if (!response.ok) throw new Error("Не удалось загрузить посты");
  return (await response.json()) as CursorPage<PostView>;
}

export function InfinitePosts({
  queryKey,
  initial,
  endpoint,
  viewerId,
  empty,
}: {
  queryKey: readonly unknown[];
  initial: CursorPage<PostView>;
  endpoint: string;
  viewerId: string;
  empty: string;
}) {
  const query = useInfiniteQuery({
    queryKey,
    initialPageParam: null as string | null,
    queryFn: ({ pageParam }) => fetchPage(endpoint, pageParam),
    getNextPageParam: (last) => last.nextCursor,
    initialData: { pages: [initial], pageParams: [null] },
  });
  const posts = query.data.pages.flatMap((page) => page.items);

  if (!query.isPending && posts.length === 0) {
    return <p className="px-8 py-12 text-center text-[15px] text-muted">{empty}</p>;
  }

  return (
    <div>
      {posts.map((post) => (
        <PostCard key={post.id} post={post} viewerId={viewerId} />
      ))}
      <LoadMore
        hasMore={Boolean(query.hasNextPage)}
        loading={query.isFetchingNextPage}
        error={query.isFetchNextPageError}
        onLoad={() => void query.fetchNextPage()}
      />
    </div>
  );
}
