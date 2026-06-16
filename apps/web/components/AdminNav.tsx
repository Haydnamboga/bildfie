"use client";
import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import { clearTokens, getCurrentUser, getRefreshToken } from "@/lib/auth";
import { api } from "@/lib/api";

export function AdminNav() {
  const path = usePathname();
  const router = useRouter();
  const user = getCurrentUser();
  const isSuper = user?.role === "SUPER_ADMIN";

  async function logout() {
    const rt = getRefreshToken();
    if (rt) await api.logout(rt).catch(() => {});
    clearTokens();
    router.push("/login");
  }

  const links = [
    { href: "/back-office/users", label: "Users" },
    { href: "/back-office/projects", label: "Projects" },
    { href: "/back-office/marketplace", label: "Marketplace" },
    { href: "/back-office/payments", label: "Payments" },
    { href: "/back-office/audit", label: "Audit log" },
    ...(isSuper ? [
      { href: "/super-admin/admins", label: "Admins" },
      { href: "/super-admin/roles", label: "Roles" },
      { href: "/super-admin/audit", label: "Full audit" },
      { href: "/super-admin/system", label: "System" },
    ] : []),
  ];

  return (
    <div className="sidebar">
      <div style={{ padding: "0 20px 16px", fontWeight: 700, borderBottom: "1px solid #e5e7eb", marginBottom: 8 }}>
        bildfie {isSuper ? "super-admin" : "admin"}
      </div>
      {links.map(l => (
        <Link key={l.href} href={l.href} className={path === l.href ? "active" : ""}>{l.label}</Link>
      ))}
      <div style={{ padding: "16px 20px", marginTop: "auto", borderTop: "1px solid #e5e7eb", position: "absolute", bottom: 0, width: "100%" }}>
        <button onClick={logout} className="btn btn-secondary" style={{ width: "100%", fontSize: 14 }}>Log out</button>
      </div>
    </div>
  );
}
