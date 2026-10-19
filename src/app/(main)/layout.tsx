import { redirect } from "next/navigation";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { AppShell } from "@/components/layout/app-shell";

export default async function MainLayout({ children }: { children: React.ReactNode }) {
  const session = await auth();
  if (!session?.user) {
    redirect("/login");
  }

  const followingIds = (
    await prisma.follow.findMany({
      where: { followerId: session.user.id },
      select: { followingId: true },
    })
  ).map((follow) => follow.followingId);

  const [topics, suggestions, unreadCount] = await Promise.all([
    prisma.hashtag.findMany({
      take: 6,
      orderBy: { posts: { _count: "desc" } },
      select: {
        name: true,
        _count: {
          select: {
            posts: true,
          },
        },
      },
    }),
    prisma.user.findMany({
      where: { id: { notIn: [...followingIds, session.user.id] } },
      take: 3,
      orderBy: { followers: { _count: "desc" } },
      select: {
        id: true,
        username: true,
        displayName: true,
        avatarUrl: true,
        bio: true,
      },
    }),
    prisma.notification.count({
      where: { recipientId: session.user.id, read: false },
    }),
  ]);

  return (
    <AppShell
      user={session.user}
      unreadCount={unreadCount}
      topics={topics
        .filter((topic) => topic._count.posts > 0)
        .map((topic) => ({ name: topic.name, posts: topic._count.posts }))}
      suggestions={suggestions.map((user) => ({ ...user, isFollowing: false }))}
    >
      {children}
    </AppShell>
  );
}
