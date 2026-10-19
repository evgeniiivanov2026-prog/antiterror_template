import Link from "next/link";
import { ArrowLeft } from "lucide-react";

export function ColumnHeader({ title, backHref }: { title: string; backHref?: string }) {
  return (
    <header className="sticky top-0 z-20 flex items-center gap-6 border-b border-border bg-black/80 px-4 py-3 backdrop-blur-md">
      {backHref ? (
        <Link href={backHref} aria-label="Назад" className="rounded-full p-2 hover:bg-white/10">
          <ArrowLeft className="size-5" />
        </Link>
      ) : null}
      <h1 className="truncate text-xl font-bold">{title}</h1>
    </header>
  );
}
