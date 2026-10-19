import type { Metadata } from "next";
import { notFound, redirect } from "next/navigation";
import { ColumnHeader } from "@/components/layout/column-header";
import { ThreadView } from "@/components/thread/thread-view";
import { getAncestors, getPost, getReplies } from "@/lib/posts";
import { toPostView, type PostRecord } from "@/lib/serializers";
import { requireUserId } from "@/lib/session";
import { getMe } from "@/lib/users";

export const metadata: Metadata = { title: "Пост" };

export default async function PostPage({
  params,
}: {
  params: Promise<{ username: string; postId: string }>;
}) {
  const { username, postId } = await params;
  const userId = await requireUserId();
  const record = await getPost(postId, userId);
  if (!record) notFound();
  const post = toPostView(record as PostRecord);
  if (post.author.username !== username.toLowerCase()) {
    redirect(`/${post.author.username}/status/${post.id}`);
  }

  const [ancestors, replies, me] = await Promise.all([
    getAncestors(record.parentId, userId),
    getReplies(post.id, userId),
    getMe(userId),
  ]);
  if (!me) notFound();

  return (
    <>
      <ColumnHeader title="Пост" backHref="/" />
      <ThreadView me={me} ancestors={ancestors} post={post} replies={replies} />
    </>
  );
}
