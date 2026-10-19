import Link from "next/link";

export default function NotFound() {
  return (
    <div className="flex min-h-screen flex-col items-center justify-center gap-4 bg-black px-6 text-center">
      <h1 className="text-3xl font-black">Страница не найдена</h1>
      <p className="text-muted">Такого маршрута в Vox нет.</p>
      <Link href="/" className="text-accent hover:underline">
        На главную
      </Link>
    </div>
  );
}
