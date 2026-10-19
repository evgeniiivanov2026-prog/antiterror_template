import Link from "next/link";

export default function PostNotFound() {
  return (
    <div className="px-8 py-16 text-center">
      <h1 className="text-3xl font-black">Пост не найден</h1>
      <p className="mt-2 text-muted">Его удалили или ссылка неверная.</p>
      <Link href="/" className="mt-5 inline-block font-bold text-accent">
        В ленту
      </Link>
    </div>
  );
}
