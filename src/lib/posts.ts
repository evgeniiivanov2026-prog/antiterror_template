import "server-only";
import { Prisma } from "@/generated/prisma/client";
import { encodeCursor, newerThan, olderThan, requireCursor } from "@/lib/cursor";
import { prisma } from "@/lib/prisma";
import { postInclude, toPostView, type PostRecord } from "@/lib/serializers";
import type { CursorPage, PostView, ProfileTab } from "@/types/social";

function pageOf(rows: PostRecord[], limit: number): CursorPage<PostView> {
  const hasMore = rows.length > limit;
  const page = hasMore ? rows.slice(0, limit) : rows;
  const last = page[page.length - 1];
  return {
    items: page.map(toPostView),
    nextCursor: hasMore && last ? encodeCursor(last.createdAt, last.id) : null,
  };
}

export async function getPost(postId: string, userId: string) {
  return prisma.post.findUnique({
    where: { id: postId },
    include: postInclude(userId),
  });
}

export async function getAncestors(parentId: string | null, userId: string): Promise<PostView[]> {
  if (!parentId) return [];
  const rows = await prisma.$queryRaw<Array<{ id: string; depth: number }>>(Prisma.sql`
    WITH RECURSIVE chain AS (
      SELECT id, "parentId", 0 AS depth
      FROM "Post"
      WHERE id = ${parentId}
      UNION ALL
      SELECT p.id, p."parentId", c.depth + 1
      FROM "Post" p
      INNER JOIN chain c ON p.id = c."parentId"
      WHERE c.depth < 25
    )
    SELECT id, depth FROM chain ORDER BY depth DESC
  `);
  if (rows.length === 0) return [];
  const posts = await prisma.post.findMany({
    where: { id: { in: rows.map((row) => row.id) } },
    include: postInclude(userId),
  });
  const byId = new Map(posts.map((post) => [post.id, post as PostRecord]));
  return rows.flatMap((row) => {
    const post = byId.get(row.id);
    return post ? [toPostView(post)] : [];
  });
}

export async function getReplies(postId: string, userId: string, cursorRaw?: string, limit = 20) {
  const cursor = requireCursor(cursorRaw);
  const rows = await prisma.post.findMany({
    where: { parentId: postId, ...newerThan(cursor) },
    orderBy: [{ createdAt: "asc" }, { id: "asc" }],
    take: limit + 1,
    include: postInclude(userId),
  });
  return pageOf(rows as PostRecord[], limit);
}

export async function getProfilePosts(
  authorId: string,
  viewerId: string,
  tab: Exclude<ProfileTab, "likes">,
  cursorRaw?: string,
  limit = 20,
) {
  const cursor = requireCursor(cursorRaw);
  const where =
    tab === "replies"
      ? { authorId, parentId: { not: null } }
      : tab === "media"
        ? { authorId, NOT: { mediaUrls: { isEmpty: true } } }
        : { authorId, parentId: null };

  const rows = await prisma.post.findMany({
    where: { ...where, ...olderThan(cursor) },
    orderBy: [{ createdAt: "desc" }, { id: "desc" }],
    take: limit + 1,
    include: postInclude(viewerId),
  });
  return pageOf(rows as PostRecord[], limit);
}

export async function getLikedPosts(profileId: string, viewerId: string, cursorRaw?: string, limit = 20) {
  const cursor = requireCursor(cursorRaw);
  const rows = await prisma.like.findMany({
    where: { userId: profileId, ...olderThan(cursor, "postId") },
    orderBy: [{ createdAt: "desc" }, { postId: "desc" }],
    take: limit + 1,
    include: { post: { include: postInclude(viewerId) } },
  });
  const hasMore = rows.length > limit;
  const page = hasMore ? rows.slice(0, limit) : rows;
  const last = page[page.length - 1];
  return {
    items: page.map((row) => toPostView(row.post as PostRecord)),
    nextCursor: hasMore && last ? encodeCursor(last.createdAt, last.postId) : null,
  } satisfies CursorPage<PostView>;
}

export async function getBookmarks(userId: string, cursorRaw?: string, limit = 20) {
  const cursor = requireCursor(cursorRaw);
  const rows = await prisma.bookmark.findMany({
    where: { userId, ...olderThan(cursor, "postId") },
    orderBy: [{ createdAt: "desc" }, { postId: "desc" }],
    take: limit + 1,
    include: { post: { include: postInclude(userId) } },
  });
  const hasMore = rows.length > limit;
  const page = hasMore ? rows.slice(0, limit) : rows;
  const last = page[page.length - 1];
  return {
    items: page.map((row) => toPostView(row.post as PostRecord)),
    nextCursor: hasMore && last ? encodeCursor(last.createdAt, last.postId) : null,
  } satisfies CursorPage<PostView>;
}
