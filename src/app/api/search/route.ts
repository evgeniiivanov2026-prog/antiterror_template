import { apiError, json, parseSearch, requireApiUser } from "@/lib/api";
import { searchAll } from "@/lib/search";
import { searchQuerySchema } from "@/lib/validators";

export async function GET(request: Request) {
  const authResult = await requireApiUser();
  if (!authResult.ok) return authResult.response;
  const parsed = parseSearch(searchQuerySchema, new URL(request.url).searchParams);
  if (!parsed.ok) return parsed.response;
  try {
    const results = await searchAll(parsed.data.q, authResult.userId, parsed.data.cursor, parsed.data.limit);
    return json(results);
  } catch (error) {
    return apiError(error);
  }
}
