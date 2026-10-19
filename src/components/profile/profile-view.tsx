"use client";

import Link from "next/link";
import { useState } from "react";
import { CalendarDays } from "lucide-react";
import { InfinitePosts } from "@/components/feed/infinite-posts";
import { FollowButton } from "@/components/user/follow-button";
import { Avatar } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { formatJoined } from "@/lib/format";
import { cn, formatCount } from "@/lib/utils";
import type { CursorPage, PostView, Profile, ProfileTab } from "@/types/social";

const TABS: Array<{ id: ProfileTab; label: string }> = [
  { id: "posts", label: "Посты" },
  { id: "replies", label: "Ответы" },
  { id: "media", label: "Медиа" },
  { id: "likes", label: "Нравится" },
];

export function ProfileView({
  profile,
  tab,
  initial,
  viewerId,
}: {
  profile: Profile;
  tab: ProfileTab;
  initial: CursorPage<PostView>;
  viewerId: string;
}) {
  const [followers, setFollowers] = useState(profile.counts.followers);
  const [coverFailed, setCoverFailed] = useState(false);

  return (
    <div>
      <div className="relative h-48 bg-gradient-to-r from-[#16324f] to-[#0d2136]">
        {profile.coverUrl && !coverFailed ? (
          <img
            src={profile.coverUrl}
            alt=""
            className="h-full w-full object-cover"
            onError={() => setCoverFailed(true)}
          />
        ) : null}
      </div>
      <div className="px-4 pb-3">
        <div className="flex items-start justify-between">
          <Avatar
            src={profile.avatarUrl}
            name={profile.displayName}
            size={132}
            className="-mt-[66px] border-4 border-black text-3xl"
          />
          {profile.isMe ? (
            <Button asChild variant="outline" className="mt-3">
              <Link href="/settings">Изменить профиль</Link>
            </Button>
          ) : (
            <div className="mt-3">
              <FollowButton
                userId={profile.id}
                initialFollowing={profile.viewerFollows}
                onChange={(_following, count) => setFollowers(count)}
              />
            </div>
          )}
        </div>
        <h2 className="mt-3 text-xl font-extrabold">{profile.displayName}</h2>
        <p className="text-[15px] text-muted">@{profile.username}</p>
        {profile.bio ? <p className="mt-3 whitespace-pre-wrap text-[15px] leading-5">{profile.bio}</p> : null}
        <p className="mt-3 flex items-center gap-1 text-[15px] text-muted">
          <CalendarDays className="size-4" />
          Регистрация: {formatJoined(profile.createdAt)}
        </p>
        <div className="mt-3 flex gap-4 text-[15px]">
          <Link href={`/${profile.username}/following`} className="hover:underline">
            <span className="font-bold">{formatCount(profile.counts.following)}</span>{" "}
            <span className="text-muted">читаемых</span>
          </Link>
          <Link href={`/${profile.username}/followers`} className="hover:underline">
            <span className="font-bold">{formatCount(followers)}</span>{" "}
            <span className="text-muted">читателей</span>
          </Link>
        </div>
      </div>
      <div className="flex border-b border-border">
        {TABS.map((item) => {
          const href = item.id === "posts" ? `/${profile.username}` : `/${profile.username}?tab=${item.id}`;
          const active = tab === item.id;
          return (
            <Link
              key={item.id}
              href={href}
              className="relative flex-1 py-4 text-center text-[15px] hover:bg-white/5"
            >
              <span className={cn(active ? "font-bold" : "text-muted")}>{item.label}</span>
              {active ? <span className="absolute inset-x-[22%] bottom-0 h-1 rounded-full bg-accent" /> : null}
            </Link>
          );
        })}
      </div>
      <InfinitePosts
        queryKey={["profile", profile.username, tab]}
        initial={initial}
        endpoint={`/api/users/${profile.username}/posts?tab=${tab}`}
        viewerId={viewerId}
        empty={emptyText(tab)}
      />
    </div>
  );
}

function emptyText(tab: ProfileTab) {
  if (tab === "replies") return "Ответов пока нет";
  if (tab === "media") return "Постов с изображениями пока нет";
  if (tab === "likes") return "Отметок «Нравится» пока нет";
  return "Постов пока нет";
}
