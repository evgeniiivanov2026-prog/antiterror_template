import "server-only";
import { encodeCursor, olderThan, requireCursor } from "@/lib/cursor";
import { prisma } from "@/lib/prisma";
import { authorSelect, toNotification } from "@/lib/serializers";
import type { CursorPage, NotificationView } from "@/types/social";

export async function getNotifications(
  userId: string,
  cursorRaw?: string,
  limit = 20,
): Promise<CursorPage<NotificationView>> {
  const cursor = requireCursor(cursorRaw);
  const rows = await prisma.notification.findMany({
    where: { recipientId: userId, ...olderThan(cursor) },
    orderBy: [{ createdAt: "desc" }, { id: "desc" }],
    take: limit + 1,
    include: {
      issuer: { select: authorSelect },
      post: {
        select: {
          id: true,
          content: true,
          author: { select: { username: true } },
        },
      },
    },
  });

  const hasMore = rows.length > limit;
  const page = hasMore ? rows.slice(0, limit) : rows;
  const last = page[page.length - 1];
  return {
    items: page.map(toNotification),
    nextCursor: hasMore && last ? encodeCursor(last.createdAt, last.id) : null,
  };
}

export async function getUnreadCount(userId: string) {
  return prisma.notification.count({
    where: { recipientId: userId, read: false },
  });
}

export async function markNotificationsRead(userId: string) {
  const result = await prisma.notification.updateMany({
    where: { recipientId: userId, read: false },
    data: { read: true },
  });
  return result.count;
}
