import "server-only";
import { olderThan, requireCursor } from "@/lib/cursor";
import { encodeCursor } from "@/lib/cursor";
import { prisma } from "@/lib/prisma";
import { postInclude, toPostView, toUserCard, type PostRecord } from "@/lib/serializers";
import type { SearchResults } from "@/types/social";

const userCardSelect = (viewerId: string) => ({
  id: true,
  username: true,
  displayName: true,
  avatarUrl: true,
  bio: true,
  _count: { select: { followers: true } },
  followers: { where: { followerId: viewerId }, select: { followerId: true }, take: 1 },
});

export async function searchAll(
  query: string,
  userId: string,
  cursorRaw?: string,
  limit = 20,
): Promise<SearchResults> {
  const cursor = requireCursor(cursorRaw);
  const raw = query.trim();
  const isTag = raw.startsWith("#");
  const isMention = raw.startsWith("@");
  const term = (isTag || isMention ? raw.slice(1) : raw).trim();
  const contentQuery = isTag ? `#${term}` : isMention ? `@${term}` : raw;

  const users =
    cursor || isTag || term.length === 0
      ? []
      : await prisma.user.findMany({
          where: {
            OR: [
              { username: { contains: term, mode: "insensitive" } },
              { displayName: { contains: isMention ? term : raw, mode: "insensitive" } },
            ],
          },
          orderBy: [{ followers: { _count: "desc" } }, { username: "asc" }],
          take: 8,
          select: userCardSelect(userId),
        });

  const rows = await prisma.post.findMany({
    where: {
      content: { contains: contentQuery, mode: "insensitive" },
      ...olderThan(cursor),
    },
    orderBy: [{ createdAt: "desc" }, { id: "desc" }],
    take: limit + 1,
    include: postInclude(userId),
  });

  const hasMore = rows.length > limit;
  const page = hasMore ? rows.slice(0, limit) : rows;
  const last = page[page.length - 1];

  return {
    users: users.map((user) => toUserCard(user, userId)),
    posts: {
      items: page.map((post) => toPostView(post as PostRecord)),
      nextCursor: hasMore && last ? encodeCursor(last.createdAt, last.id) : null,
    },
  };
}
