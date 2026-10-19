"use server";

import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/session";

export async function markNotificationsReadAction() {
  const user = await requireUser();
  await prisma.notification.updateMany({
    where: { recipientId: user.id, read: false },
    data: { read: true },
  });
  return { ok: true as const };
}
