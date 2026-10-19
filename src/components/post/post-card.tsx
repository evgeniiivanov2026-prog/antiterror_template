"use client";

import { useState, useTransition } from "react";
import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import { Repeat2, Trash2 } from "lucide-react";
import { toast } from "sonner";
import { deletePost } from "@/actions/social";
import { PostActions } from "@/components/post/post-actions";
import { PostContent } from "@/components/post/post-content";
import { RelativeTime } from "@/components/relative-time";
import { Avatar } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent } from "@/components/ui/dialog";
import { removeCachedPost } from "@/lib/post-cache";
import { safeHttpUrl } from "@/lib/utils";
import type { AuthorView, PostView } from "@/types/social";
import { useQueryClient } from "@tanstack/react-query";

export function PostCard({
  post,
  viewerId,
  repostedBy = null,
  variant = "feed",
}: {
  post: PostView;
  viewerId: string;
  repostedBy?: AuthorView | null;
  variant?: "feed" | "detail" | "ancestor";
}) {
  const router = useRouter();
  const pathname = usePathname();
  const queryClient = useQueryClient();
  const [confirm, setConfirm] = useState(false);
  const [pending, startTransition] = useTransition();
  const href = `/${post.author.username}/status/${post.id}`;
  const media = post.mediaUrls.map(safeHttpUrl).filter((url): url is string => Boolean(url));
  const isOwn = post.author.id === viewerId;

  return (
    <article
      className={variant === "detail" ? "px-4 py-3" : "cursor-pointer px-4 pt-3 transition-colors hover:bg-white/[0.03]"}
      onClick={
        variant === "feed"
          ? (event) => {
              const target = event.target as HTMLElement;
              if (target.closest("a, button, dialog")) return;
              router.push(href);
            }
          : undefined
      }
    >
      {repostedBy ? (
        <div className="mb-1 flex items-center gap-3 pl-8 text-[13px] font-bold text-muted">
          <Repeat2 className="size-4" />
          <Link href={`/${repostedBy.username}`} className="hover:underline" onClick={(event) => event.stopPropagation()}>
            {repostedBy.displayName} сделал(а) репост
          </Link>
        </div>
      ) : null}
      <div className="flex gap-3">
        <div className="flex flex-col items-center">
          <Link href={`/${post.author.username}`} onClick={(event) => event.stopPropagation()}>
            <Avatar src={post.author.avatarUrl} name={post.author.displayName} size={variant === "detail" ? 48 : 40} />
          </Link>
          {variant === "ancestor" ? <div className="my-1 w-0.5 flex-1 bg-border" /> : null}
        </div>
        <div className={`min-w-0 flex-1 ${variant === "ancestor" ? "pb-3" : "pb-2"}`}>
          <div className="flex items-start justify-between gap-3">
            <div className="flex min-w-0 flex-wrap items-center gap-x-1 text-[15px] leading-5">
              <Link
                href={`/${post.author.username}`}
                className="truncate font-bold hover:underline"
                onClick={(event) => event.stopPropagation()}
              >
                {post.author.displayName}
              </Link>
              <Link href={`/${post.author.username}`} className="truncate text-muted" onClick={(event) => event.stopPropagation()}>
                @{post.author.username}
              </Link>
              <span className="text-muted">·</span>
              <Link href={href} className="text-muted hover:underline" onClick={(event) => event.stopPropagation()}>
                <RelativeTime iso={post.createdAt} />
              </Link>
            </div>
            {isOwn ? (
              <button
                type="button"
                aria-label="Удалить пост"
                className="rounded-full p-1.5 text-muted hover:bg-[#f4212e]/10 hover:text-[#f4212e]"
                onClick={(event) => {
                  event.stopPropagation();
                  setConfirm(true);
                }}
              >
                <Trash2 className="size-4" />
              </button>
            ) : null}
          </div>
          {post.parent && variant !== "ancestor" ? (
            <p className="text-[15px] text-muted">
              В ответ{" "}
              <Link href={`/${post.parent.authorUsername}`} className="text-accent hover:underline" onClick={(event) => event.stopPropagation()}>
                @{post.parent.authorUsername}
              </Link>
            </p>
          ) : null}
          <PostContent
            text={post.content}
            className={
              variant === "detail"
                ? "mt-3 whitespace-pre-wrap break-words text-[17px] leading-6"
                : "mt-1 whitespace-pre-wrap break-words text-[15px] leading-5"
            }
          />
          {media.length > 0 ? (
            <div className={`mt-3 grid gap-0.5 overflow-hidden rounded-2xl border border-border ${media.length > 1 ? "grid-cols-2" : "grid-cols-1"}`}>
              {media.map((url) => (
                <a key={url} href={url} target="_blank" rel="noreferrer noopener" onClick={(event) => event.stopPropagation()}>
                  <img src={url} alt="" className="max-h-80 w-full object-cover" />
                </a>
              ))}
            </div>
          ) : null}
          {variant !== "ancestor" ? <PostActions post={post} /> : null}
        </div>
      </div>
      <Dialog open={confirm} onOpenChange={setConfirm}>
        <DialogContent title="Удалить пост?">
          <div className="space-y-4 px-6 py-5">
            <p className="text-[15px] leading-5 text-muted">Пост исчезнет из ленты вместе с ответами. Это нельзя отменить.</p>
            <div className="flex justify-end gap-3">
              <Button type="button" variant="outline" onClick={() => setConfirm(false)}>
                Отмена
              </Button>
              <Button
                type="button"
                variant="danger"
                disabled={pending}
                onClick={() => {
                  startTransition(async () => {
                    const result = await deletePost(post.id);
                    if (!result.ok) {
                      toast.error(result.error);
                      return;
                    }
                    removeCachedPost(queryClient, post.id);
                    setConfirm(false);
                    toast.success("Пост удалён");
                    if (pathname === href) router.push(`/${post.author.username}`);
                    else router.refresh();
                  });
                }}
              >
                Удалить
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </article>
  );
}
