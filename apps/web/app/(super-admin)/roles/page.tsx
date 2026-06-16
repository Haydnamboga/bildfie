"use client";
import { AdminNav } from "@/components/AdminNav";

export default function RolesPage() {
  const roles = [
    { role: "USER", desc: "Default role. Can use marketplace, create projects, hire and be hired.", zones: "Marketplace, Projects, Dashboard" },
    { role: "ADMIN", desc: "Back-office access. Manages users, oversees projects, views audit logs. MFA required.", zones: "All user zones + /back-office" },
    { role: "SUPER_ADMIN", desc: "Full system access. Can promote/demote admins and access full audit trail. MFA required.", zones: "All zones + /super-admin" },
  ];

  return (
    <div style={{ minHeight: "100vh" }}>
      <div className="sidebar-layout">
        <AdminNav />
        <div className="main-content">
          <div className="page-header"><h1>Role definitions</h1><p>Role hierarchy — higher roles inherit lower-role access.</p></div>
          <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
            {roles.map(r => (
              <div key={r.role} className="card">
                <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 8 }}>
                  <span className={`badge badge-${r.role === "SUPER_ADMIN" ? "red" : r.role === "ADMIN" ? "blue" : "green"}`} style={{ fontSize: 14, padding: "4px 12px" }}>{r.role}</span>
                </div>
                <p style={{ marginBottom: 6 }}>{r.desc}</p>
                <p style={{ fontSize: 13, color: "#6b7280" }}>Zones: {r.zones}</p>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
