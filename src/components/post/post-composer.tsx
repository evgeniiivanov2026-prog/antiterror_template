"use client";

import { useState, useTransition } from "react";
import { ImagePlus, X } from "lucide-react";
import { useQueryClient } from "@tanstack/react-query";
import { createPostAction } from "@/lib/actions/post";
import { queryKeys } from "@/lib/query-keys";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { UserAvatar } from "@/components/user/user-avatar";
import { CharacterRing } from "@/components/post/character-ring";
import type { SessionUser } from "@/types";

type PostComposerProps = {
  user: SessionUser;
  parentId?: string;
  placeholder?: string;
  onCreated?: () => void;
  compact?: boolean;
};

export function PostComposer({
  user,
  parentId,
  placeholder = "Что происходит?",
  onCreated,
  compact = false,
}: PostComposerProps) {
  const queryClient = useQueryClient();
  const [content, setContent] = useState("");
  const [mediaUrls, setMediaUrls] = useState<string[]>([]);
  const [error, setError] = useState<string | null>(null);
  const [isPending, startTransition] = useTransition();

  const remainingValid = content.trim().length > 0 && content.length <= 280;

  async function handleUpload(file: File) {
    const formData = new FormData();
    formData.append("file", file);
    const response = await fetch("/api/upload", { method: "POST", body: formData });
    const data = (await response.json()) as { url?: string; error?: string };
    if (!response.ok || !data.url) {
      setError(data.error ?? "Не удалось загрузить файл");
      return;
    }
    setMediaUrls((current) => [...current, data.url!].slice(0, 4));
  }

  function submit() {
    if (!remainingValid) return;
    setError(null);
    startTransition(async () => {
      const result = await createPostAction({
        content,
        mediaUrls,
        parentId,
      });
      if (!result.ok) {
        setError(result.error.content?.[0] ?? "Не удалось опубликовать");
        return;
      }
      setContent("");
      setMediaUrls([]);
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.feed("for-you") }),
        queryClient.invalidateQueries({ queryKey: queryKeys.feed("following") }),
        parentId
          ? queryClient.invalidateQueries({ queryKey: queryKeys.replies(parentId) })
          : Promise.resolve(),
        queryClient.invalidateQueries({ queryKey: queryKeys.trending }),
      ]);
      onCreated?.();
    });
  }

  return (
    <div className={compact ? "" : "border-b border-zinc-800 px-4 py-3"}>
      <div className="flex gap-3">
        <UserAvatar name={user.displayName} src={user.avatarUrl} />
        <div className="min-w-0 flex-1">
          <Textarea
            value={content}
            onChange={(event) => setContent(event.target.value)}
            placeholder={placeholder}
            rows={compact ? 2 : 3}
            maxLength={300}
          />
          {mediaUrls.length > 0 && (
            <div className="mt-3 grid grid-cols-2 gap-2">
              {mediaUrls.map((url) => (
                <div key={url} className="relative overflow-hidden rounded-2xl">
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img src={url} alt="" className="h-40 w-full object-cover" />
                  <button
                    type="button"
                    className="absolute right-2 top-2 rounded-full bg-black/70 p-1"
                    onClick={() =>
                      setMediaUrls((current) => current.filter((item) => item !== url))
                    }
                  >
                    <X className="h-4 w-4" />
                  </button>
                </div>
              ))}
            </div>
          )}
          {error && <p className="mt-2 text-sm text-rose-400">{error}</p>}
          <div className="mt-3 flex items-center justify-between border-t border-zinc-800 pt-3">
            <label className="cursor-pointer rounded-full p-2 text-accent hover:bg-accent/10">
              <ImagePlus className="h-5 w-5" />
              <input
                type="file"
                accept="image/png,image/jpeg,image/webp,image/gif"
                className="hidden"
                onChange={(event) => {
                  const file = event.target.files?.[0];
                  if (file) void handleUpload(file);
                  event.target.value = "";
                }}
              />
            </label>
            <div className="flex items-center gap-3">
              {content.length > 0 && <CharacterRing value={content.length} />}
              <Button
                type="button"
                size="sm"
                disabled={!remainingValid || isPending}
                onClick={submit}
              >
                {parentId ? "Ответить" : "Опубликовать"}
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
