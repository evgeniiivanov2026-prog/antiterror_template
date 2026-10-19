import { notFound } from "next/navigation";
import { ColumnHeader } from "@/components/layout/column-header";
import { ConnectionList } from "@/components/user/connection-list";
import { requireUserId } from "@/lib/session";
import { getConnections } from "@/lib/users";

export default async function FollowersPage({ params }: { params: Promise<{ username: string }> }) {
  const { username } = await params;
  const userId = await requireUserId();
  const result = await getConnections(username, userId, "followers");
  if (!result) notFound();

  return (
    <>
      <ColumnHeader title="Читатели" backHref={`/${username.toLowerCase()}`} />
      <ConnectionList username={username.toLowerCase()} kind="followers" initial={result.users} />
    </>
  );
}
