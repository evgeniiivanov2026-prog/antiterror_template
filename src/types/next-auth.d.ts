import type { DefaultSession } from "next-auth";

declare module "next-auth" {
  interface Session {
    user: DefaultSession["user"] & {
      id: string;
      username: string;
      displayName: string;
      avatarUrl: string | null;
    };
  }

  interface User {
    id: string;
    username: string;
    displayName: string;
    avatarUrl: string | null;
  }
}

declare module "next-auth/jwt" {
  interface JWT {
    id: string;
    username: string;
    displayName: string;
    avatarUrl: string | null;
  }
}
