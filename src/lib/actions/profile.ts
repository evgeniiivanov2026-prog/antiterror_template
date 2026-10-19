"use server";

import { revalidatePath } from "next/cache";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/session";
import { profileSchema } from "@/lib/validations";

export async function updateProfileAction(input: unknown) {
  const user = await requireUser();
  const parsed = profileSchema.safeParse(input);
  if (!parsed.success) {
    return { ok: false as const, error: parsed.error.flatten().fieldErrors };
  }

  const { displayName, bio, avatarUrl, coverUrl } = parsed.data;

  const updated = await prisma.user.update({
    where: { id: user.id },
    data: {
      displayName,
      bio: bio || null,
      avatarUrl: avatarUrl || null,
      coverUrl: coverUrl || null,
    },
    select: {
      username: true,
      displayName: true,
      avatarUrl: true,
    },
  });

  revalidatePath("/");
  revalidatePath("/settings");
  revalidatePath(`/${updated.username}`);

  return {
    ok: true as const,
    user: updated,
  };
}
