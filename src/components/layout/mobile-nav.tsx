"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { Bell, Bookmark, Home, Search, UserRound } from "lucide-react";
import { cn } from "@/lib/utils";
import type { Me } from "@/types/social";

export function MobileNav({ me, unread }: { me: Me; unread: number }) {
  const pathname = usePathname();
  const items = [
    { href: "/", label: "Главная", icon: Home, active: pathname === "/" },
    { href: "/search", label: "Поиск", icon: Search, active: pathname.startsWith("/search") },
    { href: "/notifications", label: "Уведомления", icon: Bell, active: pathname.startsWith("/notifications"), badge: unread },
    { href: "/bookmarks", label: "Закладки", icon: Bookmark, active: pathname.startsWith("/bookmarks") },
    { href: `/${me.username}`, label: "Профиль", icon: UserRound, active: pathname === `/${me.username}` || pathname.startsWith(`/${me.username}/`) },
  ];

  return (
    <nav className="fixed inset-x-0 bottom-0 z-40 flex border-t border-border bg-black md:hidden">
      {items.map((item) => {
        const Icon = item.icon;
        return (
          <Link
            key={item.href}
            href={item.href}
            aria-label={item.label}
            className={cn("relative flex flex-1 items-center justify-center py-3", item.active ? "text-foreground" : "text-muted")}
          >
            <Icon className="size-6" strokeWidth={item.active ? 2.6 : 2} />
            {item.badge ? (
              <span className="absolute right-[calc(50%-18px)] top-2 min-w-4 rounded-full bg-accent px-1 text-center text-[10px] leading-4 text-white">
                {item.badge > 9 ? "9+" : item.badge}
              </span>
            ) : null}
          </Link>
        );
      })}
    </nav>
  );
}
