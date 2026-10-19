import type { Prisma } from "@prisma/client";
import { prisma } from "@/lib/prisma";
import { extractHashtags } from "@/lib/hashtags";
import type { SerializedPost } from "@/types";

export const postAuthorSelect = {
  id: true,
  username: true,
  displayName: true,
  avatarUrl: true,
} satisfies Prisma.UserSelect;

export function postInclude(currentUserId?: string | null) {
  return {
    author: { select: postAuthorSelect },
    _count: {
      select: {
        likes: true,
        reposts: true,
        replies: true,
      },
    },
    likes: currentUserId
      ? { where: { userId: currentUserId }, select: { userId: true } }
      : { take: 0, select: { userId: true } },
    reposts: currentUserId
      ? { where: { userId: currentUserId }, select: { userId: true } }
      : { take: 0, select: { userId: true } },
    bookmarks: currentUserId
      ? { where: { userId: currentUserId }, select: { userId: true } }
      : { take: 0, select: { userId: true } },
  } satisfies Prisma.PostInclude;
}

type PostWithRelations = Prisma.PostGetPayload<{
  include: ReturnType<typeof postInclude>;
}>;

export function serializePost(post: PostWithRelations): SerializedPost {
  return {
    id: post.id,
    content: post.content,
    mediaUrls: post.mediaUrls,
    createdAt: post.createdAt.toISOString(),
    parentId: post.parentId,
    author: post.author,
    counts: {
      likes: post._count.likes,
      reposts: post._count.reposts,
      replies: post._count.replies,
    },
    viewer: {
      liked: post.likes.length > 0,
      reposted: post.reposts.length > 0,
      bookmarked: post.bookmarks.length > 0,
    },
  };
}

export async function syncHashtags(postId: string, content: string) {
  const names = extractHashtags(content);
  await prisma.postHashtag.deleteMany({ where: { postId } });
  if (names.length === 0) return;

  await Promise.all(
    names.map((name) =>
      prisma.hashtag.upsert({
        where: { name },
        update: {},
        create: { name },
      }),
    ),
  );

  const hashtags = await prisma.hashtag.findMany({
    where: { name: { in: names } },
    select: { id: true },
  });

  await prisma.postHashtag.createMany({
    data: hashtags.map((hashtag) => ({ postId, hashtagId: hashtag.id })),
    skipDuplicates: true,
  });
}

export async function getFeedPage(options: {
  currentUserId?: string | null;
  tab: "for-you" | "following";
  cursor?: string;
  limit?: number;
}) {
  const { currentUserId, tab, cursor, limit = 20 } = options;

  let authorFilter: Prisma.PostWhereInput = { parentId: null };

  if (tab === "following") {
    if (!currentUserId) {
      return { items: [], nextCursor: null };
    }
    const follows = await prisma.follow.findMany({
      where: { followerId: currentUserId },
      select: { followingId: true },
    });
    const followingIds = follows.map((follow) => follow.followingId);
    authorFilter = {
      parentId: null,
      authorId: { in: followingIds },
    };
  }

  const posts = await prisma.post.findMany({
    where: authorFilter,
    take: limit + 1,
    ...(cursor ? { cursor: { id: cursor }, skip: 1 } : {}),
    orderBy: { createdAt: "desc" },
    include: postInclude(currentUserId),
  });

  const hasMore = posts.length > limit;
  const page = hasMore ? posts.slice(0, limit) : posts;

  return {
    items: page.map(serializePost),
    nextCursor: hasMore ? page[page.length - 1]?.id ?? null : null,
  };
}

export async function getPostById(postId: string, currentUserId?: string | null) {
  const post = await prisma.post.findUnique({
    where: { id: postId },
    include: postInclude(currentUserId),
  });
  return post ? serializePost(post) : null;
}

export async function getReplies(
  postId: string,
  currentUserId?: string | null,
  cursor?: string,
  limit = 20,
) {
  const posts = await prisma.post.findMany({
    where: { parentId: postId },
    take: limit + 1,
    ...(cursor ? { cursor: { id: cursor }, skip: 1 } : {}),
    orderBy: { createdAt: "asc" },
    include: postInclude(currentUserId),
  });

  const hasMore = posts.length > limit;
  const page = hasMore ? posts.slice(0, limit) : posts;

  return {
    items: page.map(serializePost),
    nextCursor: hasMore ? page[page.length - 1]?.id ?? null : null,
  };
}

export async function getUserPosts(options: {
  userId: string;
  currentUserId?: string | null;
  filter: "posts" | "replies" | "media" | "likes";
  cursor?: string;
  limit?: number;
}) {
  const { userId, currentUserId, filter, cursor, limit = 20 } = options;

  if (filter === "likes") {
    const likes = await prisma.like.findMany({
      where: { userId },
      take: limit + 1,
      ...(cursor ? { cursor: { userId_postId: { userId, postId: cursor } }, skip: 1 } : {}),
      orderBy: { createdAt: "desc" },
      include: {
        post: { include: postInclude(currentUserId) },
      },
    });
    const hasMore = likes.length > limit;
    const page = hasMore ? likes.slice(0, limit) : likes;
    return {
      items: page.map((like) => serializePost(like.post)),
      nextCursor: hasMore ? page[page.length - 1]?.postId ?? null : null,
    };
  }

  const where: Prisma.PostWhereInput =
    filter === "replies"
      ? { authorId: userId, parentId: { not: null } }
      : filter === "media"
        ? { authorId: userId, parentId: null, mediaUrls: { isEmpty: false } }
        : { authorId: userId, parentId: null };

  const posts = await prisma.post.findMany({
    where,
    take: limit + 1,
    ...(cursor ? { cursor: { id: cursor }, skip: 1 } : {}),
    orderBy: { createdAt: "desc" },
    include: postInclude(currentUserId),
  });

  const hasMore = posts.length > limit;
  const page = hasMore ? posts.slice(0, limit) : posts;

  return {
    items: page.map(serializePost),
    nextCursor: hasMore ? page[page.length - 1]?.id ?? null : null,
  };
}
