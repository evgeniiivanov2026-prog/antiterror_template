import "server-only";
import { Prisma } from "@/generated/prisma/client";
import { requireCursor } from "@/lib/cursor";
import { encodeCursor } from "@/lib/cursor";
import { prisma } from "@/lib/prisma";
import { authorSelect, postInclude, toFeedItem, type PostRecord } from "@/lib/serializers";
import type { AuthorView, FeedPage, FeedTab } from "@/types/social";

type FeedRow = {
  kind: string;
  sort_at: Date;
  sort_id: string;
  post_id: string;
  reposter_id: string | null;
};

export async function getFeed(options: {
  userId: string;
  tab: FeedTab;
  cursor?: string;
  limit?: number;
}): Promise<FeedPage> {
  const limit = options.limit ?? 20;
  const cursor = requireCursor(options.cursor);
  const rows =
    options.tab === "following"
      ? await queryFollowing(options.userId, cursor, limit)
      : await queryForYou(cursor, limit);
  return hydrateFeed(rows, options.userId, limit);
}

async function queryForYou(cursor: { at: Date; id: string } | null, limit: number) {
  const cursorClause = cursor
    ? Prisma.sql`AND (p."createdAt", p.id) < (${cursor.at}, ${cursor.id})`
    : Prisma.empty;

  return prisma.$queryRaw<FeedRow[]>(Prisma.sql`
    SELECT
      'post'::text AS kind,
      p."createdAt" AS sort_at,
      p.id AS sort_id,
      p.id AS post_id,
      NULL::text AS reposter_id
    FROM "Post" p
    WHERE p."parentId" IS NULL
    ${cursorClause}
    ORDER BY p."createdAt" DESC, p.id DESC
    LIMIT ${limit + 1}
  `);
}

async function queryFollowing(userId: string, cursor: { at: Date; id: string } | null, limit: number) {
  const follows = await prisma.follow.findMany({
    where: { followerId: userId },
    select: { followingId: true },
  });
  const ids = [userId, ...follows.map((follow) => follow.followingId)];
  const postCursor = cursor
    ? Prisma.sql`AND (p."createdAt", p.id) < (${cursor.at}, ${cursor.id})`
    : Prisma.empty;
  const repostCursor = cursor
    ? Prisma.sql`AND (r."createdAt", r."userId" || ':' || r."postId") < (${cursor.at}, ${cursor.id})`
    : Prisma.empty;

  return prisma.$queryRaw<FeedRow[]>(Prisma.sql`
    SELECT kind, sort_at, sort_id, post_id, reposter_id
    FROM (
      SELECT
        'post'::text AS kind,
        p."createdAt" AS sort_at,
        p.id AS sort_id,
        p.id AS post_id,
        NULL::text AS reposter_id
      FROM "Post" p
      WHERE p."parentId" IS NULL
        AND p."authorId" IN (${Prisma.join(ids)})
        ${postCursor}
      UNION ALL
      SELECT
        'repost'::text,
        r."createdAt",
        r."userId" || ':' || r."postId",
        r."postId",
        r."userId"
      FROM "Repost" r
      INNER JOIN "Post" p ON p.id = r."postId"
      WHERE p."parentId" IS NULL
        AND r."userId" IN (${Prisma.join(ids)})
        ${repostCursor}
    ) feed
    ORDER BY sort_at DESC, sort_id DESC
    LIMIT ${limit + 1}
  `);
}

async function hydrateFeed(rows: FeedRow[], userId: string, limit: number): Promise<FeedPage> {
  const hasMore = rows.length > limit;
  const page = hasMore ? rows.slice(0, limit) : rows;
  if (page.length === 0) return { items: [], nextCursor: null };

  const postIds = [...new Set(page.map((row) => row.post_id))];
  const reposterIds = [
    ...new Set(page.map((row) => row.reposter_id).filter((id): id is string => Boolean(id))),
  ];

  const [posts, reposters] = await Promise.all([
    prisma.post.findMany({
      where: { id: { in: postIds } },
      include: postInclude(userId),
    }),
    reposterIds.length > 0
      ? prisma.user.findMany({
          where: { id: { in: reposterIds } },
          select: authorSelect,
        })
      : Promise.resolve([] as AuthorView[]),
  ]);

  const postMap = new Map(posts.map((post) => [post.id, post as PostRecord]));
  const userMap = new Map(reposters.map((user) => [user.id, user]));
  const items = page.flatMap((row) => {
    const post = postMap.get(row.post_id);
    if (!post) return [];
    const reposter = row.reposter_id ? (userMap.get(row.reposter_id) ?? null) : null;
    return [toFeedItem(row, post, reposter)];
  });

  const last = page[page.length - 1];
  return {
    items,
    nextCursor: hasMore && last ? encodeCursor(new Date(last.sort_at), last.sort_id) : null,
  };
}
