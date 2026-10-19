import "server-only";
import { prisma } from "@/lib/prisma";
import type { Trend } from "@/types/social";

const HASHTAG = /#[\p{L}\p{N}_]+/gu;

export async function getTrends(limit = 5): Promise<Trend[]> {
  const since = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
  const posts = await prisma.post.findMany({
    where: {
      createdAt: { gte: since },
      content: { contains: "#" },
    },
    select: { content: true },
    orderBy: { createdAt: "desc" },
    take: 800,
  });

  const counts = new Map<string, number>();
  for (const post of posts) {
    const tags = post.content.match(HASHTAG) ?? [];
    const unique = new Set(tags.map((tag) => tag.toLocaleLowerCase("ru-RU")));
    for (const tag of unique) {
      counts.set(tag, (counts.get(tag) ?? 0) + 1);
    }
  }

  return [...counts.entries()]
    .sort((left, right) => right[1] - left[1] || left[0].localeCompare(right[0], "ru"))
    .slice(0, limit)
    .map(([tag, count]) => ({ tag, count }));
}
