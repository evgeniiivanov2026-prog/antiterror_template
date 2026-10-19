import NextAuth from "next-auth";
import Credentials from "next-auth/providers/credentials";
import bcrypt from "bcryptjs";
import { prisma } from "@/lib/prisma";
import { loginSchema } from "@/lib/validations";

export const { handlers, auth, signIn, signOut } = NextAuth({
  trustHost: true,
  session: { strategy: "jwt" },
  pages: {
    signIn: "/login",
  },
  providers: [
    Credentials({
      name: "credentials",
      credentials: {
        login: { label: "Email or username", type: "text" },
        password: { label: "Password", type: "password" },
      },
      async authorize(credentials) {
        const parsed = loginSchema.safeParse(credentials);
        if (!parsed.success) return null;

        const { login, password } = parsed.data;
        const user = await prisma.user.findFirst({
          where: {
            OR: [{ email: login.toLowerCase() }, { username: login.toLowerCase() }],
          },
        });

        if (!user) return null;

        const valid = await bcrypt.compare(password, user.passwordHash);
        if (!valid) return null;

        return {
          id: user.id,
          email: user.email,
          name: user.displayName,
          image: user.avatarUrl,
          username: user.username,
          displayName: user.displayName,
          avatarUrl: user.avatarUrl,
        };
      },
    }),
  ],
  callbacks: {
    async jwt({ token, user, trigger, session }) {
      if (user) {
        token.id = user.id;
        token.username = user.username;
        token.displayName = user.displayName;
        token.avatarUrl = user.avatarUrl;
      }

      if (trigger === "update" && session) {
        token.displayName = session.displayName ?? token.displayName;
        token.avatarUrl = session.avatarUrl ?? token.avatarUrl;
      }

      return token;
    },
    async session({ session, token }) {
      const id = String(token.id ?? "");
      const username = String(token.username ?? "");
      const displayName = String(token.displayName ?? "");
      const avatarUrl = typeof token.avatarUrl === "string" ? token.avatarUrl : null;
      session.user.id = id;
      session.user.username = username;
      session.user.displayName = displayName;
      session.user.avatarUrl = avatarUrl;
      session.user.name = displayName;
      session.user.image = avatarUrl;
      return session;
    },
  },
});
