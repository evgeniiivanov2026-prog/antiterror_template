"use server";

import { revalidatePath } from "next/cache";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/session";
import { userIdSchema } from "@/lib/validations";
import { createNotification, removeNotification } from "@/lib/notifications";

export async function toggleFollowAction(input: unknown) {
  const user = await requireUser();
  const parsed = userIdSchema.safeParse(input);
  if (!parsed.success) {
    return { ok: false as const, error: "Некорректный пользователь" };
  }

  const targetId = parsed.data.userId;
  if (targetId === user.id) {
    return { ok: false as const, error: "Нельзя подписаться на себя" };
  }

  const target = await prisma.user.findUnique({
    where: { id: targetId },
    select: { id: true, username: true },
  });

  if (!target) {
    return { ok: false as const, error: "Пользователь не найден" };
  }

  const existing = await prisma.follow.findUnique({
    where: {
      followerId_followingId: {
        followerId: user.id,
        followingId: targetId,
      },
    },
  });

  if (existing) {
    await prisma.follow.delete({
      where: {
        followerId_followingId: {
          followerId: user.id,
          followingId: targetId,
        },
      },
    });
    await removeNotification({
      recipientId: targetId,
      issuerId: user.id,
      type: "FOLLOW",
    });
    revalidatePath(`/${target.username}`);
    return { ok: true as const, active: false };
  }

  await prisma.follow.create({
    data: { followerId: user.id, followingId: targetId },
  });
  await createNotification({
    recipientId: targetId,
    issuerId: user.id,
    type: "FOLLOW",
  });
  revalidatePath(`/${target.username}`);
  return { ok: true as const, active: true };
}
