"use client";

import { useState } from "react";
import { signIn } from "next-auth/react";
import { useRouter, useSearchParams } from "next/navigation";
import { loginSchema } from "@/lib/validations";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";

export function LoginForm() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const [error, setError] = useState<string | null>(null);
  const [pending, setPending] = useState(false);

  async function onSubmit(formData: FormData) {
    setError(null);
    const parsed = loginSchema.safeParse({
      login: formData.get("login"),
      password: formData.get("password"),
    });
    if (!parsed.success) {
      setError(parsed.error.flatten().fieldErrors.login?.[0] ?? "Проверьте данные");
      return;
    }

    setPending(true);
    const result = await signIn("credentials", {
      ...parsed.data,
      redirect: false,
    });
    setPending(false);

    if (!result || result.error) {
      setError("Неверный логин или пароль");
      return;
    }

    router.push(searchParams.get("callbackUrl") || "/");
    router.refresh();
  }

  return (
    <form action={onSubmit} className="space-y-4">
      <Input name="login" placeholder="Email или @ник" autoComplete="username" />
      <Input
        name="password"
        type="password"
        placeholder="Пароль"
        autoComplete="current-password"
      />
      {error && <p className="text-sm text-rose-400">{error}</p>}
      <Button type="submit" className="h-12 w-full" disabled={pending}>
        Войти
      </Button>
    </form>
  );
}
