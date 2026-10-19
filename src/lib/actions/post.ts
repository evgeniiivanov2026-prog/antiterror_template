"use server";

import { revalidatePath } from "next/cache";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/session";
import { createPostSchema, postIdSchema } from "@/lib/validations";
import { serializePost, syncHashtags, postInclude } from "@/lib/posts";
import { createNotification } from "@/lib/notifications";

export async function createPostAction(input: unknown) {
  const user = await requireUser();
  const parsed = createPostSchema.safeParse(input);
  if (!parsed.success) {
    return { ok: false as const, error: parsed.error.flatten().fieldErrors };
  }

  const { content, mediaUrls, parentId } = parsed.data;

  if (parentId) {
    const parent = await prisma.post.findUnique({
      where: { id: parentId },
      select: { id: true, authorId: true },
    });
    if (!parent) {
      return { ok: false as const, error: { content: ["Родительский пост не найден"] } };
    }
  }

  const post = await prisma.post.create({
    data: {
      content,
      mediaUrls,
      authorId: user.id,
      parentId: parentId ?? null,
    },
    include: postInclude(user.id),
  });

  await syncHashtags(post.id, content);

  if (parentId) {
    const parent = await prisma.post.findUnique({
      where: { id: parentId },
      select: { authorId: true },
    });
    if (parent) {
      await createNotification({
        recipientId: parent.authorId,
        issuerId: user.id,
        type: "REPLY",
        postId: post.id,
      });
    }
  }

  revalidatePath("/");
  if (parentId) {
    revalidatePath(`/status/${parentId}`);
  }

  return { ok: true as const, post: serializePost(post) };
}

export async function deletePostAction(input: unknown) {
  const user = await requireUser();
  const parsed = postIdSchema.safeParse(input);
  if (!parsed.success) {
    return { ok: false as const, error: "Некорректный пост" };
  }

  const post = await prisma.post.findUnique({
    where: { id: parsed.data.postId },
    select: { id: true, authorId: true },
  });

  if (!post) {
    return { ok: false as const, error: "Пост не найден" };
  }

  if (post.authorId !== user.id) {
    return { ok: false as const, error: "Нельзя удалить чужой пост" };
  }

  await prisma.post.delete({ where: { id: post.id } });
  revalidatePath("/");
  return { ok: true as const };
}
