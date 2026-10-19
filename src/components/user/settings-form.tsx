"use client";

import { useState, useTransition } from "react";
import { useSession } from "next-auth/react";
import { updateProfileAction } from "@/lib/actions/profile";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import type { SessionUser } from "@/types";

export function SettingsForm({ user, bio }: { user: SessionUser; bio: string }) {
  const { update } = useSession();
  const [message, setMessage] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isPending, startTransition] = useTransition();

  function onSubmit(formData: FormData) {
    setMessage(null);
    setError(null);
    startTransition(async () => {
      const result = await updateProfileAction({
        displayName: formData.get("displayName"),
        bio: formData.get("bio"),
        avatarUrl: formData.get("avatarUrl"),
        coverUrl: formData.get("coverUrl"),
      });
      if (!result.ok) {
        const first =
          result.error.displayName?.[0] ??
          result.error.bio?.[0] ??
          result.error.avatarUrl?.[0] ??
          result.error.coverUrl?.[0] ??
          "Не удалось сохранить";
        setError(first);
        return;
      }
      await update({
        displayName: result.user.displayName,
        avatarUrl: result.user.avatarUrl,
      });
      setMessage("Профиль обновлён");
    });
  }

  return (
    <form action={onSubmit} className="space-y-4 p-4">
      <label className="block text-sm text-muted">
        Имя
        <Input name="displayName" defaultValue={user.displayName} className="mt-1" />
      </label>
      <label className="block text-sm text-muted">
        О себе
        <Textarea
          name="bio"
          defaultValue={bio}
          maxLength={160}
          className="mt-1 min-h-24 rounded-xl border border-zinc-700 px-4 py-3 text-base"
        />
      </label>
      <label className="block text-sm text-muted">
        Ссылка на аватар
        <Input name="avatarUrl" defaultValue={user.avatarUrl ?? ""} className="mt-1" />
      </label>
      <label className="block text-sm text-muted">
        Ссылка на обложку
        <Input name="coverUrl" defaultValue="" className="mt-1" />
      </label>
      {error && <p className="text-sm text-rose-400">{error}</p>}
      {message && <p className="text-sm text-emerald-400">{message}</p>}
      <Button type="submit" disabled={isPending}>
        Сохранить
      </Button>
    </form>
  );
}
