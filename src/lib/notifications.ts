import { prisma } from "@/lib/prisma";
import type { NotificationType } from "@prisma/client";

export async function createNotification(input: {
  recipientId: string;
  issuerId: string;
  type: NotificationType;
  postId?: string | null;
}) {
  if (input.recipientId === input.issuerId) return;

  const existing = await prisma.notification.findFirst({
    where: {
      recipientId: input.recipientId,
      issuerId: input.issuerId,
      type: input.type,
      postId: input.postId ?? null,
    },
  });

  if (existing) return existing;

  return prisma.notification.create({
    data: {
      recipientId: input.recipientId,
      issuerId: input.issuerId,
      type: input.type,
      postId: input.postId ?? null,
    },
  });
}

export async function removeNotification(input: {
  recipientId: string;
  issuerId: string;
  type: NotificationType;
  postId?: string | null;
}) {
  await prisma.notification.deleteMany({
    where: {
      recipientId: input.recipientId,
      issuerId: input.issuerId,
      type: input.type,
      postId: input.postId ?? null,
    },
  });
}
