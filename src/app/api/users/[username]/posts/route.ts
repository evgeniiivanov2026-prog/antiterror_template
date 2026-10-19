import { NextResponse } from "next/server";
import { apiError, json, parseSearch, requireApiUser } from "@/lib/api";
import { getLikedPosts, getProfilePosts } from "@/lib/posts";
import { prisma } from "@/lib/prisma";
import { profilePostsQuerySchema } from "@/lib/validators";

export async function GET(request: Request, context: { params: Promise<{ username: string }> }) {
  const authResult = await requireApiUser();
  if (!authResult.ok) return authResult.response;
  const { username } = await context.params;
  const parsed = parseSearch(profilePostsQuerySchema, new URL(request.url).searchParams);
  if (!parsed.ok) return parsed.response;

  const profile = await prisma.user.findUnique({
    where: { username: username.toLowerCase() },
    select: { id: true },
  });
  if (!profile) return NextResponse.json({ error: "Пользователь не найден" }, { status: 404 });

  try {
    const page =
      parsed.data.tab === "likes"
        ? await getLikedPosts(profile.id, authResult.userId, parsed.data.cursor, parsed.data.limit)
        : await getProfilePosts(
            profile.id,
            authResult.userId,
            parsed.data.tab,
            parsed.data.cursor,
            parsed.data.limit,
          );
    return json(page);
  } catch (error) {
    return apiError(error);
  }
}
