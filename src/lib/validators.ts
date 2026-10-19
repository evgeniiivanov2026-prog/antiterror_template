import { z } from "zod";
import { MAX_MEDIA, MAX_POST_LENGTH, RESERVED_USERNAMES } from "@/lib/constants";
import { codePointLength, safeHttpUrl } from "@/lib/utils";

export function firstIssue(error: z.ZodError) {
  return error.issues[0]?.message ?? "Проверьте введённые данные";
}

const httpUrl = z
  .string()
  .trim()
  .max(500, "Слишком длинная ссылка")
  .refine((value) => safeHttpUrl(value) !== null, "Нужна ссылка http или https");

export const usernameSchema = z
  .string()
  .trim()
  .toLowerCase()
  .regex(/^[a-z0-9_]{3,15}$/, "3–15 символов: латиница, цифры и _")
  .refine((value) => !RESERVED_USERNAMES.has(value), "Это имя зарезервировано");

export const loginSchema = z.object({
  identifier: z
    .string()
    .trim()
    .min(1, "Введите email или @username")
    .max(200)
    .transform((value) => value.replace(/^@/, "").toLowerCase()),
  password: z.string().min(1, "Введите пароль").max(72),
});

export const registerSchema = z.object({
  displayName: z.string().trim().min(1, "Укажите имя").max(50, "Имя длиннее 50 символов"),
  username: usernameSchema,
  email: z.string().trim().toLowerCase().email("Некорректный email").max(200),
  password: z.string().min(8, "Минимум 8 символов").max(72, "Максимум 72 символа"),
});

export const updateProfileSchema = z.object({
  displayName: z.string().trim().min(1, "Укажите имя").max(50, "Имя длиннее 50 символов"),
  username: usernameSchema,
  bio: z.string().trim().max(160, "Биография длиннее 160 символов"),
  avatarUrl: z.string().trim().max(500).refine((value) => value === "" || safeHttpUrl(value) !== null, "Аватар: ссылка http(s)"),
  coverUrl: z.string().trim().max(500).refine((value) => value === "" || safeHttpUrl(value) !== null, "Обложка: ссылка http(s)"),
});

export const createPostSchema = z
  .object({
    content: z.string().max(2000).default(""),
    mediaUrls: z.array(httpUrl).max(MAX_MEDIA, "Не больше 4 изображений").default([]),
    parentId: z.string().cuid().nullable().optional(),
  })
  .superRefine((value, context) => {
    const content = value.content.trim();
    if (codePointLength(content) > MAX_POST_LENGTH) {
      context.addIssue({
        code: "custom",
        path: ["content"],
        message: `Максимум ${MAX_POST_LENGTH} символов`,
      });
    }
    if (content.length === 0 && value.mediaUrls.length === 0) {
      context.addIssue({
        code: "custom",
        path: ["content"],
        message: "Напишите текст или добавьте изображение",
      });
    }
  })
  .transform((value) => ({
    content: value.content.trim(),
    mediaUrls: value.mediaUrls,
    parentId: value.parentId ?? null,
  }));

export const cursorQuerySchema = z.object({
  cursor: z.string().min(1).max(500).optional(),
  limit: z.coerce.number().int().min(1).max(50).default(20),
});

export const feedQuerySchema = cursorQuerySchema.extend({
  tab: z.enum(["for-you", "following"]).default("for-you"),
});

export const profilePostsQuerySchema = cursorQuerySchema.extend({
  tab: z.enum(["posts", "replies", "media", "likes"]).default("posts"),
});

export const searchQuerySchema = cursorQuerySchema.extend({
  q: z.string().trim().min(1).max(100),
});

export const connectionQuerySchema = cursorQuerySchema.extend({
  kind: z.enum(["followers", "following"]),
});

export const idSchema = z.string().cuid();
