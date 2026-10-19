import { redirect } from "next/navigation";
import { auth } from "@/auth";
import { Shell } from "@/components/layout/shell";
import { getUnreadCount } from "@/lib/notifications";
import { getTrends } from "@/lib/trends";
import { getMe, getSuggestions } from "@/lib/users";

export const dynamic = "force-dynamic";

export default async function MainLayout({ children }: { children: React.ReactNode }) {
  const session = await auth();
  if (!session?.user?.id) redirect("/login");

  const me = await getMe(session.user.id);
  if (!me) redirect("/login");

  const [trends, suggestions, unread] = await Promise.all([
    getTrends(),
    getSuggestions(me.id),
    getUnreadCount(me.id),
  ]);

  return (
    <Shell me={me} trends={trends} suggestions={suggestions} unread={unread}>
      {children}
    </Shell>
  );
}
