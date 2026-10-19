"use server";

import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/session";
import { postIdSchema } from "@/lib/validations";
import { createNotification, removeNotification } from "@/lib/notifications";

async function getOwnedPost(postId: string) {
  return prisma.post.findUnique({
    where: { id: postId },
    select: { id: true, authorId: true },
  });
}

export async function toggleLikeAction(input: unknown) {
  const user = await requireUser();
  const parsed = postIdSchema.safeParse(input);
  if (!parsed.success) {
    return { ok: false as const, error: "Некорректный пост" };
  }

  const post = await getOwnedPost(parsed.data.postId);
  if (!post) return { ok: false as const, error: "Пост не найден" };

  const existing = await prisma.like.findUnique({
    where: { userId_postId: { userId: user.id, postId: post.id } },
  });

  if (existing) {
    await prisma.like.delete({
      where: { userId_postId: { userId: user.id, postId: post.id } },
    });
    await removeNotification({
      recipientId: post.authorId,
      issuerId: user.id,
      type: "LIKE",
      postId: post.id,
    });
    return { ok: true as const, active: false };
  }

  await prisma.like.create({
    data: { userId: user.id, postId: post.id },
  });
  await createNotification({
    recipientId: post.authorId,
    issuerId: user.id,
    type: "LIKE",
    postId: post.id,
  });
  return { ok: true as const, active: true };
}

export async function toggleRepostAction(input: unknown) {
  const user = await requireUser();
  const parsed = postIdSchema.safeParse(input);
  if (!parsed.success) {
    return { ok: false as const, error: "Некорректный пост" };
  }

  const post = await getOwnedPost(parsed.data.postId);
  if (!post) return { ok: false as const, error: "Пост не найден" };

  const existing = await prisma.repost.findUnique({
    where: { userId_postId: { userId: user.id, postId: post.id } },
  });

  if (existing) {
    await prisma.repost.delete({
      where: { userId_postId: { userId: user.id, postId: post.id } },
    });
    await removeNotification({
      recipientId: post.authorId,
      issuerId: user.id,
      type: "REPOST",
      postId: post.id,
    });
    return { ok: true as const, active: false };
  }

  await prisma.repost.create({
    data: { userId: user.id, postId: post.id },
  });
  await createNotification({
    recipientId: post.authorId,
    issuerId: user.id,
    type: "REPOST",
    postId: post.id,
  });
  return { ok: true as const, active: true };
}

export async function toggleBookmarkAction(input: unknown) {
  const user = await requireUser();
  const parsed = postIdSchema.safeParse(input);
  if (!parsed.success) {
    return { ok: false as const, error: "Некорректный пост" };
  }

  const post = await getOwnedPost(parsed.data.postId);
  if (!post) return { ok: false as const, error: "Пост не найден" };

  const existing = await prisma.bookmark.findUnique({
    where: { userId_postId: { userId: user.id, postId: post.id } },
  });

  if (existing) {
    await prisma.bookmark.delete({
      where: { userId_postId: { userId: user.id, postId: post.id } },
    });
    return { ok: true as const, active: false };
  }

  await prisma.bookmark.create({
    data: { userId: user.id, postId: post.id },
  });
  return { ok: true as const, active: true };
}
