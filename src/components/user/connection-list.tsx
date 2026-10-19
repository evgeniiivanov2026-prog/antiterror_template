"use client";

import { useInfiniteQuery } from "@tanstack/react-query";
import { LoadMore } from "@/components/feed/load-more";
import { UserRow } from "@/components/user/user-row";
import type { ConnectionTab, CursorPage, UserCard } from "@/types/social";

export function ConnectionList({
  username,
  kind,
  initial,
}: {
  username: string;
  kind: ConnectionTab;
  initial: CursorPage<UserCard>;
}) {
  const query = useInfiniteQuery({
    queryKey: ["connections", username, kind],
    initialPageParam: null as string | null,
    queryFn: async ({ pageParam }) => {
      const params = new URLSearchParams({ kind });
      if (pageParam) params.set("cursor", pageParam);
      const response = await fetch(`/api/users/${username}/connections?${params}`);
      if (!response.ok) throw new Error("Не удалось загрузить список");
      return (await response.json()) as CursorPage<UserCard>;
    },
    getNextPageParam: (last) => last.nextCursor,
    initialData: { pages: [initial], pageParams: [null] },
  });
  const users = query.data.pages.flatMap((page) => page.items);

  if (users.length === 0) {
    return <p className="px-8 py-12 text-center text-muted">{kind === "followers" ? "Читателей пока нет" : "Подписок пока нет"}</p>;
  }

  return (
    <div>
      {users.map((user) => (
        <div key={user.id} className="border-b border-border">
          <UserRow user={user} />
        </div>
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
