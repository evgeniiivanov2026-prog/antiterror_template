import { NextResponse } from "next/server";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { cursorQuerySchema } from "@/lib/validations";
import { postAuthorSelect } from "@/lib/posts";

export async function GET(request: Request) {
  const session = await auth();
  if (!session?.user.id) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  const { searchParams } = new URL(request.url);
  const parsed = cursorQuerySchema.safeParse({
    cursor: searchParams.get("cursor") ?? undefined,
    limit: searchParams.get("limit") ?? undefined,
  });

  if (!parsed.success) {
    return NextResponse.json({ error: "Некорректные параметры" }, { status: 400 });
  }

  const { cursor, limit } = parsed.data;

  const [items, unreadCount] = await Promise.all([
    prisma.notification.findMany({
      where: { recipientId: session.user.id },
      take: limit + 1,
      ...(cursor ? { cursor: { id: cursor }, skip: 1 } : {}),
      orderBy: { createdAt: "desc" },
      include: {
        issuer: { select: postAuthorSelect },
        post: { select: { id: true, content: true } },
      },
    }),
    prisma.notification.count({
      where: { recipientId: session.user.id, read: false },
    }),
  ]);

  const hasMore = items.length > limit;
  const page = hasMore ? items.slice(0, limit) : items;

  return NextResponse.json({
    unreadCount,
    items: page.map((item) => ({
      id: item.id,
      type: item.type,
      read: item.read,
      createdAt: item.createdAt.toISOString(),
      issuer: item.issuer,
      post: item.post,
    })),
    nextCursor: hasMore ? page[page.length - 1]?.id ?? null : null,
  });
}
