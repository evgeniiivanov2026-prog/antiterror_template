import { NextResponse } from "next/server";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { cursorQuerySchema } from "@/lib/validations";
import { postInclude, serializePost } from "@/lib/posts";

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
  const bookmarks = await prisma.bookmark.findMany({
    where: { userId: session.user.id },
    take: limit + 1,
    ...(cursor
      ? {
          cursor: {
            userId_postId: { userId: session.user.id, postId: cursor },
          },
          skip: 1,
        }
      : {}),
    orderBy: { createdAt: "desc" },
    include: {
      post: { include: postInclude(session.user.id) },
    },
  });

  const hasMore = bookmarks.length > limit;
  const page = hasMore ? bookmarks.slice(0, limit) : bookmarks;

  return NextResponse.json({
    items: page.map((bookmark) => serializePost(bookmark.post)),
    nextCursor: hasMore ? page[page.length - 1]?.postId ?? null : null,
  });
}
