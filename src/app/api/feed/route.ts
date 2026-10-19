import { apiError, json, parseSearch, requireApiUser } from "@/lib/api";
import { getFeed } from "@/lib/feed";
import { feedQuerySchema } from "@/lib/validators";

export async function GET(request: Request) {
  const authResult = await requireApiUser();
  if (!authResult.ok) return authResult.response;
  const parsed = parseSearch(feedQuerySchema, new URL(request.url).searchParams);
  if (!parsed.ok) return parsed.response;
  try {
    const page = await getFeed({
      userId: authResult.userId,
      tab: parsed.data.tab,
      cursor: parsed.data.cursor,
      limit: parsed.data.limit,
    });
    return json(page);
  } catch (error) {
    return apiError(error);
  }
}
