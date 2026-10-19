"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import {
  Bell,
  Bookmark,
  Home,
  MoreHorizontal,
  PenSquare,
  Search,
  Settings,
  User,
} from "lucide-react";
import { signOutAction } from "@/lib/actions/session";
import { Button } from "@/components/ui/button";
import { UserAvatar } from "@/components/user/user-avatar";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { cn } from "@/lib/utils";
import type { SessionUser } from "@/types";

const links = [
  { href: "/", label: "Главная", icon: Home },
  { href: "/explore", label: "Поиск", icon: Search },
  { href: "/notifications", label: "Уведомления", icon: Bell },
  { href: "/bookmarks", label: "Закладки", icon: Bookmark },
  { href: "/settings", label: "Настройки", icon: Settings },
];

export function Sidebar({
  user,
  unreadCount,
  onCompose,
}: {
  user: SessionUser;
  unreadCount: number;
  onCompose: () => void;
}) {
  const pathname = usePathname();

  return (
    <aside className="sticky top-0 hidden h-screen w-[72px] shrink-0 flex-col justify-between px-2 py-3 md:flex xl:w-[275px] xl:pr-6">
      <div className="flex flex-col gap-1">
        <Link
          href="/"
          className="mb-2 flex h-12 w-12 items-center justify-center rounded-full hover:bg-white/10 xl:ml-1"
          aria-label="Vox"
        >
          <span className="flex h-9 w-9 items-center justify-center rounded-full bg-white text-lg font-black text-black">
            V
          </span>
        </Link>
        {links.map(({ href, label, icon: Icon }) => {
          const active = pathname === href;
          return (
            <Link
              key={href}
              href={href}
              className="flex items-center gap-5 rounded-full px-3 py-3 hover:bg-white/10"
            >
              <span className="relative">
                <Icon className={cn("h-7 w-7", active && "stroke-[2.4]")} />
                {href === "/notifications" && unreadCount > 0 && (
                  <span className="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-accent px-1 text-[10px] font-bold text-white">
                    {unreadCount > 9 ? "9+" : unreadCount}
                  </span>
                )}
              </span>
              <span className={cn("hidden text-xl xl:inline", active && "font-bold")}>
                {label}
              </span>
            </Link>
          );
        })}
        <Link
          href={`/${user.username}`}
          className="flex items-center gap-5 rounded-full px-3 py-3 hover:bg-white/10"
        >
          <User className={cn("h-7 w-7", pathname === `/${user.username}` && "stroke-[2.4]")} />
          <span
            className={cn(
              "hidden text-xl xl:inline",
              pathname === `/${user.username}` && "font-bold",
            )}
          >
            Профиль
          </span>
        </Link>
        <Button className="mt-3 hidden h-[52px] w-full text-base xl:flex" onClick={onCompose}>
          Опубликовать
        </Button>
        <Button
          size="icon"
          className="mt-3 h-12 w-12 xl:hidden"
          onClick={onCompose}
          aria-label="Опубликовать"
        >
          <PenSquare className="h-5 w-5" />
        </Button>
      </div>
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <button
            type="button"
            className="flex w-full items-center gap-3 rounded-full p-3 text-left hover:bg-white/10"
          >
            <UserAvatar name={user.displayName} src={user.avatarUrl} />
            <div className="hidden min-w-0 flex-1 xl:block">
              <p className="truncate font-bold">{user.displayName}</p>
              <p className="truncate text-sm text-muted">@{user.username}</p>
            </div>
            <MoreHorizontal className="hidden h-5 w-5 xl:block" />
          </button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" className="w-64">
          <DropdownMenuItem onSelect={() => void signOutAction()}>
            Выйти из @{user.username}
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </aside>
  );
}
