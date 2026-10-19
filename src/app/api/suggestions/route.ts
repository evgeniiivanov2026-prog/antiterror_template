import { NextResponse } from "next/server";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";

export async function GET() {
  const session = await auth();
  const currentUserId = session?.user.id;

  const followingIds = currentUserId
    ? (
        await prisma.follow.findMany({
          where: { followerId: currentUserId },
          select: { followingId: true },
        })
      ).map((follow) => follow.followingId)
    : [];

  const users = await prisma.user.findMany({
    where: {
      id: {
        notIn: [...followingIds, ...(currentUserId ? [currentUserId] : [])],
      },
    },
    take: 4,
    orderBy: { followers: { _count: "desc" } },
    select: {
      id: true,
      username: true,
      displayName: true,
      avatarUrl: true,
      bio: true,
    },
  });

  return NextResponse.json(
    users.map((user) => ({ ...user, isFollowing: false })),
  );
}
