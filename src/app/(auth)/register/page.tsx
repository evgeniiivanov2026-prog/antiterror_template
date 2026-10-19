import Link from "next/link";
import { RegisterForm } from "@/components/auth/register-form";

export default function RegisterPage() {
  return (
    <div className="w-full max-w-sm">
      <h2 className="text-3xl font-black">Создать аккаунт</h2>
      <p className="mt-2 text-muted">Ник, имя и пароль — и вы в ленте.</p>
      <div className="mt-8">
        <RegisterForm />
      </div>
      <p className="mt-6 text-sm text-muted">
        Уже есть аккаунт?{" "}
        <Link href="/login" className="text-accent hover:underline">
          Войти
        </Link>
      </p>
    </div>
  );
}
