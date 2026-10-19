import { ColumnHeader } from "@/components/layout/column-header";
import { SettingsForm } from "@/components/settings/settings-form";
import { prisma } from "@/lib/prisma";
import { requireUserId } from "@/lib/session";
import { getMe } from "@/lib/users";

export const metadata = { title: "Настройки" };

export default async function SettingsPage() {
  const userId = await requireUserId();
  const me = await getMe(userId);
  if (!me) return null;
  const extra = await prisma.user.findUnique({
    where: { id: userId },
    select: { coverUrl: true },
  });

  return (
    <>
      <ColumnHeader title="Изменить профиль" backHref={`/${me.username}`} />
      <SettingsForm me={{ ...me, coverUrl: extra?.coverUrl ?? null }} />
    </>
  );
}
