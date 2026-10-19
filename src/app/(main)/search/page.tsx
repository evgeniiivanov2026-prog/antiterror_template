import { SearchView } from "@/components/search/search-view";
import { searchAll } from "@/lib/search";
import { requireUserId } from "@/lib/session";

export const metadata = { title: "Поиск" };

export default async function SearchPage({ searchParams }: { searchParams: Promise<{ q?: string }> }) {
  const userId = await requireUserId();
  const query = ((await searchParams).q ?? "").trim().slice(0, 100);
  const initial = query ? await searchAll(query, userId) : null;
  return <SearchView query={query} initial={initial} viewerId={userId} />;
}
