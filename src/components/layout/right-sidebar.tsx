import { SearchBox } from "@/components/search/search-box";
import { Trending } from "@/components/search/trending";
import { WhoToFollow } from "@/components/user/who-to-follow";
import type { SuggestionUser, TrendingTopic } from "@/types";

export function RightSidebar({
  topics,
  suggestions,
}: {
  topics: TrendingTopic[];
  suggestions: SuggestionUser[];
}) {
  return (
    <aside className="sticky top-0 hidden h-screen w-[350px] shrink-0 flex-col gap-4 py-3 pl-6 lg:flex">
      <SearchBox />
      <Trending topics={topics} />
      <WhoToFollow users={suggestions} />
    </aside>
  );
}
