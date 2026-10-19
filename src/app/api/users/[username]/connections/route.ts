import { NextResponse } from "next/server";
import { apiError, json, parseSearch, requireApiUser } from "@/lib/api";
import { getConnections } from "@/lib/users";
import { connectionQuerySchema } from "@/lib/validators";

export async function GET(request: Request, context: { params: Promise<{ username: string }> }) {
  const authResult = await requireApiUser();
  if (!authResult.ok) return authResult.response;
  const { username } = await context.params;
  const parsed = parseSearch(connectionQuerySchema, new URL(request.url).searchParams);
  if (!parsed.ok) return parsed.response;
  try {
    const result = await getConnections(
      username,
      authResult.userId,
      parsed.data.kind,
      parsed.data.cursor,
      parsed.data.limit,
    );
    if (!result) return NextResponse.json({ error: "Пользователь не найден" }, { status: 404 });
    return json(result.users);
  } catch (error) {
    return apiError(error);
  }
}
