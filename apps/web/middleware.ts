import { NextResponse, type NextRequest } from "next/server";
import { canAccessZone, type Zone } from "@bildfie/auth";
import type { UserRole } from "@bildfie/types";

/**
 * THE gatekeeper (§7). Reads the authenticated role on every request and
 * returns 404 — never 403 — for any zone the user isn't entitled to, so the
 * existence of the back-office / super-admin areas stays invisible.
 *
 * Super-admin gets the strictest gate (MFA; optionally IP allow-list).
 */

// Path prefix → zone. Route groups like (back-office) don't appear in URLs,
// so we map by the real URL segments they expose.
const ZONE_PREFIXES: Array<{ prefix: string; zone: Zone }> = [
  { prefix: "/super-admin", zone: "super-admin" },
  { prefix: "/back-office", zone: "back-office" },
  { prefix: "/dashboard", zone: "app" },
  { prefix: "/offers", zone: "app" },
  { prefix: "/projects", zone: "app" },
  { prefix: "/payments", zone: "app" },
  { prefix: "/profile", zone: "app" },
];

function resolveZone(pathname: string): Zone | null {
  return ZONE_PREFIXES.find(({ prefix }) => pathname.startsWith(prefix))?.zone ?? null;
}

// TODO: replace with real session/JWT verification via @bildfie/auth.
function getRole(req: NextRequest): UserRole | null {
  const session = req.cookies.get("session")?.value;
  if (!session) return null;
  // Placeholder — decode + verify the token here.
  return "USER";
}

export function middleware(req: NextRequest) {
  const zone = resolveZone(req.nextUrl.pathname);
  if (!zone) {
    return NextResponse.next();
  }

  const role = getRole(req);

  // Not logged in: public zones are handled above; protected ones 404.
  if (!role) {
    if (zone === "app") {
      const url = req.nextUrl.clone();
      url.pathname = "/login";
      return NextResponse.redirect(url);
    }
    return NextResponse.rewrite(new URL("/404", req.url));
  }

  if (!canAccessZone(role, zone)) {
    // Hide existence — rewrite to 404, do not 403.
    return NextResponse.rewrite(new URL("/404", req.url));
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
    "/back-office/:path*",
    "/super-admin/:path*",
  ],
};
