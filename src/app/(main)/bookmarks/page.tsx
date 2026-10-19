import { InfinitePosts } from "@/components/feed/infinite-posts";
import { ColumnHeader } from "@/components/layout/column-header";
import { getBookmarks } from "@/lib/posts";
import { requireUserId } from "@/lib/session";

export const metadata = { title: "Закладки" };

export default async function BookmarksPage() {
  const userId = await requireUserId();
  const initial = await getBookmarks(userId);
  return (
    <>
      <ColumnHeader title="Закладки" />
      <InfinitePosts
        queryKey={["bookmarks", userId]}
        initial={initial}
        endpoint="/api/bookmarks"
        viewerId={userId}
        empty="Сохраняйте посты — они появятся здесь."
      />
    </>
  );
}
