import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";

export async function GET() {
  const tags = await prisma.hashtag.findMany({
    take: 6,
    orderBy: {
      posts: { _count: "desc" },
    },
    select: {
      name: true,
      _count: {
        select: {
          posts: true,
        },
      },
    },
  });

  return NextResponse.json(
    tags
      .filter((tag) => tag._count.posts > 0)
      .map((tag) => ({ name: tag.name, posts: tag._count.posts })),
  );
}
