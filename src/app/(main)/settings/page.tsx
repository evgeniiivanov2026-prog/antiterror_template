import { redirect } from "next/navigation";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { SettingsForm } from "@/components/user/settings-form";

export default async function SettingsPage() {
  const session = await auth();
  if (!session?.user) redirect("/login");

  const user = await prisma.user.findUnique({
    where: { id: session.user.id },
    select: { bio: true },
  });

  return (
    <div>
      <header className="sticky top-0 z-20 border-b border-zinc-800 bg-black/75 px-4 py-3 text-xl font-bold backdrop-blur-md">
        Настройки профиля
      </header>
      <SettingsForm user={session.user} bio={user?.bio ?? ""} />
    </div>
  );
}
