"use client";

import { useActionState } from "react";
import { registerAction } from "@/lib/actions/auth";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";

export function RegisterForm() {
  const [state, action, pending] = useActionState(registerAction, undefined);

  return (
    <form action={action} className="space-y-4">
      <div>
        <Input name="displayName" placeholder="Отображаемое имя" />
        {state?.errors?.displayName && (
          <p className="mt-1 text-sm text-rose-400">{state.errors.displayName[0]}</p>
        )}
      </div>
      <div>
        <Input name="username" placeholder="Ник (без @)" />
        {state?.errors?.username && (
          <p className="mt-1 text-sm text-rose-400">{state.errors.username[0]}</p>
        )}
      </div>
      <div>
        <Input name="email" type="email" placeholder="Email" />
        {state?.errors?.email && (
          <p className="mt-1 text-sm text-rose-400">{state.errors.email[0]}</p>
        )}
      </div>
      <div>
        <Input name="password" type="password" placeholder="Пароль (от 8 символов)" />
        {state?.errors?.password && (
          <p className="mt-1 text-sm text-rose-400">{state.errors.password[0]}</p>
        )}
      </div>
      {state?.message && <p className="text-sm text-rose-400">{state.message}</p>}
      <Button type="submit" className="h-12 w-full" disabled={pending}>
        Создать аккаунт
      </Button>
    </form>
  );
}
