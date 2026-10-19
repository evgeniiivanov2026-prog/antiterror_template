import Link from "next/link";
import { formatCompactNumber } from "@/lib/utils";
import type { TrendingTopic } from "@/types";

export function Trending({ topics }: { topics: TrendingTopic[] }) {
  return (
    <section className="overflow-hidden rounded-2xl bg-zinc-950">
      <h2 className="px-4 py-3 text-xl font-extrabold">Актуальные темы</h2>
      {topics.length === 0 ? (
        <p className="px-4 pb-4 text-sm text-muted">Пока нет трендов.</p>
      ) : (
        topics.map((topic, index) => (
          <Link
            key={topic.name}
            href={`/explore?q=${encodeURIComponent(`#${topic.name}`)}`}
            className="block px-4 py-3 hover:bg-white/5"
          >
            <p className="text-xs text-muted">{index + 1} · Популярно</p>
            <p className="font-bold">#{topic.name}</p>
            <p className="text-xs text-muted">
              {formatCompactNumber(topic.posts)} постов
            </p>
          </Link>
        ))
      )}
    </section>
  );
}
