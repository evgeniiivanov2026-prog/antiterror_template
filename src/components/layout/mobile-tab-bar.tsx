"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { Bell, Home, Search, User } from "lucide-react";
import { cn } from "@/lib/utils";

export function MobileTabBar({
  username,
  unreadCount,
}: {
  username: string;
  unreadCount: number;
}) {
  const pathname = usePathname();
  const items = [
    { href: "/", icon: Home, label: "Главная" },
    { href: "/explore", icon: Search, label: "Поиск" },
    { href: "/notifications", icon: Bell, label: "Уведомления" },
    { href: `/${username}`, icon: User, label: "Профиль" },
  ];

  return (
    <nav className="fixed inset-x-0 bottom-0 z-30 flex h-14 items-center justify-around border-t border-zinc-800 bg-black/90 backdrop-blur md:hidden">
      {items.map(({ href, icon: Icon, label }) => (
        <Link
          key={href}
          href={href}
          aria-label={label}
          className="relative flex h-full w-full items-center justify-center text-foreground"
        >
          <Icon className={cn("h-6 w-6", pathname === href && "stroke-[2.5]")} />
          {href === "/notifications" && unreadCount > 0 && (
            <span className="absolute right-6 top-2 h-2 w-2 rounded-full bg-accent" />
          )}
        </Link>
      ))}
    </nav>
  );
}
