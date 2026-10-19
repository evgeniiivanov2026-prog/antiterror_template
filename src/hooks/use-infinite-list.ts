"use client";

import { useInfiniteQuery } from "@tanstack/react-query";
import type { FeedPage } from "@/types";

export function useInfiniteList(
  queryKey: readonly unknown[],
  path: string,
  enabled = true,
) {
  return useInfiniteQuery({
    queryKey,
    enabled,
    initialPageParam: null as string | null,
    queryFn: async ({ pageParam }) => {
      const url = new URL(path, window.location.origin);
      if (pageParam) url.searchParams.set("cursor", pageParam);
      const response = await fetch(url.toString());
      if (!response.ok) {
        throw new Error("Не удалось загрузить данные");
      }
      return (await response.json()) as FeedPage;
    },
    getNextPageParam: (lastPage) => lastPage.nextCursor,
  });
}
