import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";
import { formatDistanceToNowStrict } from "date-fns";
import { ru } from "date-fns/locale";

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function formatRelativeTime(date: Date | string) {
  const value = typeof date === "string" ? new Date(date) : date;
  return formatDistanceToNowStrict(value, { addSuffix: true, locale: ru });
}

export function formatCompactNumber(value: number) {
  return new Intl.NumberFormat("ru-RU", {
    notation: "compact",
    maximumFractionDigits: 1,
  }).format(value);
}

export function getInitials(name: string) {
  return name
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? "")
    .join("");
}

export function avatarColor(seed: string) {
  const palette = [
    "from-sky-500 to-blue-700",
    "from-violet-500 to-fuchsia-700",
    "from-emerald-500 to-teal-700",
    "from-amber-500 to-orange-700",
    "from-rose-500 to-pink-700",
    "from-cyan-500 to-indigo-700",
  ];
  let hash = 0;
  for (let i = 0; i < seed.length; i += 1) {
    hash = seed.charCodeAt(i) + ((hash << 5) - hash);
  }
  return palette[Math.abs(hash) % palette.length];
}

export const RESERVED_USERNAMES = new Set([
  "explore",
  "notifications",
  "bookmarks",
  "settings",
  "login",
  "register",
  "api",
  "home",
  "search",
  "compose",
  "status",
  "admin",
  "vox",
  "about",
  "i",
  "messages",
]);
