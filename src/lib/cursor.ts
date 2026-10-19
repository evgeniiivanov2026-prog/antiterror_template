import { z } from "zod";

const cursorSchema = z.object({
  at: z.string().datetime(),
  id: z.string().min(1).max(200),
});

export class BadCursorError extends Error {
  constructor() {
    super("Некорректный курсор");
    this.name = "BadCursorError";
  }
}

export function encodeCursor(at: Date, id: string) {
  return Buffer.from(JSON.stringify({ at: at.toISOString(), id }), "utf8").toString("base64url");
}

export function decodeCursor(value: string) {
  try {
    const json: unknown = JSON.parse(Buffer.from(value, "base64url").toString("utf8"));
    const parsed = cursorSchema.safeParse(json);
    if (!parsed.success) return null;
    const date = new Date(parsed.data.at);
    if (Number.isNaN(date.getTime())) return null;
    return { at: date, id: parsed.data.id };
  } catch {
    return null;
  }
}

export function requireCursor(value: string | undefined) {
  if (!value) return null;
  const cursor = decodeCursor(value);
  if (!cursor) throw new BadCursorError();
  return cursor;
}

export function olderThan(cursor: { at: Date; id: string } | null, idField: "id" | "postId" = "id") {
  if (!cursor) return {};
  return {
    OR: [
      { createdAt: { lt: cursor.at } },
      { AND: [{ createdAt: cursor.at }, { [idField]: { lt: cursor.id } }] },
    ],
  };
}

export function newerThan(cursor: { at: Date; id: string } | null) {
  if (!cursor) return {};
  return {
    OR: [
      { createdAt: { gt: cursor.at } },
      { AND: [{ createdAt: cursor.at }, { id: { gt: cursor.id } }] },
    ],
  };
}
