"use client";

import { useMutation, useQueryClient } from "@tanstack/react-query";
import { Bookmark, Heart, MessageCircle, Repeat2, Share } from "lucide-react";
import { useRouter } from "next/navigation";
import { toast } from "sonner";
import { bookmarkPost, likePost, repostPost } from "@/actions/social";
import { patchCachedPost, restorePostCaches, snapshotPostCaches } from "@/lib/post-cache";
import type { ActionResult, PostView } from "@/types/social";
import { cn, formatCount } from "@/lib/utils";

type ToggleData = { active: boolean; count: number };

function usePostToggle(
  postId: string,
  mutate: () => Promise<ActionResult<ToggleData>>,
  optimistic: (post: PostView) => PostView,
  field: "liked" | "reposted" | "bookmarked",
  countKey: "likes" | "reposts" | "bookmarks",
) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async () => {
      const result = await mutate();
      if (!result.ok) throw new Error(result.error);
      return result.data;
    },
    onMutate: async () => {
      await queryClient.cancelQueries({
        predicate: (query) => ["feed", "replies", "profile", "bookmarks", "search", "thread"].includes(String(query.queryKey[0])),
      });
      const snapshot = snapshotPostCaches(queryClient);
      patchCachedPost(queryClient, postId, optimistic);
      return { snapshot };
    },
    onError: (error, _vars, context) => {
      if (context) restorePostCaches(queryClient, context.snapshot);
      toast.error(error instanceof Error ? error.message : "Не удалось обновить");
    },
    onSuccess: (data) => {
      patchCachedPost(queryClient, postId, (current) => ({
        ...current,
        viewer: { ...current.viewer, [field]: data.active },
        counts: { ...current.counts, [countKey]: data.count },
      }));
    },
  });
}

export function PostActions({ post }: { post: PostView }) {
  const router = useRouter();
  const like = usePostToggle(
    post.id,
    () => likePost(post.id),
    (current) => ({
      ...current,
      viewer: { ...current.viewer, liked: !current.viewer.liked },
      counts: {
        ...current.counts,
        likes: Math.max(0, current.counts.likes + (current.viewer.liked ? -1 : 1)),
      },
    }),
    "liked",
    "likes",
  );
  const repost = usePostToggle(
    post.id,
    () => repostPost(post.id),
    (current) => ({
      ...current,
      viewer: { ...current.viewer, reposted: !current.viewer.reposted },
      counts: {
        ...current.counts,
        reposts: Math.max(0, current.counts.reposts + (current.viewer.reposted ? -1 : 1)),
      },
    }),
    "reposted",
    "reposts",
  );
  const bookmark = usePostToggle(
    post.id,
    () => bookmarkPost(post.id),
    (current) => ({
      ...current,
      viewer: { ...current.viewer, bookmarked: !current.viewer.bookmarked },
      counts: {
        ...current.counts,
        bookmarks: Math.max(0, current.counts.bookmarks + (current.viewer.bookmarked ? -1 : 1)),
      },
    }),
    "bookmarked",
    "bookmarks",
  );

  return (
    <div className="mt-3 flex max-w-[425px] items-center justify-between text-muted">
      <ActionButton
        label="Ответить"
        count={post.counts.replies}
        active={false}
        tone="accent"
        onClick={() => router.push(`/${post.author.username}/status/${post.id}#reply`)}
      >
        <MessageCircle className="size-[18px]" />
      </ActionButton>
      <ActionButton
        label={post.viewer.reposted ? "Убрать репост" : "Репост"}
        count={post.counts.reposts}
        active={post.viewer.reposted}
        tone="repost"
        disabled={repost.isPending}
        onClick={() => repost.mutate()}
      >
        <Repeat2 className="size-[18px]" />
      </ActionButton>
      <ActionButton
        label={post.viewer.liked ? "Убрать отметку" : "Нравится"}
        count={post.counts.likes}
        active={post.viewer.liked}
        tone="like"
        disabled={like.isPending}
        onClick={() => like.mutate()}
      >
        <Heart className={cn("size-[18px]", post.viewer.liked && "fill-current")} />
      </ActionButton>
      <ActionButton
        label={post.viewer.bookmarked ? "Убрать из закладок" : "В закладки"}
        count={post.counts.bookmarks}
        active={post.viewer.bookmarked}
        tone="accent"
        disabled={bookmark.isPending}
        onClick={() => bookmark.mutate()}
      >
        <Bookmark className={cn("size-[18px]", post.viewer.bookmarked && "fill-current")} />
      </ActionButton>
      <ActionButton
        label="Копировать ссылку"
        active={false}
        tone="accent"
        onClick={() => {
          const url = `${window.location.origin}/${post.author.username}/status/${post.id}`;
          void navigator.clipboard.writeText(url).then(
            () => toast.success("Ссылка скопирована"),
            () => toast.error("Не удалось скопировать ссылку"),
          );
        }}
      >
        <Share className="size-[18px]" />
      </ActionButton>
    </div>
  );
}

function ActionButton({
  label,
  count,
  active,
  tone,
  disabled,
  onClick,
  children,
}: {
  label: string;
  count?: number;
  active: boolean;
  tone: "accent" | "like" | "repost";
  disabled?: boolean;
  onClick: () => void;
  children: React.ReactNode;
}) {
  const toneClass =
    tone === "like"
      ? "hover:text-like group-hover:bg-like/10"
      : tone === "repost"
        ? "hover:text-repost group-hover:bg-repost/10"
        : "hover:text-accent group-hover:bg-accent/10";
  const activeClass = active ? (tone === "like" ? "text-like" : tone === "repost" ? "text-repost" : "text-accent") : "";

  return (
    <button
      type="button"
      aria-label={label}
      aria-pressed={active}
      disabled={disabled}
      onClick={(event) => {
        event.stopPropagation();
        onClick();
      }}
      className={cn("group flex items-center gap-1 text-[13px] disabled:opacity-60", toneClass, activeClass)}
    >
      <span className={cn("rounded-full p-2 transition-colors", toneClass)}>{children}</span>
      {typeof count === "number" && count > 0 ? <span className="tabular-nums">{formatCount(count)}</span> : null}
    </button>
  );
}
