"use client";

import { useState } from "react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { ImagePlus, X } from "lucide-react";
import { useRouter } from "next/navigation";
import { toast } from "sonner";
import { createPost } from "@/actions/social";
import { Avatar } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { appendReply, patchCachedPost, prependFeedPost } from "@/lib/post-cache";
import { MAX_MEDIA, MAX_POST_LENGTH } from "@/lib/constants";
import { codePointLength, cn, safeHttpUrl } from "@/lib/utils";
import type { AuthorView } from "@/types/social";

export function ComposeForm({
  me,
  parentId = null,
  placeholder = "Что происходит?",
  autoFocus = false,
  onPosted,
}: {
  me: Pick<AuthorView, "displayName" | "username" | "avatarUrl">;
  parentId?: string | null;
  placeholder?: string;
  autoFocus?: boolean;
  onPosted?: () => void;
}) {
  const router = useRouter();
  const queryClient = useQueryClient();
  const [content, setContent] = useState("");
  const [mediaUrls, setMediaUrls] = useState<string[]>([]);
  const [draftUrl, setDraftUrl] = useState("");
  const [showMedia, setShowMedia] = useState(false);
  const length = codePointLength(content);
  const remaining = MAX_POST_LENGTH - length;
  const canSubmit = (content.trim().length > 0 || mediaUrls.length > 0) && remaining >= 0;

  const mutation = useMutation({
    mutationFn: async () => {
      const result = await createPost({ content, mediaUrls, parentId });
      if (!result.ok) throw new Error(result.error);
      return result.data;
    },
    onSuccess: (post) => {
      if (post.parent) {
        patchCachedPost(queryClient, post.parent.id, (current) => ({
          ...current,
          counts: { ...current.counts, replies: current.counts.replies + 1 },
        }));
        appendReply(queryClient, post.parent.id, post);
        void queryClient.invalidateQueries({ queryKey: ["replies", post.parent.id] });
      } else {
        prependFeedPost(queryClient, post);
      }
      void queryClient.invalidateQueries({ queryKey: ["profile"] });
      setContent("");
      setMediaUrls([]);
      setDraftUrl("");
      toast.success(parentId ? "Ответ опубликован" : "Пост опубликован");
      onPosted?.();
      router.refresh();
    },
    onError: (error) => {
      toast.error(error instanceof Error ? error.message : "Не удалось опубликовать");
    },
  });

  function addMedia() {
    const safe = safeHttpUrl(draftUrl.trim());
    if (!safe) {
      toast.error("Нужна ссылка http или https");
      return;
    }
    if (mediaUrls.includes(safe)) return;
    if (mediaUrls.length >= MAX_MEDIA) {
      toast.error("Не больше 4 изображений");
      return;
    }
    setMediaUrls((current) => [...current, safe]);
    setDraftUrl("");
  }

  return (
    <form
      id={parentId ? "reply" : undefined}
      className="flex gap-3"
      onSubmit={(event) => {
        event.preventDefault();
        if (!canSubmit || mutation.isPending) return;
        mutation.mutate();
      }}
    >
      <Avatar src={me.avatarUrl} name={me.displayName} size={40} />
      <div className="min-w-0 flex-1">
        <textarea
          value={content}
          autoFocus={autoFocus}
          maxLength={2000}
          rows={parentId ? 2 : 3}
          placeholder={placeholder}
          aria-label={placeholder}
          onChange={(event) => setContent(event.target.value)}
          className="min-h-14 w-full resize-none bg-transparent text-xl leading-6 text-foreground outline-none placeholder:text-muted"
        />
        {mediaUrls.length > 0 ? (
          <div className="mt-2 grid grid-cols-2 gap-2">
            {mediaUrls.map((url) => (
              <div key={url} className="relative overflow-hidden rounded-2xl border border-border">
                <img src={url} alt="" className="h-32 w-full object-cover" />
                <button
                  type="button"
                  aria-label="Убрать изображение"
                  className="absolute right-2 top-2 rounded-full bg-black/70 p-1"
                  onClick={() => setMediaUrls((current) => current.filter((item) => item !== url))}
                >
                  <X className="size-4" />
                </button>
              </div>
            ))}
          </div>
        ) : null}
        {showMedia ? (
          <div className="mt-3 flex gap-2">
            <input
              value={draftUrl}
              onChange={(event) => setDraftUrl(event.target.value)}
              placeholder="https://…"
              aria-label="Ссылка на изображение"
              className="h-10 min-w-0 flex-1 rounded border border-border bg-transparent px-3 text-sm outline-none focus:border-accent"
            />
            <Button type="button" variant="outline" size="sm" onClick={addMedia}>
              Добавить
            </Button>
          </div>
        ) : null}
        <div className="mt-3 flex items-center justify-between border-t border-border pt-3">
          <button
            type="button"
            aria-label="Добавить изображение"
            className="rounded-full p-2 text-accent hover:bg-accent/10"
            onClick={() => setShowMedia((value) => !value)}
          >
            <ImagePlus className="size-5" />
          </button>
          <div className="flex items-center gap-3">
            <span
              className={cn(
                "text-sm tabular-nums",
                remaining < 0 ? "text-[#f4212e]" : remaining <= 20 ? "text-[#ffd400]" : "text-muted",
              )}
            >
              {length > 0 ? remaining : ""}
            </span>
            <Button type="submit" disabled={!canSubmit || mutation.isPending} className="px-5">
              {mutation.isPending ? "Публикация…" : parentId ? "Ответить" : "Опубликовать"}
            </Button>
          </div>
        </div>
      </div>
    </form>
  );
}
