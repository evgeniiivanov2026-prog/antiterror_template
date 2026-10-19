"use client";

import Link from "next/link";
import { useActionState } from "react";
import { login, type AuthFormState } from "@/actions/auth";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";

const initial: AuthFormState = { error: null };

export function LoginForm() {
  const [state, action, pending] = useActionState(login, initial);

  return (
    <form action={action} className="w-full max-w-sm space-y-5">
      <div className="mb-2 flex size-12 items-center justify-center rounded-full bg-accent text-xl font-black text-white lg:hidden">
        P
      </div>
      <h1 className="text-3xl font-black">Войти в Pulse</h1>
      <label className="block text-sm text-muted">
        Email или @username
        <Input name="identifier" autoComplete="username" required className="mt-2" placeholder="ada@pulse.dev" />
      </label>
      <label className="block text-sm text-muted">
        Пароль
        <Input name="password" type="password" autoComplete="current-password" required className="mt-2" />
      </label>
      {state.error ? <p className="text-sm text-[#f4212e]">{state.error}</p> : null}
      <Button type="submit" disabled={pending} size="lg" className="w-full">
        {pending ? "Входим…" : "Войти"}
      </Button>
      <p className="text-[15px] text-muted">
        Нет аккаунта?{" "}
        <Link href="/register" className="text-accent hover:underline">
          Зарегистрироваться
        </Link>
      </p>
      <p className="rounded-xl border border-border p-3 text-sm text-muted lg:hidden">
        Демо-аккаунт: ada@pulse.dev
        <br />
        Пароль: password123
      </p>
    </form>
  );
}
