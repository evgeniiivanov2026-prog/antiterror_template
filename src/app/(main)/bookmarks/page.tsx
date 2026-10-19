import { redirect } from "next/navigation";
import { auth } from "@/auth";
import { queryKeys } from "@/lib/query-keys";
import { InfiniteFeed } from "@/components/post/infinite-feed";

export default async function BookmarksPage() {
  const session = await auth();
  if (!session?.user) redirect("/login");

  return (
    <div>
      <header className="sticky top-0 z-20 border-b border-zinc-800 bg-black/75 px-4 py-3 backdrop-blur-md">
        <h1 className="text-xl font-bold">Закладки</h1>
        <p className="text-sm text-muted">@{session.user.username}</p>
      </header>
      <InfiniteFeed
        queryKey={queryKeys.bookmarks}
        path="/api/bookmarks"
        currentUser={session.user}
        emptyTitle="Сохраняйте посты на потом"
        emptyDescription="Нажмите на закладку под постом — и он появится здесь."
      />
    </div>
  );
}
