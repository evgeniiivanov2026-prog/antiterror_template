import { redirect } from "next/navigation";
import { auth } from "@/auth";
import { RegisterForm } from "@/app/(auth)/register/register-form";

export default async function RegisterPage() {
  const session = await auth();
  if (session?.user) redirect("/");
  return <RegisterForm />;
}
