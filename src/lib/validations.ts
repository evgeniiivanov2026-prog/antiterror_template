import { z } from "zod";
import { RESERVED_USERNAMES } from "@/lib/utils";

const usernameSchema = z
  .string()
  .trim()
  .toLowerCase()
  .min(3, "Минимум 3 символа")
  .max(20, "Максимум 20 символов")
  .regex(/^[a-z0-9_]+$/, "Только латиница, цифры и подчёркивание")
  .refine((value) => !RESERVED_USERNAMES.has(value), "Этот ник занят системой");

export const registerSchema = z.object({
  username: usernameSchema,
  displayName: z
    .string()
    .trim()
    .min(2, "Минимум 2 символа")
    .max(40, "Максимум 40 символов"),
  email: z.string().trim().email("Некорректный email").toLowerCase(),
  password: z
    .string()
    .min(8, "Минимум 8 символов")
    .max(72, "Максимум 72 символа"),
});

export const loginSchema = z.object({
  login: z.string().trim().min(1, "Введите email или ник"),
  password: z.string().min(1, "Введите пароль"),
});

export const createPostSchema = z.object({
  content: z
    .string()
    .trim()
    .min(1, "Пост не может быть пустым")
    .max(280, "Максимум 280 символов"),
  mediaUrls: z
    .array(z.string().url("Некорректная ссылка на медиа"))
    .max(4, "Не больше 4 изображений")
    .default([]),
  parentId: z.string().cuid().optional().nullable(),
});

export const postIdSchema = z.object({
  postId: z.string().cuid("Некорректный идентификатор поста"),
});

export const userIdSchema = z.object({
  userId: z.string().cuid("Некорректный идентификатор пользователя"),
});

export const profileSchema = z.object({
  displayName: z
    .string()
    .trim()
    .min(2, "Минимум 2 символа")
    .max(40, "Максимум 40 символов"),
  bio: z.string().trim().max(160, "Максимум 160 символов").optional().default(""),
  avatarUrl: z
    .string()
    .trim()
    .url("Некорректная ссылка")
    .optional()
    .or(z.literal("")),
  coverUrl: z
    .string()
    .trim()
    .url("Некорректная ссылка")
    .optional()
    .or(z.literal("")),
});

export const feedQuerySchema = z.object({
  tab: z.enum(["for-you", "following"]).default("for-you"),
  cursor: z.string().cuid().optional(),
  limit: z.coerce.number().int().min(1).max(50).default(20),
});

export const searchQuerySchema = z.object({
  q: z.string().trim().min(1).max(80),
  cursor: z.string().cuid().optional(),
  limit: z.coerce.number().int().min(1).max(50).default(20),
});

export const cursorQuerySchema = z.object({
  cursor: z.string().cuid().optional(),
  limit: z.coerce.number().int().min(1).max(50).default(20),
});

export type RegisterInput = z.infer<typeof registerSchema>;
export type LoginInput = z.infer<typeof loginSchema>;
export type CreatePostInput = z.infer<typeof createPostSchema>;
export type ProfileInput = z.infer<typeof profileSchema>;
