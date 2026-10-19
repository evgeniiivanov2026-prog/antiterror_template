import { NextResponse } from "next/server";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";

export async function GET(
  _request: Request,
  context: { params: Promise<{ username: string }> },
) {
  const session = await auth();
  const { username } = await context.params;

  const user = await prisma.user.findUnique({
    where: { username: username.toLowerCase() },
    select: {
      id: true,
      username: true,
      displayName: true,
      avatarUrl: true,
      bio: true,
      coverUrl: true,
      createdAt: true,
      _count: {
        select: {
          posts: { where: { parentId: null } },
          followers: true,
          following: true,
        },
      },
    },
  });

  if (!user) {
    return NextResponse.json({ error: "Пользователь не найден" }, { status: 404 });
  }

  let isFollowing = false;
  if (session?.user.id && session.user.id !== user.id) {
    const follow = await prisma.follow.findUnique({
      where: {
        followerId_followingId: {
          followerId: session.user.id,
          followingId: user.id,
        },
      },
    });
    isFollowing = Boolean(follow);
  }

  return NextResponse.json({
    id: user.id,
    username: user.username,
    displayName: user.displayName,
    avatarUrl: user.avatarUrl,
    bio: user.bio,
    coverUrl: user.coverUrl,
    createdAt: user.createdAt.toISOString(),
    counts: {
      posts: user._count.posts,
      followers: user._count.followers,
      following: user._count.following,
    },
    viewer: {
      isSelf: session?.user.id === user.id,
      isFollowing,
    },
  });
}
