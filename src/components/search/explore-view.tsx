"use client";

import { useSearchParams } from "next/navigation";
import { useQuery } from "@tanstack/react-query";
import Link from "next/link";
import { queryKeys } from "@/lib/query-keys";
import { SearchBox } from "@/components/search/search-box";
import { PostCard } from "@/components/post/post-card";
import { UserAvatar } from "@/components/user/user-avatar";
import { FollowButton } from "@/components/user/follow-button";
import type { PublicUser, SerializedPost, SessionUser } from "@/types";

export function ExploreView({
  user,
  initialQuery,
}: {
  user: SessionUser;
  initialQuery: string;
}) {
  const searchParams = useSearchParams();
  const q = searchParams.get("q") ?? initialQuery;

  const { data, isPending } = useQuery({
    queryKey: queryKeys.search(q),
    enabled: q.length > 0,
    queryFn: async () => {
      const response = await fetch(`/api/search?q=${encodeURIComponent(q)}`);
      if (!response.ok) throw new Error("fail");
      return (await response.json()) as {
        users: PublicUser[];
        items: SerializedPost[];
      };
    },
  });

  return (
    <div>
      <header className="sticky top-0 z-20 border-b border-zinc-800 bg-black/75 px-4 py-3 backdrop-blur-md">
        <SearchBox initialQuery={q} />
      </header>
      {!q && (
        <div className="px-8 py-16 text-center">
          <h2 className="text-2xl font-extrabold">Ищите посты и людей</h2>
          <p className="mt-2 text-muted">Введите ник, слово или хэштег.</p>
        </div>
      )}
      {q && isPending && <p className="px-4 py-8 text-center text-muted">Ищем...</p>}
      {data && (
        <>
          {data.users.length > 0 && (
            <section className="border-b border-zinc-800">
              <h2 className="px-4 py-3 text-xl font-extrabold">Люди</h2>
              {data.users.map((item) => (
                <div key={item.id} className="flex items-center gap-3 px-4 py-3">
                  <Link href={`/${item.username}`}>
                    <UserAvatar name={item.displayName} src={item.avatarUrl} />
                  </Link>
                  <div className="min-w-0 flex-1">
                    <Link href={`/${item.username}`} className="block font-bold hover:underline">
                      {item.displayName}
                    </Link>
                    <p className="text-sm text-muted">@{item.username}</p>
                  </div>
                  {item.id !== user.id && (
                    <FollowButton userId={item.id} initialFollowing={false} />
                  )}
                </div>
              ))}
            </section>
          )}
          {data.items.map((post) => (
            <PostCard key={post.id} post={post} currentUser={user} />
          ))}
          {data.users.length === 0 && data.items.length === 0 && (
            <p className="px-8 py-16 text-center text-muted">Ничего не найдено по запросу «{q}»</p>
          )}
        </>
      )}
    </div>
  );
}
