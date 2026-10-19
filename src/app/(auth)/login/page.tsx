import { Suspense } from "react";
import Link from "next/link";
import { LoginForm } from "@/components/auth/login-form";

export default function LoginPage() {
  return (
    <div className="w-full max-w-sm">
      <div className="mb-8 flex h-12 w-12 items-center justify-center rounded-full bg-white text-xl font-black text-black lg:hidden">
        V
      </div>
      <h2 className="text-3xl font-black">Вход в Vox</h2>
      <p className="mt-2 text-muted">Демо-аккаунт: demo / demo1234</p>
      <div className="mt-8">
        <Suspense>
          <LoginForm />
        </Suspense>
      </div>
      <p className="mt-6 text-sm text-muted">
        Нет аккаунта?{" "}
        <Link href="/register" className="text-accent hover:underline">
          Зарегистрироваться
        </Link>
      </p>
    </div>
  );
}
