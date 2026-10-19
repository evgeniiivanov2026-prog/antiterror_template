import type { Metadata } from "next";
import { Inter } from "next/font/google";
import { auth } from "@/auth";
import { Providers } from "@/app/providers";
import "./globals.css";

const inter = Inter({
  variable: "--font-inter",
  subsets: ["latin", "cyrillic"],
});

export const metadata: Metadata = {
  title: "Vox — микроблоги",
  description: "Современная микроблоггинг-платформа в духе X.com",
};

export default async function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const session = await auth();

  return (
    <html lang="ru" className={`${inter.variable} h-full dark`}>
      <body className="min-h-full bg-black font-sans text-foreground antialiased">
        <Providers session={session}>{children}</Providers>
      </body>
    </html>
  );
}
