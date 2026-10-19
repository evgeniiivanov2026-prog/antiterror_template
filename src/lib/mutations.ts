import "server-only";
import { NotificationType } from "@/generated/prisma/client";
import { prisma } from "@/lib/prisma";
import { postInclude, toAuthor, toPostView, type PostRecord } from "@/lib/serializers";
import type { ActionResult, Me, PostView } from "@/types/social";

function uniqueViolation(error: unknown) {
  return (
    typeof error === "object" &&
    error !== null &&
    "code" in error &&
    (error as { code?: string }).code === "P2002"
  );
}

async function notify(input: {
  recipientId: string;
  issuerId: string;
  type: (typeof NotificationType)[keyof typeof NotificationType];
  postId?: string | null;
}) {
  if (input.recipientId === input.issuerId) return;
  await prisma.notification.create({
    data: {
      recipientId: input.recipientId,
      issuerId: input.issuerId,
      type: input.type,
      postId: input.postId ?? null,
    },
  });
}

export async function publishPost(
  userId: string,
  input: { content: string; mediaUrls: string[]; parentId: string | null },
): Promise<ActionResult<PostView>> {
  let parentAuthorId: string | null = null;
  if (input.parentId) {
    const parent = await prisma.post.findUnique({
      where: { id: input.parentId },
      select: { id: true, authorId: true },
    });
    if (!parent) return { ok: false, error: "Исходный пост не найден" };
    parentAuthorId = parent.authorId;
  }

  try {
    const post = await prisma.post.create({
      data: {
        content: input.content,
        mediaUrls: input.mediaUrls,
        authorId: userId,
        parentId: input.parentId,
      },
      include: postInclude(userId),
    });
    if (input.parentId && parentAuthorId) {
      await notify({
        recipientId: parentAuthorId,
        issuerId: userId,
        type: NotificationType.REPLY,
        postId: post.id,
      });
    }
    return { ok: true, data: toPostView(post as PostRecord) };
  } catch {
    return { ok: false, error: "Не удалось опубликовать пост" };
  }
}

export async function removePost(userId: string, postId: string): Promise<ActionResult<{ id: string }>> {
  const post = await prisma.post.findUnique({
    where: { id: postId },
    select: { authorId: true },
  });
  if (!post) return { ok: false, error: "Пост не найден" };
  if (post.authorId !== userId) return { ok: false, error: "Можно удалять только свои посты" };
  await prisma.post.delete({ where: { id: postId } });
  return { ok: true, data: { id: postId } };
}

async function toggleInteraction(
  userId: string,
  postId: string,
  kind: "like" | "repost" | "bookmark",
): Promise<ActionResult<{ active: boolean; count: number }>> {
  const post = await prisma.post.findUnique({
    where: { id: postId },
    select: { id: true, authorId: true },
  });
  if (!post) return { ok: false, error: "Пост не найден" };

  const data = await prisma.$transaction(async (tx) => {
    if (kind === "like") {
      const existing = await tx.like.findUnique({
        where: { userId_postId: { userId, postId } },
      });
      if (existing) {
        await tx.like.delete({ where: { userId_postId: { userId, postId } } });
        await tx.notification.deleteMany({
          where: { recipientId: post.authorId, issuerId: userId, type: NotificationType.LIKE, postId },
        });
      } else {
        await tx.like.create({ data: { userId, postId } });
        if (post.authorId !== userId) {
          await tx.notification.create({
            data: {
              recipientId: post.authorId,
              issuerId: userId,
              type: NotificationType.LIKE,
              postId,
            },
          });
        }
      }
      const count = await tx.like.count({ where: { postId } });
      return { active: !existing, count };
    }

    if (kind === "repost") {
      const existing = await tx.repost.findUnique({
        where: { userId_postId: { userId, postId } },
      });
      if (existing) {
        await tx.repost.delete({ where: { userId_postId: { userId, postId } } });
        await tx.notification.deleteMany({
          where: { recipientId: post.authorId, issuerId: userId, type: NotificationType.REPOST, postId },
        });
      } else {
        await tx.repost.create({ data: { userId, postId } });
        if (post.authorId !== userId) {
          await tx.notification.create({
            data: {
              recipientId: post.authorId,
              issuerId: userId,
              type: NotificationType.REPOST,
              postId,
            },
          });
        }
      }
      const count = await tx.repost.count({ where: { postId } });
      return { active: !existing, count };
    }

    const existing = await tx.bookmark.findUnique({
      where: { userId_postId: { userId, postId } },
    });
    if (existing) {
      await tx.bookmark.delete({ where: { userId_postId: { userId, postId } } });
    } else {
      await tx.bookmark.create({ data: { userId, postId } });
    }
    const count = await tx.bookmark.count({ where: { postId } });
    return { active: !existing, count };
  });

  return { ok: true, data };
}

