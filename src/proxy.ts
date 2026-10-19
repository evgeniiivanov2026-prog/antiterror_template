import { NextResponse } from "next/server";
import { auth } from "@/auth";

const publicPaths = new Set(["/login", "/register"]);

export default auth((request) => {
  const { pathname } = request.nextUrl;
  const isPublic = publicPaths.has(pathname);
  const isAuthenticated = Boolean(request.auth);

  if (pathname.startsWith("/api/auth")) {
    return NextResponse.next();
  }

  if (!isAuthenticated && !isPublic) {
    if (pathname.startsWith("/api/")) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }
    const url = new URL("/login", request.nextUrl);
    url.searchParams.set("callbackUrl", pathname);
    return NextResponse.redirect(url);
  }

  if (isAuthenticated && isPublic) {
    return NextResponse.redirect(new URL("/", request.nextUrl));
  }

  return NextResponse.next();
});

export const config = {
  matcher: [
    "/((?!_next/static|_next/image|favicon.ico|uploads|.*\\.(?:svg|png|jpg|jpeg|gif|webp)$).*)",
  ],
};
