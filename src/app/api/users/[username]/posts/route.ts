import { NextResponse } from "next/server";
import { z } from "zod";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { getUserPosts } from "@/lib/posts";

const querySchema = z.object({
  filter: z.enum(["posts", "replies", "media", "likes"]).default("posts"),
  cursor: z.string().cuid().optional(),
  limit: z.coerce.number().int().min(1).max(50).default(20),
});

export async function GET(
  request: Request,
  context: { params: Promise<{ username: string }> },
) {
  const session = await auth();
  const { username } = await context.params;
  const { searchParams } = new URL(request.url);
  const parsed = querySchema.safeParse({
    filter: searchParams.get("filter") ?? "posts",
    cursor: searchParams.get("cursor") ?? undefined,
    limit: searchParams.get("limit") ?? undefined,
  });

  if (!parsed.success) {
    return NextResponse.json({ error: "Некорректные параметры" }, { status: 400 });
  }

  const user = await prisma.user.findUnique({
    where: { username: username.toLowerCase() },
    select: { id: true },
  });

  if (!user) {
    return NextResponse.json({ error: "Пользователь не найден" }, { status: 404 });
  }

  const page = await getUserPosts({
    userId: user.id,
    currentUserId: session?.user.id,
    filter: parsed.data.filter,
    cursor: parsed.data.cursor,
    limit: parsed.data.limit,
  });

  return NextResponse.json(page);
}
