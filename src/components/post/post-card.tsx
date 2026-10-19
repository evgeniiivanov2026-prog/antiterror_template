"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { MoreHorizontal, Trash2 } from "lucide-react";
import { useQueryClient } from "@tanstack/react-query";
import { deletePostAction } from "@/lib/actions/post";
import { formatRelativeTime } from "@/lib/utils";
import { UserAvatar } from "@/components/user/user-avatar";
import { RichText } from "@/components/post/rich-text";
import { PostActions } from "@/components/post/post-actions";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import type { SerializedPost, SessionUser } from "@/types";

type PostCardProps = {
  post: SerializedPost;
  currentUser?: SessionUser | null;
};

export function PostCard({ post, currentUser }: PostCardProps) {
  const router = useRouter();
  const queryClient = useQueryClient();
  const href = `/${post.author.username}/status/${post.id}`;

  async function handleDelete() {
    const result = await deletePostAction({ postId: post.id });
    if (result.ok) {
      await queryClient.invalidateQueries();
    }
  }

  return (
    <article
      className="cursor-pointer border-b border-zinc-800 px-4 py-3 transition-colors hover:bg-white/[0.03]"
      onClick={() => router.push(href)}
    >
      <div className="flex gap-3">
        <Link
          href={`/${post.author.username}`}
          onClick={(event) => event.stopPropagation()}
        >
          <UserAvatar name={post.author.displayName} src={post.author.avatarUrl} />
        </Link>
        <div className="min-w-0 flex-1">
          <div className="flex items-start justify-between gap-2">
            <div className="flex min-w-0 flex-wrap items-center gap-x-1 text-[15px]">
              <Link
                href={`/${post.author.username}`}
                onClick={(event) => event.stopPropagation()}
                className="truncate font-bold hover:underline"
              >
                {post.author.displayName}
              </Link>
              <span className="truncate text-muted">@{post.author.username}</span>
              <span className="text-muted">·</span>
              <time className="text-muted" dateTime={post.createdAt}>
                {formatRelativeTime(post.createdAt)}
              </time>
            </div>
            {currentUser?.id === post.author.id && (
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <button
                    type="button"
                    className="rounded-full p-1 text-muted hover:bg-accent/10 hover:text-accent"
                    onClick={(event) => event.stopPropagation()}
                    aria-label="Ещё"
                  >
                    <MoreHorizontal className="h-5 w-5" />
                  </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" onClick={(event) => event.stopPropagation()}>
                  <DropdownMenuItem className="text-rose-400" onSelect={handleDelete}>
                    <Trash2 className="h-4 w-4" />
                    Удалить
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            )}
          </div>
          <div className="mt-1">
            <RichText content={post.content} />
          </div>
          {post.mediaUrls.length > 0 && (
            <div className="mt-3 grid grid-cols-2 gap-1 overflow-hidden rounded-2xl border border-zinc-800">
              {post.mediaUrls.map((url) => (
                // eslint-disable-next-line @next/next/no-img-element
                <img
                  key={url}
                  src={url}
                  alt=""
                  className="max-h-80 w-full object-cover"
                />
              ))}
            </div>
          )}
          <div onClick={(event) => event.stopPropagation()}>
            <PostActions
              post={post}
              onReply={() => router.push(href)}
            />
          </div>
        </div>
      </div>
    </article>
  );
}
