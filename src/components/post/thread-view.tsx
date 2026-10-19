"use client";

import { useQuery } from "@tanstack/react-query";
import Link from "next/link";
import { ArrowLeft } from "lucide-react";
import { queryKeys } from "@/lib/query-keys";
import { PostCard } from "@/components/post/post-card";
import { PostComposer } from "@/components/post/post-composer";
import { InfiniteFeed } from "@/components/post/infinite-feed";
import type { SerializedPost, SessionUser } from "@/types";

export function ThreadView({
  postId,
  user,
}: {
  postId: string;
  user: SessionUser;
}) {
  const { data: post, isPending } = useQuery({
    queryKey: queryKeys.post(postId),
    queryFn: async () => {
      const response = await fetch(`/api/posts/${postId}`);
      if (!response.ok) throw new Error("not found");
      return (await response.json()) as SerializedPost;
    },
  });

  return (
    <div>
      <header className="sticky top-0 z-20 flex items-center gap-6 border-b border-zinc-800 bg-black/75 px-4 py-3 backdrop-blur-md">
        <Link href="/" className="rounded-full p-2 hover:bg-white/10">
          <ArrowLeft className="h-5 w-5" />
        </Link>
        <h1 className="text-xl font-bold">Пост</h1>
      </header>
      {isPending && <p className="px-4 py-8 text-center text-muted">Загрузка...</p>}
      {post && (
        <>
          <PostCard post={post} currentUser={user} />
          <PostComposer
            user={user}
            parentId={post.id}
            placeholder="Опубликовать ответ"
          />
          <InfiniteFeed
            queryKey={queryKeys.replies(post.id)}
            path={`/api/posts/${post.id}/replies`}
            currentUser={user}
            emptyTitle="Ответов пока нет"
            emptyDescription="Станьте первым, кто ответит."
          />
        </>
      )}
    </div>
  );
}
