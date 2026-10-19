"use client";

import { useEffect } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { Heart, Repeat2, MessageCircle, UserPlus } from "lucide-react";
import { markNotificationsReadAction } from "@/lib/actions/notifications";
import { queryKeys } from "@/lib/query-keys";
import { formatRelativeTime } from "@/lib/utils";
import { UserAvatar } from "@/components/user/user-avatar";
import type { NotificationItem } from "@/types";

const icons = {
  LIKE: Heart,
  REPOST: Repeat2,
  REPLY: MessageCircle,
  FOLLOW: UserPlus,
};

const labels = {
  LIKE: "нравится ваш пост",
  REPOST: "сделал(а) репост",
  REPLY: "ответил(а) вам",
  FOLLOW: "подписался(ась) на вас",
};

export function NotificationList() {
  const { data, isPending } = useQuery({
    queryKey: queryKeys.notifications,
    queryFn: async () => {
      const response = await fetch("/api/notifications");
      if (!response.ok) throw new Error("fail");
      return (await response.json()) as {
        items: NotificationItem[];
        unreadCount: number;
      };
    },
  });

  useEffect(() => {
    if (data && data.unreadCount > 0) {
      void markNotificationsReadAction();
    }
  }, [data]);

  if (isPending) {
    return <p className="px-4 py-8 text-center text-muted">Загрузка...</p>;
  }

  if (!data || data.items.length === 0) {
    return (
      <div className="px-8 py-16 text-center">
        <h2 className="text-2xl font-extrabold">Пока пусто</h2>
        <p className="mt-2 text-muted">Здесь появятся лайки, ответы и новые подписчики.</p>
      </div>
    );
  }

  return (
    <div>
      {data.items.map((item) => {
        const Icon = icons[item.type];
        const href = item.post
          ? `/${item.issuer.username}/status/${item.post.id}`
          : `/${item.issuer.username}`;
        return (
          <Link
            key={item.id}
            href={href}
            className="flex gap-3 border-b border-zinc-800 px-4 py-4 hover:bg-white/[0.03]"
          >
            <Icon className="mt-1 h-6 w-6 text-accent" />
            <div className="min-w-0">
              <UserAvatar name={item.issuer.displayName} src={item.issuer.avatarUrl} size="sm" />
              <p className="mt-2 text-[15px]">
                <span className="font-bold">{item.issuer.displayName}</span>{" "}
                <span className="text-muted">{labels[item.type]}</span>
              </p>
              {item.post && (
                <p className="mt-1 line-clamp-2 text-sm text-muted">{item.post.content}</p>
              )}
              <p className="mt-1 text-xs text-muted">{formatRelativeTime(item.createdAt)}</p>
            </div>
          </Link>
        );
      })}
    </div>
  );
}
