import { HomeFeed } from "@/components/feed/home-feed";
import { ColumnHeader } from "@/components/layout/column-header";
import { getFeed } from "@/lib/feed";
import { requireUserId } from "@/lib/session";
import { getMe } from "@/lib/users";

export default async function HomePage() {
  const userId = await requireUserId();
  const me = await getMe(userId);
  if (!me) return null;
  const initialForYou = await getFeed({ userId, tab: "for-you" });

  return (
    <>
      <ColumnHeader title="Главная" />
      <HomeFeed me={me} initialForYou={initialForYou} />
    </>
  );
}
