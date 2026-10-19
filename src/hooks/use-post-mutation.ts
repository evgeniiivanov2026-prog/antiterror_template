"use client";

import { useMutation, useQueryClient } from "@tanstack/react-query";
import type { InfiniteData } from "@tanstack/react-query";
import type { FeedPage, SerializedPost } from "@/types";

type ActionKey = "liked" | "reposted" | "bookmarked";
type CountKey = "likes" | "reposts";

function patchPost(post: SerializedPost, action: ActionKey, next: boolean) {
  const countKey: CountKey | null =
    action === "liked" ? "likes" : action === "reposted" ? "reposts" : null;
  return {
    ...post,
    viewer: { ...post.viewer, [action]: next },
    counts: countKey
      ? {
          ...post.counts,
          [countKey]: Math.max(0, post.counts[countKey] + (next ? 1 : -1)),
        }
      : post.counts,
  };
}

function mapFeedPages(
  data: InfiniteData<FeedPage> | undefined,
  postId: string,
  updater: (post: SerializedPost) => SerializedPost,
) {
  if (!data) return data;
  return {
    ...data,
    pages: data.pages.map((page) => ({
      ...page,
      items: page.items.map((post) => (post.id === postId ? updater(post) : post)),
    })),
  };
}

export function usePostMutation(
  action: ActionKey,
  mutateFn: (postId: string) => Promise<{ ok: boolean; active?: boolean; error?: string }>,
) {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (postId: string) => {
      const result = await mutateFn(postId);
      if (!result.ok) {
        throw new Error(result.error ?? "Ошибка");
      }
      return result;
    },
    onMutate: async (postId) => {
      await queryClient.cancelQueries();
      const snapshots = queryClient.getQueriesData<InfiniteData<FeedPage>>({
        predicate: (query) => Array.isArray(query.queryKey),
      });

      queryClient.setQueriesData<InfiniteData<FeedPage> | SerializedPost>(
        { predicate: (query) => Array.isArray(query.queryKey) },
        (current) => {
          if (!current) return current;
          if ("pages" in current) {
            const first = current.pages[0]?.items.find((item) => item.id === postId);
            const next = first ? !first.viewer[action] : true;
            return mapFeedPages(current, postId, (post) => patchPost(post, action, next));
          }
          if ("id" in current && current.id === postId) {
            return patchPost(current, action, !current.viewer[action]);
          }
          return current;
        },
      );

      return { snapshots };
    },
    onError: (_error, _postId, context) => {
      context?.snapshots.forEach(([key, data]) => {
        queryClient.setQueryData(key, data);
      });
    },
  });
}
