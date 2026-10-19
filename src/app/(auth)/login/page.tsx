import { redirect } from "next/navigation";
import { auth } from "@/auth";
import { LoginForm } from "@/app/(auth)/login/login-form";

export default async function LoginPage() {
  const session = await auth();
  if (session?.user) redirect("/");
  return <LoginForm />;
}
