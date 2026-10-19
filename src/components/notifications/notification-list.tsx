"use client";

import { useInfiniteQuery } from "@tanstack/react-query";
import { Heart, Repeat2, UserPlus, MessageCircle } from "lucide-react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useRef } from "react";
import { readNotifications } from "@/actions/social";
import { LoadMore } from "@/components/feed/load-more";
import { RelativeTime } from "@/components/relative-time";
import { Avatar } from "@/components/ui/avatar";
import { snippet } from "@/lib/format";
import type { CursorPage, NotificationView } from "@/types/social";

const COPY = {
  LIKE: "оценил(а) ваш пост",
  REPOST: "сделал(а) репост",
  REPLY: "ответил(а) вам",
  FOLLOW: "подписался(-ась) на вас",
} as const;

const ICONS = {
  LIKE: Heart,
  REPOST: Repeat2,
  REPLY: MessageCircle,
  FOLLOW: UserPlus,
} as const;

export function NotificationList({ initial }: { initial: CursorPage<NotificationView> }) {
  const router = useRouter();
  const marked = useRef(false);
  const query = useInfiniteQuery({
    queryKey: ["notifications"],
    initialPageParam: null as string | null,
    queryFn: async ({ pageParam }) => {
      const params = new URLSearchParams();
      if (pageParam) params.set("cursor", pageParam);
      const response = await fetch(`/api/notifications?${params}`);
      if (!response.ok) throw new Error("Не удалось загрузить уведомления");
      return (await response.json()) as CursorPage<NotificationView>;
    },
    getNextPageParam: (last) => last.nextCursor,
    initialData: { pages: [initial], pageParams: [null] },
  });

  useEffect(() => {
    if (marked.current) return;
    marked.current = true;
    void readNotifications().then((result) => {
      if (result.ok && result.data.updated > 0) router.refresh();
    });
  }, [router]);

  const items = query.data.pages.flatMap((page) => page.items);
  if (items.length === 0) {
    return <p className="px-8 py-16 text-center text-muted">Уведомлений пока нет</p>;
  }

  return (
    <div>
      {items.map((item) => {
        const Icon = ICONS[item.type];
        const href = item.post
          ? `/${item.post.authorUsername}/status/${item.post.id}`
          : `/${item.issuer.username}`;
        return (
          <Link
            key={item.id}
            href={href}
            className="flex gap-3 border-b border-border px-4 py-3 hover:bg-white/[0.03]"
          >
            <Icon className={`mt-1 size-7 shrink-0 ${item.type === "LIKE" ? "text-like" : item.type === "REPOST" ? "text-repost" : "text-accent"}`} />
            <div className="min-w-0">
              <div className="flex items-center gap-2">
                <Avatar src={item.issuer.avatarUrl} name={item.issuer.displayName} size={32} />
                {!item.read ? <span className="size-2 rounded-full bg-accent" /> : null}
              </div>
              <p className="mt-2 text-[15px] leading-5">
                <span className="font-bold">{item.issuer.displayName}</span> {COPY[item.type]}
                <span className="text-muted"> · <RelativeTime iso={item.createdAt} /></span>
              </p>
              {item.post ? <p className="mt-1 text-[15px] text-muted">{snippet(item.post.content)}</p> : null}
            </div>
          </Link>
        );
      })}
      <LoadMore
        hasMore={Boolean(query.hasNextPage)}
        loading={query.isFetchingNextPage}
        error={query.isFetchNextPageError}
        onLoad={() => void query.fetchNextPage()}
      />
    </div>
  );
}
