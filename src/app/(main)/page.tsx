import { redirect } from "next/navigation";
import { auth } from "@/auth";
import { HomeFeed } from "@/components/post/home-feed";

export default async function HomePage() {
  const session = await auth();
  if (!session?.user) redirect("/login");
  return <HomeFeed user={session.user} />;
}
