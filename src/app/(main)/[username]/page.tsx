import type { Metadata } from "next";
import { notFound, redirect } from "next/navigation";
import { ColumnHeader } from "@/components/layout/column-header";
import { ProfileView } from "@/components/profile/profile-view";
import { getLikedPosts, getProfilePosts } from "@/lib/posts";
import { requireUserId } from "@/lib/session";
import { getProfile } from "@/lib/users";
import type { ProfileTab } from "@/types/social";

function parseTab(value: string | undefined): ProfileTab {
  if (value === "replies" || value === "media" || value === "likes") return value;
  return "posts";
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ username: string }>;
}): Promise<Metadata> {
  const { username } = await params;
  return { title: `@${username.toLowerCase()}` };
}

export default async function ProfilePage({
  params,
  searchParams,
}: {
  params: Promise<{ username: string }>;
  searchParams: Promise<{ tab?: string }>;
}) {
  const [{ username }, query] = await Promise.all([params, searchParams]);
  const tab = parseTab(query.tab);
  const userId = await requireUserId();
  const profile = await getProfile(username, userId);
  if (!profile) notFound();
  if (username !== profile.username) {
    redirect(tab === "posts" ? `/${profile.username}` : `/${profile.username}?tab=${tab}`);
  }

  const initial =
    tab === "likes"
      ? await getLikedPosts(profile.id, userId)
      : await getProfilePosts(profile.id, userId, tab);

  return (
    <>
      <ColumnHeader title={profile.displayName} backHref="/" />
      <ProfileView profile={profile} tab={tab} initial={initial} viewerId={userId} />
    </>
  );
}
