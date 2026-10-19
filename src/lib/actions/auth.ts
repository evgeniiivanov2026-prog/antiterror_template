"use server";

import bcrypt from "bcryptjs";
import { signIn } from "@/auth";
import { prisma } from "@/lib/prisma";
import { registerSchema } from "@/lib/validations";
import { AuthError } from "next-auth";

export type AuthFormState = {
  errors?: {
    username?: string[];
    displayName?: string[];
    email?: string[];
    password?: string[];
    login?: string[];
  };
  message?: string;
};

export async function registerAction(
  _state: AuthFormState | undefined,
  formData: FormData,
): Promise<AuthFormState> {
  const parsed = registerSchema.safeParse({
    username: formData.get("username"),
    displayName: formData.get("displayName"),
    email: formData.get("email"),
    password: formData.get("password"),
  });

  if (!parsed.success) {
    return { errors: parsed.error.flatten().fieldErrors };
  }

  const { username, displayName, email, password } = parsed.data;

  const existing = await prisma.user.findFirst({
    where: { OR: [{ email }, { username }] },
  });

  if (existing) {
    return {
      message:
        existing.email === email
          ? "Пользователь с таким email уже существует"
          : "Этот ник уже занят",
    };
  }

  const passwordHash = await bcrypt.hash(password, 12);

  await prisma.user.create({
    data: {
      username,
      displayName,
      email,
      passwordHash,
    },
  });

  try {
    await signIn("credentials", {
      login: email,
      password,
      redirectTo: "/",
    });
  } catch (error) {
    if (error instanceof AuthError) {
      return { message: "Аккаунт создан, но не удалось войти. Попробуйте ещё раз." };
    }
    throw error;
  }

  return {};
}
