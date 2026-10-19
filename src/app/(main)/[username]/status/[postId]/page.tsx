import { redirect } from "next/navigation";
import { auth } from "@/auth";
import { ThreadView } from "@/components/post/thread-view";

export default async function StatusPage({
  params,
}: {
  params: Promise<{ username: string; postId: string }>;
}) {
  const session = await auth();
  if (!session?.user) redirect("/login");
  const { postId } = await params;
  return <ThreadView postId={postId} user={session.user} />;
}
