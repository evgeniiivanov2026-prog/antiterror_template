import { NextResponse } from "next/server";
import { auth } from "@/auth";
import { feedQuerySchema } from "@/lib/validations";
import { getFeedPage } from "@/lib/posts";

export async function GET(request: Request) {
  const session = await auth();
  const { searchParams } = new URL(request.url);
  const parsed = feedQuerySchema.safeParse({
    tab: searchParams.get("tab") ?? "for-you",
    cursor: searchParams.get("cursor") ?? undefined,
    limit: searchParams.get("limit") ?? undefined,
  });

  if (!parsed.success) {
    return NextResponse.json({ error: "Некорректные параметры" }, { status: 400 });
  }

  const page = await getFeedPage({
    currentUserId: session?.user.id,
    tab: parsed.data.tab,
    cursor: parsed.data.cursor,
    limit: parsed.data.limit,
  });

  return NextResponse.json(page);
}
