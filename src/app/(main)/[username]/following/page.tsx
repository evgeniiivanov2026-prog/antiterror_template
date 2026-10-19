import { notFound } from "next/navigation";
import { ColumnHeader } from "@/components/layout/column-header";
import { ConnectionList } from "@/components/user/connection-list";
import { requireUserId } from "@/lib/session";
import { getConnections } from "@/lib/users";

export default async function FollowingPage({ params }: { params: Promise<{ username: string }> }) {
  const { username } = await params;
  const userId = await requireUserId();
  const result = await getConnections(username, userId, "following");
  if (!result) notFound();

  return (
    <>
      <ColumnHeader title="Читает" backHref={`/${username.toLowerCase()}`} />
      <ConnectionList username={username.toLowerCase()} kind="following" initial={result.users} />
    </>
  );
}