export function toggleLike(userId: string, postId: string) {
  return toggleInteraction(userId, postId, "like");
}

export function toggleRepost(userId: string, postId: string) {
  return toggleInteraction(userId, postId, "repost");
}

export function toggleBookmark(userId: string, postId: string) {
  return toggleInteraction(userId, postId, "bookmark");
}

export async function toggleFollow(
  userId: string,
  targetId: string,
): Promise<ActionResult<{ following: boolean; followers: number }>> {
  if (userId === targetId) return { ok: false, error: "Нельзя подписаться на себя" };
  const target = await prisma.user.findUnique({ where: { id: targetId }, select: { id: true } });
  if (!target) return { ok: false, error: "Пользователь не найден" };

  const existing = await prisma.follow.findUnique({
    where: { followerId_followingId: { followerId: userId, followingId: targetId } },
  });

  if (existing) {
    await prisma.$transaction([
      prisma.follow.delete({
        where: { followerId_followingId: { followerId: userId, followingId: targetId } },
      }),
      prisma.notification.deleteMany({
        where: { recipientId: targetId, issuerId: userId, type: NotificationType.FOLLOW },
      }),
    ]);
  } else {
    await prisma.$transaction([
      prisma.follow.create({ data: { followerId: userId, followingId: targetId } }),
      prisma.notification.create({
        data: { recipientId: targetId, issuerId: userId, type: NotificationType.FOLLOW },
      }),
    ]);
  }

  const followers = await prisma.follow.count({ where: { followingId: targetId } });
  return { ok: true, data: { following: !existing, followers } };
}

export async function saveProfile(
  userId: string,
  input: {
    displayName: string;
    username: string;
    bio: string;
    avatarUrl: string;
    coverUrl: string;
  },
): Promise<ActionResult<Me>> {
  const current = await prisma.user.findUnique({
    where: { id: userId },
    select: { username: true },
  });
  if (!current) return { ok: false, error: "Пользователь не найден" };

  if (input.username !== current.username) {
    const taken = await prisma.user.findUnique({
      where: { username: input.username },
      select: { id: true },
    });
    if (taken) return { ok: false, error: "Это имя пользователя уже занято" };
  }

  try {
    const user = await prisma.user.update({
      where: { id: userId },
      data: {
        displayName: input.displayName,
        username: input.username,
        bio: input.bio,
        avatarUrl: input.avatarUrl || null,
        coverUrl: input.coverUrl || null,
      },
      select: {
        id: true,
        username: true,
        displayName: true,
        avatarUrl: true,
        bio: true,
        createdAt: true,
        _count: { select: { posts: true, followers: true, following: true } },
      },
    });
    return {
      ok: true,
      data: {
        ...toAuthor(user),
        createdAt: user.createdAt.toISOString(),
        counts: user._count,
      },
    };
  } catch (error) {
    if (uniqueViolation(error)) return { ok: false, error: "Это имя пользователя уже занято" };
    return { ok: false, error: "Не удалось сохранить профиль" };
  }
}
