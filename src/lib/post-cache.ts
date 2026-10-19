import type { InfiniteData, QueryClient, QueryKey } from "@tanstack/react-query";
import type { CursorPage, FeedItem, FeedPage, PostView } from "@/types/social";

const ROOTS = new Set(["feed", "replies", "profile", "bookmarks", "search", "thread"]);

function isPost(value: unknown): value is PostView {
  if (!value || typeof value !== "object") return false;
  const record = value as Record<string, unknown>;
  return (
    typeof record.id === "string" &&
    typeof record.content === "string" &&
    typeof record.createdAt === "string" &&
    typeof record.counts === "object" &&
    record.counts !== null &&
    typeof record.viewer === "object" &&
    record.viewer !== null &&
    typeof record.author === "object" &&
    record.author !== null
  );
}

function isFeedItem(value: unknown): value is FeedItem {
  if (!value || typeof value !== "object") return false;
  const record = value as Record<string, unknown>;
  return (record.kind === "post" || record.kind === "repost") && isPost(record.post);
}

export function snapshotPostCaches(queryClient: QueryClient) {
  return queryClient.getQueriesData({
    predicate: (query) => ROOTS.has(String(query.queryKey[0])),
  });
}

export function restorePostCaches(
  queryClient: QueryClient,
  snapshot: Array<[QueryKey, unknown]>,
) {
  for (const [key, data] of snapshot) {
    queryClient.setQueryData(key, data);
  }
}

export function patchCachedPost(
  queryClient: QueryClient,
  postId: string,
  updater: (post: PostView) => PostView,
) {
  queryClient.setQueriesData(
    { predicate: (query) => ROOTS.has(String(query.queryKey[0])) },
    (data) => rewrite(data, postId, updater),
  );
}

function rewrite(data: unknown, postId: string, updater: (post: PostView) => PostView): unknown {
  if (isPost(data)) return data.id === postId ? updater(data) : data;
  if (Array.isArray(data)) {
    let changed = false;
    const next = data.map((item) => {
      const updated = rewrite(item, postId, updater);
      if (updated !== item) changed = true;
      return updated;
    });
    return changed ? next : data;
  }
  if (!data || typeof data !== "object") return data;
  let changed = false;
  const next: Record<string, unknown> = {};
  for (const [key, value] of Object.entries(data)) {
    const updated = rewrite(value, postId, updater);
    if (updated !== value) changed = true;
    next[key] = updated;
  }
  return changed ? next : data;
}

export function removeCachedPost(queryClient: QueryClient, postId: string) {
  queryClient.setQueriesData(
    { predicate: (query) => ROOTS.has(String(query.queryKey[0])) },
    (data) => strip(data, postId),
  );
}

function strip(data: unknown, postId: string): unknown {
  if (Array.isArray(data)) {
    return data
      .filter((item) => {
        if (isPost(item)) return item.id !== postId;
        if (isFeedItem(item)) return item.post.id !== postId;
        return true;
      })
      .map((item) => strip(item, postId));
  }
  if (!data || typeof data !== "object" || isPost(data)) return data;
  let changed = false;
  const next: Record<string, unknown> = {};
  for (const [key, value] of Object.entries(data)) {
    const updated = strip(value, postId);
    if (updated !== value) changed = true;
    next[key] = updated;
  }
  return changed ? next : data;
}

export function prependFeedPost(queryClient: QueryClient, post: PostView) {
  const item: FeedItem = {
    id: `post:${post.id}`,
    sortAt: post.createdAt,
    sortId: post.id,
    kind: "post",
    repostedBy: null,
    post,
  };
  for (const tab of ["for-you", "following"] as const) {
    queryClient.setQueryData<InfiniteData<FeedPage>>(["feed", tab], (data) => {
      if (!data?.pages[0]) return data;
      if (data.pages.some((page) => page.items.some((entry) => entry.kind === "post" && entry.post.id === post.id))) {
        return data;
      }
      const [first, ...rest] = data.pages;
      return {
        ...data,
        pages: [{ ...first, items: [item, ...first.items] }, ...rest],
      };
    });
  }
}

export function appendReply(queryClient: QueryClient, parentId: string, reply: PostView) {
  queryClient.setQueryData<InfiniteData<CursorPage<PostView>>>(["replies", parentId], (data) => {
    if (!data) return data;
    if (data.pages.some((page) => page.items.some((item) => item.id === reply.id))) return data;
    if (data.pages.some((page) => page.nextCursor)) return data;
    const pages = data.pages.slice();
    const lastIndex = pages.length - 1;
    const last = pages[lastIndex];
    if (!last) return data;
    pages[lastIndex] = { ...last, items: [...last.items, reply] };
    return { ...data, pages };
  });
}
