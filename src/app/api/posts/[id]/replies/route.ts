import { NextResponse } from "next/server";
import { auth } from "@/auth";
import { cursorQuerySchema } from "@/lib/validations";
import { getReplies } from "@/lib/posts";

export async function GET(
  request: Request,
  context: { params: Promise<{ id: string }> },
) {
  const session = await auth();
  const { id } = await context.params;
  const { searchParams } = new URL(request.url);
  const parsed = cursorQuerySchema.safeParse({
    cursor: searchParams.get("cursor") ?? undefined,
    limit: searchParams.get("limit") ?? undefined,
  });

  if (!parsed.success) {
    return NextResponse.json({ error: "Некорректные параметры" }, { status: 400 });
  }

  const page = await getReplies(
    id,
    session?.user.id,
    parsed.data.cursor,
    parsed.data.limit,
  );

  return NextResponse.json(page);
}
