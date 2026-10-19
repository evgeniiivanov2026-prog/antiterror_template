"use client";

import { InfinitePosts } from "@/components/feed/infinite-posts";
import { ComposeForm } from "@/components/post/compose-form";
import { PostCard } from "@/components/post/post-card";
import type { AuthorView, CursorPage, PostView } from "@/types/social";

export function ThreadView({
  me,
  ancestors,
  post,
  replies,
}: {
  me: Pick<AuthorView, "id" | "displayName" | "username" | "avatarUrl">;
  ancestors: PostView[];
  post: PostView;
  replies: CursorPage<PostView>;
}) {
  return (
    <div>
      {ancestors.map((item) => (
        <PostCard key={item.id} post={item} viewerId={me.id} variant="ancestor" />
      ))}
      <div className="border-b border-border">
        <PostCard post={post} viewerId={me.id} variant="detail" />
      </div>
      <div className="border-b border-border px-4 py-3">
        <ComposeForm me={me} parentId={post.id} placeholder="Опубликовать ответ" />
      </div>
      <InfinitePosts
        queryKey={["replies", post.id]}
        initial={replies}
        endpoint={`/api/posts/${post.id}/replies`}
        viewerId={me.id}
        empty="Ответов пока нет. Будьте первым."
      />
    </div>
  );
}
