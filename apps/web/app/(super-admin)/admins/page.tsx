"use client";
import { useEffect, useState } from "react";
import { AdminNav } from "@/components/AdminNav";

type Admin = { id: string; email: string; fullName: string; role: string; mfaEnabled: boolean; createdAt: string };

export default function AdminsPage() {
  const [admins, setAdmins] = useState<Admin[]>([]);

  useEffect(() => {
    const token = localStorage.getItem("bildfie_token");
    fetch(`${process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:4000"}/system/admins`, {
      headers: { Authorization: `Bearer ${token}` },
    }).then(r => r.json()).then(setAdmins).catch(() => {});
  }, []);

  async function promote(id: string) {
    const token = localStorage.getItem("bildfie_token");
    await fetch(`${process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:4000"}/system/admins/${id}/promote`, {
      method: "PATCH",
      headers: { Authorization: `Bearer ${token}` },
    });
    setAdmins(prev => prev.map(a => a.id === id ? { ...a, role: "SUPER_ADMIN" } : a));
  }

  return (
    <div style={{ minHeight: "100vh" }}>
      <div className="sidebar-layout">
        <AdminNav />
        <div className="main-content">
          <div className="page-header"><h1>Admins &amp; Super-admins</h1></div>
          <div className="card" style={{ padding: 0, overflow: "hidden" }}>
            <table>
              <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>MFA</th><th>Actions</th></tr></thead>
              <tbody>
                {admins.map(a => (
                  <tr key={a.id}>
                    <td style={{ fontWeight: 500 }}>{a.fullName}</td>
                    <td style={{ color: "#6b7280" }}>{a.email}</td>
                    <td><span className={`badge badge-${a.role === "SUPER_ADMIN" ? "red" : "blue"}`}>{a.role}</span></td>
                    <td>{a.mfaEnabled ? "✓" : "—"}</td>
                    <td>
                      {a.role === "ADMIN" && (
                        <button className="btn btn-secondary" style={{ fontSize: 13, padding: "4px 10px" }} onClick={() => promote(a.id)}>
                          Promote to Super-admin
                        </button>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
}
