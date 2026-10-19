"use server";

import { AuthError } from "next-auth";
import { hash } from "bcryptjs";
import { signIn, signOut } from "@/auth";
import { prisma } from "@/lib/prisma";
import { firstIssue, loginSchema, registerSchema } from "@/lib/validators";

export type AuthFormState = { error: string | null };

export async function login(_state: AuthFormState, formData: FormData): Promise<AuthFormState> {
  const parsed = loginSchema.safeParse({
    identifier: formData.get("identifier"),
    password: formData.get("password"),
  });
  if (!parsed.success) return { error: firstIssue(parsed.error) };

  try {
    await signIn("credentials", {
      identifier: parsed.data.identifier,
      password: parsed.data.password,
      redirectTo: "/",
    });
  } catch (error) {
    if (error instanceof AuthError) return { error: "Неверный email, имя или пароль" };
    throw error;
  }
  return { error: null };
}

export async function register(_state: AuthFormState, formData: FormData): Promise<AuthFormState> {
  const parsed = registerSchema.safeParse({
    displayName: formData.get("displayName"),
    username: formData.get("username"),
    email: formData.get("email"),
    password: formData.get("password"),
  });
  if (!parsed.success) return { error: firstIssue(parsed.error) };

  const existing = await prisma.user.findFirst({
    where: { OR: [{ email: parsed.data.email }, { username: parsed.data.username }] },
    select: { email: true, username: true },
  });
  if (existing?.email === parsed.data.email) return { error: "Этот email уже зарегистрирован" };
  if (existing?.username === parsed.data.username) return { error: "Это имя пользователя уже занято" };

  const passwordHash = await hash(parsed.data.password, 12);
  await prisma.user.create({
    data: {
      email: parsed.data.email,
      username: parsed.data.username,
      displayName: parsed.data.displayName,
      passwordHash,
    },
  });

  try {
    await signIn("credentials", {
      identifier: parsed.data.email,
      password: parsed.data.password,
      redirectTo: "/",
    });
  } catch (error) {
    if (error instanceof AuthError) return { error: "Аккаунт создан, но войти не удалось" };
    throw error;
  }
  return { error: null };
}

export async function logout() {
  await signOut({ redirectTo: "/login" });
}
