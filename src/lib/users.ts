import "server-only";
import { cache } from "react";
import { encodeCursor, requireCursor } from "@/lib/cursor";
import { prisma } from "@/lib/prisma";
import { authorSelect, toAuthor, toUserCard } from "@/lib/serializers";
import type { ConnectionTab, CursorPage, Me, Profile, UserCard } from "@/types/social";

const userCardSelect = (viewerId: string) => ({
  ...authorSelect,
  _count: { select: { followers: true } },
  followers: { where: { followerId: viewerId }, select: { followerId: true }, take: 1 },
});

export const getMe = cache(async (userId: string): Promise<Me | null> => {
  const user = await prisma.user.findUnique({
    where: { id: userId },
    select: {
      ...authorSelect,
      createdAt: true,
      _count: { select: { posts: true, followers: true, following: true } },
    },
  });
  if (!user) return null;
  return {
    ...toAuthor(user),
    createdAt: user.createdAt.toISOString(),
    counts: user._count,
  };
});

export async function getProfile(username: string, viewerId: string): Promise<Profile | null> {
  const user = await prisma.user.findUnique({
    where: { username: username.toLowerCase() },
    select: {
      ...authorSelect,
      coverUrl: true,
      createdAt: true,
      _count: { select: { posts: true, followers: true, following: true } },
      followers: { where: { followerId: viewerId }, select: { followerId: true }, take: 1 },
    },
  });
  if (!user) return null;
  return {
    ...toAuthor(user),
    coverUrl: user.coverUrl,
    createdAt: user.createdAt.toISOString(),
    counts: user._count,
    viewerFollows: user.followers.length > 0,
    isMe: user.id === viewerId,
  };
}

export async function getSuggestions(userId: string, take = 3): Promise<UserCard[]> {
  const users = await prisma.user.findMany({
    where: {
      id: { not: userId },
      followers: { none: { followerId: userId } },
    },
    orderBy: [{ followers: { _count: "desc" } }, { createdAt: "asc" }],
    take,
    select: userCardSelect(userId),
  });
  return users.map((user) => toUserCard(user, userId));
}

export async function getConnections(
  username: string,
  viewerId: string,
  kind: ConnectionTab,
  cursorRaw?: string,
  limit = 20,
): Promise<{ title: string; users: CursorPage<UserCard> } | null> {
  const profile = await prisma.user.findUnique({
    where: { username: username.toLowerCase() },
    select: { id: true, displayName: true, username: true },
  });
  if (!profile) return null;

  const cursor = requireCursor(cursorRaw);
  const cursorFilter = !cursor
    ? {}
    : kind === "followers"
      ? {
          OR: [
            { createdAt: { lt: cursor.at } },
            { createdAt: cursor.at, followerId: { lt: cursor.id } },
          ],
        }
      : {
          OR: [
            { createdAt: { lt: cursor.at } },
            { createdAt: cursor.at, followingId: { lt: cursor.id } },
          ],
        };
  const rows = await prisma.follow.findMany({
    where: {
      ...(kind === "followers" ? { followingId: profile.id } : { followerId: profile.id }),
      ...cursorFilter,
    },
    orderBy:
      kind === "followers"
        ? [{ createdAt: "desc" as const }, { followerId: "desc" as const }]
        : [{ createdAt: "desc" as const }, { followingId: "desc" as const }],
    take: limit + 1,
    include: {
      follower: { select: userCardSelect(viewerId) },
      following: { select: userCardSelect(viewerId) },
    },
  });

  const hasMore = rows.length > limit;
  const page = hasMore ? rows.slice(0, limit) : rows;
  const last = page[page.length - 1];
  const cursorId = last ? (kind === "followers" ? last.followerId : last.followingId) : "";

  return {
    title: kind === "followers" ? `Читатели ${profile.displayName}` : `${profile.displayName} читает`,
    users: {
      items: page.map((row) => toUserCard(kind === "followers" ? row.follower : row.following, viewerId)),
      nextCursor: hasMore && last ? encodeCursor(last.createdAt, cursorId) : null,
    },
  };
}
