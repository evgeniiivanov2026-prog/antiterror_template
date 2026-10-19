"use server";

import { getUserId } from "@/lib/session";
import {
  publishPost,
  removePost,
  saveProfile,
  toggleBookmark,
  toggleFollow,
  toggleLike,
  toggleRepost,
} from "@/lib/mutations";
import { markNotificationsRead } from "@/lib/notifications";
import type { ActionResult, Me, PostView } from "@/types/social";
import { createPostSchema, firstIssue, idSchema, updateProfileSchema } from "@/lib/validators";

async function userIdOrError(): Promise<ActionResult<string>> {
  const userId = await getUserId();
  if (!userId) return { ok: false, error: "Нужно войти" };
  return { ok: true, data: userId };
}

export async function createPost(input: unknown): Promise<ActionResult<PostView>> {
  const authResult = await userIdOrError();
  if (!authResult.ok) return authResult;
  const parsed = createPostSchema.safeParse(input);
  if (!parsed.success) return { ok: false, error: firstIssue(parsed.error) };
  return publishPost(authResult.data, parsed.data);
}

export async function deletePost(postId: unknown): Promise<ActionResult<{ id: string }>> {
  const authResult = await userIdOrError();
  if (!authResult.ok) return authResult;
  const parsed = idSchema.safeParse(postId);
  if (!parsed.success) return { ok: false, error: "Некорректный пост" };
  return removePost(authResult.data, parsed.data);
}

export async function likePost(postId: unknown) {
  return interact(postId, toggleLike);
}

export async function repostPost(postId: unknown) {
  return interact(postId, toggleRepost);
}

export async function bookmarkPost(postId: unknown) {
  return interact(postId, toggleBookmark);
}

async function interact(
  postId: unknown,
  run: (userId: string, id: string) => ReturnType<typeof toggleLike>,
) {
  const authResult = await userIdOrError();
  if (!authResult.ok) return authResult;
  const parsed = idSchema.safeParse(postId);
  if (!parsed.success) return { ok: false as const, error: "Некорректный пост" };
  return run(authResult.data, parsed.data);
}

export async function followUser(targetId: unknown) {
  const authResult = await userIdOrError();
  if (!authResult.ok) return authResult;
  const parsed = idSchema.safeParse(targetId);
  if (!parsed.success) return { ok: false as const, error: "Некорректный пользователь" };
  return toggleFollow(authResult.data, parsed.data);
}

export async function updateProfile(input: unknown): Promise<ActionResult<Me>> {
  const authResult = await userIdOrError();
  if (!authResult.ok) return authResult;
  const parsed = updateProfileSchema.safeParse(input);
  if (!parsed.success) return { ok: false, error: firstIssue(parsed.error) };
  return saveProfile(authResult.data, parsed.data);
}

export async function readNotifications(): Promise<ActionResult<{ updated: number }>> {
  const authResult = await userIdOrError();
  if (!authResult.ok) return authResult;
  const updated = await markNotificationsRead(authResult.data);
  return { ok: true, data: { updated } };
}
