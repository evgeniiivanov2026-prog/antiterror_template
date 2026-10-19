"use client";

import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { ArrowLeft, Calendar } from "lucide-react";
import Link from "next/link";
import { format } from "date-fns";
import { ru } from "date-fns/locale";
import { queryKeys } from "@/lib/query-keys";
import { formatCompactNumber, cn } from "@/lib/utils";
import { UserAvatar } from "@/components/user/user-avatar";
import { FollowButton } from "@/components/user/follow-button";
import { Button } from "@/components/ui/button";
import { InfiniteFeed } from "@/components/post/infinite-feed";
import type { ProfileUser, SessionUser } from "@/types";

const filters = [
  ["posts", "Посты"],
  ["replies", "Ответы"],
  ["media", "Медиа"],
  ["likes", "Нравится"],
] as const;

export function ProfileView({
  username,
  currentUser,
}: {
  username: string;
  currentUser: SessionUser;
}) {
  const [filter, setFilter] = useState<(typeof filters)[number][0]>("posts");
  const { data: profile, isPending } = useQuery({
    queryKey: queryKeys.profile(username),
    queryFn: async () => {
      const response = await fetch(`/api/users/${username}`);
      if (!response.ok) throw new Error("not found");
      return (await response.json()) as ProfileUser;
    },
  });

  if (isPending) {
    return <p className="px-4 py-8 text-center text-muted">Загрузка профиля...</p>;
  }

  if (!profile) {
    return (
      <div className="px-8 py-16 text-center">
        <h1 className="text-2xl font-extrabold">Этот аккаунт не существует</h1>
      </div>
    );
  }

  return (
    <div>
      <header className="sticky top-0 z-20 flex items-center gap-6 border-b border-zinc-800 bg-black/75 px-4 py-2 backdrop-blur-md">
        <Link href="/" className="rounded-full p-2 hover:bg-white/10">
          <ArrowLeft className="h-5 w-5" />
        </Link>
        <div>
          <h1 className="text-xl font-bold">{profile.displayName}</h1>
          <p className="text-xs text-muted">{formatCompactNumber(profile.counts.posts)} постов</p>
        </div>
      </header>
      <div className="relative h-36 bg-zinc-800 sm:h-48">
        {profile.coverUrl && (
          // eslint-disable-next-line @next/next/no-img-element
          <img src={profile.coverUrl} alt="" className="h-full w-full object-cover" />
        )}
      </div>
      <div className="px-4">
        <div className="-mt-12 flex items-end justify-between">
          <UserAvatar
            name={profile.displayName}
            src={profile.avatarUrl}
            size="xl"
            className="border-4 border-black"
          />
          {profile.viewer.isSelf ? (
            <Button asChild variant="outline" size="sm">
              <Link href="/settings">Изменить профиль</Link>
            </Button>
          ) : (
            <FollowButton userId={profile.id} initialFollowing={profile.viewer.isFollowing} />
          )}
        </div>
        <h2 className="mt-3 text-xl font-extrabold">{profile.displayName}</h2>
        <p className="text-muted">@{profile.username}</p>
        {profile.bio && <p className="mt-3 whitespace-pre-wrap text-[15px]">{profile.bio}</p>}
        <p className="mt-3 flex items-center gap-2 text-sm text-muted">
          <Calendar className="h-4 w-4" />
          Регистрация: {format(new Date(profile.createdAt), "LLLL yyyy", { locale: ru })}
        </p>
        <div className="mt-3 flex gap-4 text-sm">
          <p>
            <span className="font-bold">{formatCompactNumber(profile.counts.following)}</span>{" "}
            <span className="text-muted">в читаемых</span>
          </p>
          <p>
            <span className="font-bold">{formatCompactNumber(profile.counts.followers)}</span>{" "}
            <span className="text-muted">читателей</span>
          </p>
        </div>
      </div>
      <div className="mt-4 grid grid-cols-4 border-b border-zinc-800">
        {filters.map(([value, label]) => (
          <button
            key={value}
            type="button"
            onClick={() => setFilter(value)}
            className={cn(
              "relative h-[53px] text-sm hover:bg-white/5",
              filter === value ? "font-bold" : "text-muted",
            )}
          >
            {label}
            {filter === value && (
              <span className="absolute bottom-0 left-1/2 h-1 w-12 -translate-x-1/2 rounded-full bg-accent" />
            )}
          </button>
        ))}
      </div>
      <InfiniteFeed
        queryKey={queryKeys.userPosts(username, filter)}
        path={`/api/users/${username}/posts?filter=${filter}`}
        currentUser={currentUser}
        emptyTitle="Здесь пока ничего нет"
        emptyDescription="Посты появятся, как только автор что-нибудь опубликует."
      />
    </div>
  );
}
