"use client";
import Link from "next/link";
import { useRouter, usePathname } from "next/navigation";
import { clearTokens, getCurrentUser } from "@/lib/auth";
import { api } from "@/lib/api";
import { getRefreshToken } from "@/lib/auth";

export function AppNav() {
  const router = useRouter();
  const path = usePathname();
  const user = getCurrentUser();

  async function logout() {
    const rt = getRefreshToken();
    if (rt) await api.logout(rt).catch(() => {});
    clearTokens();
    router.push("/login");
  }

  const links = [
    { href: "/dashboard", label: "Dashboard" },
    { href: "/projects", label: "Projects" },
    { href: "/marketplace", label: "Marketplace" },
    { href: "/offers", label: "Offers" },
    { href: "/payments", label: "Payments" },
    { href: "/profile", label: "Profile" },
  ];

  return (
    <nav>
      <div className="container nav-inner">
        <Link href="/dashboard" className="nav-logo">bildfie</Link>
        <div className="nav-links">
          {links.map(l => (
            <Link key={l.href} href={l.href} style={path.startsWith(l.href) ? { color: "#2563eb", fontWeight: 600 } : {}}>{l.label}</Link>
          ))}
          {user && <span style={{ color: "#6b7280", fontSize: 14 }}>{user.fullName}</span>}
          <button onClick={logout} className="btn btn-secondary" style={{ fontSize: 14, padding: "6px 14px" }}>Log out</button>
        </div>
      </div>
    </nav>
  );
}
