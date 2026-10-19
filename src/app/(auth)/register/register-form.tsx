"use client";

import Link from "next/link";
import { useActionState } from "react";
import { register, type AuthFormState } from "@/actions/auth";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";

const initial: AuthFormState = { error: null };

export function RegisterForm() {
  const [state, action, pending] = useActionState(register, initial);

  return (
    <form action={action} className="w-full max-w-sm space-y-4">
      <h1 className="text-3xl font-black">Создать аккаунт</h1>
      <label className="block text-sm text-muted">
        Имя
        <Input name="displayName" required maxLength={50} className="mt-2" placeholder="Ада Ловелейс" />
      </label>
      <label className="block text-sm text-muted">
        Имя пользователя
        <Input name="username" required minLength={3} maxLength={15} className="mt-2" placeholder="ada" />
      </label>
      <label className="block text-sm text-muted">
        Email
        <Input name="email" type="email" required autoComplete="email" className="mt-2" placeholder="ada@pulse.dev" />
      </label>
      <label className="block text-sm text-muted">
        Пароль
        <Input name="password" type="password" required minLength={8} autoComplete="new-password" className="mt-2" />
      </label>
      {state.error ? <p className="text-sm text-[#f4212e]">{state.error}</p> : null}
      <Button type="submit" disabled={pending} size="lg" className="w-full">
        {pending ? "Создаём…" : "Зарегистрироваться"}
      </Button>
      <p className="text-[15px] text-muted">
        Уже есть аккаунт?{" "}
        <Link href="/login" className="text-accent hover:underline">
          Войти
        </Link>
      </p>
    </form>
  );
}
