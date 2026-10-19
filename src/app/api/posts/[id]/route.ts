import { NextResponse } from "next/server";
import { auth } from "@/auth";
import { getPostById } from "@/lib/posts";

export async function GET(
  _request: Request,
  context: { params: Promise<{ id: string }> },
) {
  const session = await auth();
  const { id } = await context.params;
  const post = await getPostById(id, session?.user.id);

  if (!post) {
    return NextResponse.json({ error: "Пост не найден" }, { status: 404 });
  }

  return NextResponse.json(post);
}
