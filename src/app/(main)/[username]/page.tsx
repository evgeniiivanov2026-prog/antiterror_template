import { redirect } from "next/navigation";
import { auth } from "@/auth";
import { RESERVED_USERNAMES } from "@/lib/utils";
import { ProfileView } from "@/components/user/profile-view";
import { notFound } from "next/navigation";

export default async function ProfilePage({
  params,
}: {
  params: Promise<{ username: string }>;
}) {
  const session = await auth();
  if (!session?.user) redirect("/login");
  const { username } = await params;
  if (RESERVED_USERNAMES.has(username.toLowerCase())) {
    notFound();
  }

  return <ProfileView username={username.toLowerCase()} currentUser={session.user} />;
}
