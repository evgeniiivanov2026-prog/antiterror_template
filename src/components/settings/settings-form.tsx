"use client";

import { useState, useTransition } from "react";
import { useRouter } from "next/navigation";
import { toast } from "sonner";
import { updateProfile } from "@/actions/social";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import type { Me } from "@/types/social";

export function SettingsForm({ me }: { me: Me & { coverUrl: string | null } }) {
  const router = useRouter();
  const [pending, startTransition] = useTransition();
  const [displayName, setDisplayName] = useState(me.displayName);
  const [username, setUsername] = useState(me.username);
  const [bio, setBio] = useState(me.bio);
  const [avatarUrl, setAvatarUrl] = useState(me.avatarUrl ?? "");
  const [coverUrl, setCoverUrl] = useState(me.coverUrl ?? "");

  return (
    <form
      className="space-y-4 px-4 py-4"
      onSubmit={(event) => {
        event.preventDefault();
        startTransition(async () => {
          const result = await updateProfile({ displayName, username, bio, avatarUrl, coverUrl });
          if (!result.ok) {
            toast.error(result.error);
            return;
          }
          toast.success("Профиль обновлён");
          router.push(`/${result.data.username}`);
          router.refresh();
        });
      }}
    >
      <Field label="Имя" value={displayName} onChange={setDisplayName} maxLength={50} />
      <Field label="Имя пользователя" value={username} onChange={setUsername} prefix="@" maxLength={15} />
      <label className="block text-sm text-muted">
        Биография
        <textarea
          value={bio}
          maxLength={160}
          onChange={(event) => setBio(event.target.value)}
          className="mt-2 min-h-28 w-full rounded border border-border bg-transparent p-3 text-[15px] text-foreground outline-none focus:border-accent"
        />
        <span className="mt-1 block text-right">{bio.length}/160</span>
      </label>
      <Field label="Ссылка на аватар" value={avatarUrl} onChange={setAvatarUrl} />
      <Field label="Ссылка на обложку" value={coverUrl} onChange={setCoverUrl} />
      <Button type="submit" disabled={pending} className="w-full" size="lg">
        {pending ? "Сохраняем…" : "Сохранить"}
      </Button>
    </form>
  );
}

function Field({
  label,
  value,
  onChange,
  prefix,
  maxLength,
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
  prefix?: string;
  maxLength?: number;
}) {
  return (
    <label className="block text-sm text-muted">
      {label}
      <div className="relative mt-2">
        {prefix ? <span className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted">{prefix}</span> : null}
        <Input value={value} maxLength={maxLength} onChange={(event) => onChange(event.target.value)} className={prefix ? "pl-8" : undefined} />
      </div>
    </label>
  );
}
