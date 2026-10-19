"use client";

import { Bookmark, Heart, MessageCircle, Repeat2 } from "lucide-react";
import {
  toggleBookmarkAction,
  toggleLikeAction,
  toggleRepostAction,
} from "@/lib/actions/interactions";
import { usePostMutation } from "@/hooks/use-post-mutation";
import { cn, formatCompactNumber } from "@/lib/utils";
import type { SerializedPost } from "@/types";

type PostActionsProps = {
  post: SerializedPost;
  onReply?: () => void;
};

export function PostActions({ post, onReply }: PostActionsProps) {
  const like = usePostMutation("liked", (postId) => toggleLikeAction({ postId }));
  const repost = usePostMutation("reposted", (postId) =>
    toggleRepostAction({ postId }),
  );
  const bookmark = usePostMutation("bookmarked", (postId) =>
    toggleBookmarkAction({ postId }),
  );

  return (
    <div className="mt-3 flex max-w-md items-center justify-between text-muted">
      <button
        type="button"
        onClick={onReply}
        className="group flex items-center gap-1 hover:text-accent"
        aria-label="Ответить"
      >
        <span className="rounded-full p-2 group-hover:bg-accent/10">
          <MessageCircle className="h-4 w-4" />
        </span>
        <span className="text-xs">{formatCompactNumber(post.counts.replies)}</span>
      </button>
      <button
        type="button"
        onClick={() => repost.mutate(post.id)}
        className={cn(
          "group flex items-center gap-1 hover:text-repost",
          post.viewer.reposted && "text-repost",
        )}
        aria-label="Репост"
      >
        <span className="rounded-full p-2 group-hover:bg-repost/10">
          <Repeat2 className="h-4 w-4" />
        </span>
        <span className="text-xs">{formatCompactNumber(post.counts.reposts)}</span>
      </button>
      <button
        type="button"
        onClick={() => like.mutate(post.id)}
        className={cn(
          "group flex items-center gap-1 hover:text-like",
          post.viewer.liked && "text-like",
        )}
        aria-label="Нравится"
      >
        <span className="rounded-full p-2 group-hover:bg-like/10">
          <Heart className={cn("h-4 w-4", post.viewer.liked && "fill-current")} />
        </span>
        <span className="text-xs">{formatCompactNumber(post.counts.likes)}</span>
      </button>
      <button
        type="button"
        onClick={() => bookmark.mutate(post.id)}
        className={cn(
          "group flex items-center gap-1 hover:text-accent",
          post.viewer.bookmarked && "text-accent",
        )}
        aria-label="Закладка"
      >
        <span className="rounded-full p-2 group-hover:bg-accent/10">
          <Bookmark className={cn("h-4 w-4", post.viewer.bookmarked && "fill-current")} />
        </span>
      </button>
    </div>
  );
}
