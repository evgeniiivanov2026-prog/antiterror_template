import { NextResponse } from "next/server";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { searchQuerySchema } from "@/lib/validations";
import { postInclude, serializePost } from "@/lib/posts";

export async function GET(request: Request) {
  const session = await auth();
  const { searchParams } = new URL(request.url);
  const parsed = searchQuerySchema.safeParse({
    q: searchParams.get("q") ?? "",
    cursor: searchParams.get("cursor") ?? undefined,
    limit: searchParams.get("limit") ?? undefined,
  });

  if (!parsed.success) {
    return NextResponse.json({ error: "Введите запрос" }, { status: 400 });
  }

  const { q, cursor, limit } = parsed.data;
  const query = q.replace(/^#/, "");

  const [users, posts] = await Promise.all([
    prisma.user.findMany({
      where: {
        OR: [
          { username: { contains: query, mode: "insensitive" } },
          { displayName: { contains: query, mode: "insensitive" } },
        ],
      },
      take: 8,
      select: {
        id: true,
        username: true,
        displayName: true,
        avatarUrl: true,
        bio: true,
      },
    }),
    prisma.post.findMany({
      where: {
        OR: [
          { content: { contains: query, mode: "insensitive" } },
          { hashtags: { some: { hashtag: { name: { equals: query.toLowerCase() } } } } },
        ],
      },
      take: limit + 1,
      ...(cursor ? { cursor: { id: cursor }, skip: 1 } : {}),
      orderBy: { createdAt: "desc" },
      include: postInclude(session?.user.id),
    }),
  ]);

  const hasMore = posts.length > limit;
  const page = hasMore ? posts.slice(0, limit) : posts;

  return NextResponse.json({
    users,
    items: page.map(serializePost),
    nextCursor: hasMore ? page[page.length - 1]?.id ?? null : null,
  });
}
