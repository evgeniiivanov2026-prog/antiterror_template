"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useTransition } from "react";
import { Bell, Bookmark, Home, LogOut, Search, Settings, UserRound } from "lucide-react";
import { logout } from "@/actions/auth";
import { ComposeButton } from "@/components/post/compose-dialog";
import { Avatar } from "@/components/ui/avatar";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { cn } from "@/lib/utils";
import type { Me } from "@/types/social";

export function Sidebar({ me, unread }: { me: Me; unread: number }) {
  const pathname = usePathname();
  const [pending, startTransition] = useTransition();
  const items = [
    { href: "/", label: "Главная", icon: Home, active: pathname === "/" },
    { href: "/search", label: "Поиск", icon: Search, active: pathname.startsWith("/search") },
    { href: "/notifications", label: "Уведомления", icon: Bell, active: pathname.startsWith("/notifications"), badge: unread },
    { href: "/bookmarks", label: "Закладки", icon: Bookmark, active: pathname.startsWith("/bookmarks") },
    { href: `/${me.username}`, label: "Профиль", icon: UserRound, active: pathname === `/${me.username}` || pathname.startsWith(`/${me.username}/`) },
  ];

  return (
    <aside className="sticky top-0 hidden h-screen shrink-0 flex-col justify-between px-2 py-1 md:flex md:w-[76px] xl:w-[275px]">
      <div>
        <Link
          href="/"
          aria-label="Pulse"
          className="mb-1 ml-1 flex size-12 items-center justify-center rounded-full text-xl font-black text-accent hover:bg-white/10"
        >
          P
        </Link>
        <nav className="flex flex-col">
          {items.map((item) => {
            const Icon = item.icon;
            return (
              <Link
                key={item.href}
                href={item.href}
                className={cn(
                  "flex items-center gap-5 rounded-full px-3 py-3 text-xl hover:bg-white/10 xl:pr-6",
                  item.active && "font-bold",
                )}
              >
                <span className="relative">
                  <Icon className="size-7" strokeWidth={item.active ? 2.6 : 2} />
                  {item.badge ? (
                    <span className="absolute -right-1.5 -top-1 min-w-4 rounded-full bg-accent px-1 text-center text-[11px] leading-4 text-white">
                      {item.badge > 9 ? "9+" : item.badge}
                    </span>
                  ) : null}
                </span>
                <span className="hidden xl:inline">{item.label}</span>
              </Link>
            );
          })}
        </nav>
        <div className="mt-4 px-2">
          <ComposeButton me={me} iconOnly className="xl:hidden" />
          <ComposeButton me={me} className="hidden xl:inline-flex" />
        </div>
      </div>
      <DropdownMenu>
        <DropdownMenuTrigger className="mb-3 flex w-full items-center gap-3 rounded-full p-2 text-left hover:bg-white/10">
          <Avatar src={me.avatarUrl} name={me.displayName} size={40} />
          <span className="hidden min-w-0 flex-1 xl:block">
            <span className="block truncate font-bold">{me.displayName}</span>
            <span className="block truncate text-sm text-muted">@{me.username}</span>
          </span>
        </DropdownMenuTrigger>
        <DropdownMenuContent side="top" align="start" className="w-64">
          <DropdownMenuItem asChild>
            <Link href={`/${me.username}`}>Профиль</Link>
          </DropdownMenuItem>
          <DropdownMenuItem asChild>
            <Link href="/settings" className="flex items-center gap-2">
              <Settings className="size-4" /> Настройки
            </Link>
          </DropdownMenuItem>
          <DropdownMenuItem
            disabled={pending}
            className="flex items-center gap-2 text-[#f4212e]"
            onSelect={(event) => {
              event.preventDefault();
              startTransition(() => {
                void logout();
              });
            }}
          >
            <LogOut className="size-4" /> Выйти
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </aside>
  );
}
