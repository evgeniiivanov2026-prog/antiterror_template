import Link from "next/link";
import { SearchBox } from "@/components/layout/search-box";
import { UserRow } from "@/components/user/user-row";
import { formatCount } from "@/lib/utils";
import type { Trend, UserCard } from "@/types/social";

export function RightPanel({ trends, suggestions }: { trends: Trend[]; suggestions: UserCard[] }) {
  return (
    <aside className="sticky top-0 hidden h-screen w-[350px] shrink-0 overflow-y-auto px-6 py-2 xl:block">
      <SearchBox />
      <section className="mt-4 overflow-hidden rounded-2xl border border-border">
        <h2 className="px-4 py-3 text-xl font-extrabold">Актуальные темы</h2>
        {trends.length === 0 ? (
          <p className="px-4 pb-4 text-sm text-muted">Пока нет активных тем</p>
        ) : (
          trends.map((trend) => (
            <Link
              key={trend.tag}
              href={`/search?q=${encodeURIComponent(trend.tag)}`}
              className="block px-4 py-3 hover:bg-white/[0.03]"
            >
              <p className="text-[13px] text-muted">Тема</p>
              <p className="font-bold">{trend.tag}</p>
              <p className="text-[13px] text-muted">{formatCount(trend.count)} постов</p>
            </Link>
          ))
        )}
      </section>
      <section className="mt-4 overflow-hidden rounded-2xl border border-border">
        <h2 className="px-4 py-3 text-xl font-extrabold">Кого читать</h2>
        {suggestions.length === 0 ? (
          <p className="px-4 pb-4 text-sm text-muted">Вы уже читаете всех, кого мы можем предложить</p>
        ) : (
          suggestions.map((user) => <UserRow key={user.id} user={user} />)
        )}
      </section>
    </aside>
  );
}
