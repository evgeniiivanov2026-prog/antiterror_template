import Link from "next/link";

export default function ProfileNotFound() {
  return (
    <div className="px-8 py-16 text-center">
      <h1 className="text-3xl font-black">Такого пользователя нет</h1>
      <p className="mt-2 text-muted">Проверьте написание @username.</p>
      <Link href="/" className="mt-5 inline-block font-bold text-accent">
        На главную
      </Link>
    </div>
  );
}
