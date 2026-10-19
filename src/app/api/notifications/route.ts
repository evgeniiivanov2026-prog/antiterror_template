import { apiError, json, parseSearch, requireApiUser } from "@/lib/api";
import { getNotifications } from "@/lib/notifications";
import { cursorQuerySchema } from "@/lib/validators";

export async function GET(request: Request) {
  const authResult = await requireApiUser();
  if (!authResult.ok) return authResult.response;
  const parsed = parseSearch(cursorQuerySchema, new URL(request.url).searchParams);
  if (!parsed.ok) return parsed.response;
  try {
    const page = await getNotifications(authResult.userId, parsed.data.cursor, parsed.data.limit);
    return json(page);
  } catch (error) {
    return apiError(error);
  }
}
