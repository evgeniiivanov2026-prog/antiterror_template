import { NextResponse } from "next/server";
import { apiError, json, parseSearch, requireApiUser } from "@/lib/api";
import { getReplies } from "@/lib/posts";
import { cursorQuerySchema, idSchema } from "@/lib/validators";

export async function GET(request: Request, context: { params: Promise<{ id: string }> }) {
  const authResult = await requireApiUser();
  if (!authResult.ok) return authResult.response;
  const { id } = await context.params;
  const postId = idSchema.safeParse(id);
  if (!postId.success) {
    return NextResponse.json({ error: "Некорректный пост" }, { status: 400 });
  }
  const parsed = parseSearch(cursorQuerySchema, new URL(request.url).searchParams);
  if (!parsed.ok) return parsed.response;
  try {
    const page = await getReplies(postId.data, authResult.userId, parsed.data.cursor, parsed.data.limit);
    return json(page);
  } catch (error) {
    return apiError(error);
  }
}
