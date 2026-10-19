import "server-only";
import { NextResponse } from "next/server";
import { BadCursorError } from "@/lib/cursor";
import { getUserId } from "@/lib/session";

type Parser<T> = {
  safeParse: (data: unknown) => { success: true; data: T } | { success: false; error?: unknown };
};

export async function requireApiUser() {
  const userId = await getUserId();
  if (!userId) {
    return {
      ok: false as const,
      response: NextResponse.json({ error: "Нужно войти" }, { status: 401 }),
    };
  }
  return { ok: true as const, userId };
}

export function parseSearch<T>(schema: Parser<T>, searchParams: URLSearchParams) {
  const raw: Record<string, string> = {};
  for (const [key, value] of searchParams.entries()) {
    if (value !== "") raw[key] = value;
  }
  const parsed = schema.safeParse(raw);
  if (!parsed.success) {
    return {
      ok: false as const,
      response: NextResponse.json({ error: "Некорректный запрос" }, { status: 400 }),
    };
  }
  return { ok: true as const, data: parsed.data };
}

export function json(data: unknown) {
  return NextResponse.json(data, {
    headers: { "Cache-Control": "no-store" },
  });
}

export function apiError(error: unknown) {
  if (error instanceof BadCursorError) {
    return NextResponse.json({ error: "Некорректный курсор" }, { status: 400 });
  }
  console.error(error);
  return NextResponse.json({ error: "Ошибка сервера" }, { status: 500 });
}
