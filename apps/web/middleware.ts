import { NextResponse, type NextRequest } from "next/server";
import { canAccessZone, type Zone } from "@bildfie/auth";
import type { UserRole } from "@bildfie/types";

const ZONE_PREFIXES: Array<{ prefix: string; zone: Zone }> = [
  { prefix: "/super-admin", zone: "super-admin" },
  { prefix: "/back-office", zone: "back-office" },
  { prefix: "/dashboard", zone: "app" },
  { prefix: "/offers", zone: "app" },
  { prefix: "/projects", zone: "app" },
  { prefix: "/payments", zone: "app" },
  { prefix: "/profile", zone: "app" },
  { prefix: "/marketplace", zone: "app" },
];

function resolveZone(pathname: string): Zone | null {
  return ZONE_PREFIXES.find(({ prefix }) => pathname.startsWith(prefix))?.zone ?? null;
}

function getRole(req: NextRequest): UserRole | null {
  const token = req.cookies.get("session")?.value;
  if (!token) return null;
  try {
    const parts = token.split(".");
    if (parts.length !== 3) return null;
    const payload = JSON.parse(atob((parts[1] ?? "").replace(/-/g, "+").replace(/_/g, "/")));
    if (payload.exp && Math.floor(Date.now() / 1000) > payload.exp) return null;
    return (payload.role as UserRole) ?? null;
  } catch {
    return null;
  }
}

export function middleware(req: NextRequest) {
  const zone = resolveZone(req.nextUrl.pathname);
  if (!zone) return NextResponse.next();

  const role = getRole(req);

  if (!role) {
    if (zone === "app") {
      const url = req.nextUrl.clone();
      url.pathname = "/login";
      return NextResponse.redirect(url);
    }
    return NextResponse.rewrite(new URL("/not-found", req.url));
  }

  if (!canAccessZone(role, zone)) {
    return NextResponse.rewrite(new URL("/not-found", req.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    "/dashboard/:path*",
    "/offers/:path*",
    "/projects/:path*",
    "/payments/:path*",
    "/profile/:path*",
    "/marketplace/:path*",
    "/back-office/:path*",
    "/super-admin/:path*",
  ],
};
