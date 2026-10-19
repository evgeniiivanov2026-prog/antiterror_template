import "server-only";
import { auth } from "@/auth";

export async function getUserId() {
  const session = await auth();
  return session?.user?.id ?? null;
}

export async function requireUserId() {
  const userId = await getUserId();
  if (!userId) {
    throw new Error("UNAUTHORIZED");
  }
  return userId;
}
