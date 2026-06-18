"use client";
import { useEffect, useState } from "react";
import { AdminNav } from "@/components/AdminNav";

type User = { id: string; email: string; fullName: string; role: string; emailVerified: boolean; createdAt: string };

export default function AdminUsersPage() {
  const [users, setUsers] = useState<User[]>([]);
  const [q, setQ] = useState("");
  const [total, setTotal] = useState(0);

  async function load(query = "") {
    const token = localStorage.getItem("bildfie_token");
    const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:4000"}/admin/users?q=${encodeURIComponent(query)}&pageSize=50`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    if (res.ok) {
      const data = await res.json();
      setUsers(data.data);
      setTotal(data.total);
    }
  }

  useEffect(() => { load(); }, []);

  async function changeRole(userId: string, role: string) {
    const token = localStorage.getItem("bildfie_token");
    await fetch(`${process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:4000"}/admin/users/${userId}/role`, {
      method: "PATCH",
      headers: { "Content-Type": "application/json", Authorization: `Bearer ${token}` },
      body: JSON.stringify({ role }),
    });
    setUsers(prev => prev.map(u => u.id === userId ? { ...u, role } : u));
  }

  return (
    <div style={{ minHeight: "100vh" }}>
      <div className="sidebar-layout">
        <AdminNav />
        <div className="main-content">
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24 }}>
            <div className="page-header" style={{ marginBottom: 0 }}><h1>Users ({total})</h1></div>
            <div style={{ display: "flex", gap: 8 }}>
              <input placeholder="Search…" value={q} onChange={e => setQ(e.target.value)} onKeyDown={e => e.key === "Enter" && load(q)} style={{ width: 220 }} />
              <button className="btn btn-primary" onClick={() => load(q)}>Search</button>
            </div>
          </div>
          <div className="card" style={{ padding: 0, overflow: "hidden" }}>
            <table>
              <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Verified</th><th>Joined</th><th>Actions</th></tr></thead>
              <tbody>
                {users.map(u => (
                  <tr key={u.id}>
                    <td style={{ fontWeight: 500 }}>{u.fullName}</td>
                    <td style={{ color: "#6b7280" }}>{u.email}</td>
                    <td><span className={`badge badge-${u.role === "SUPER_ADMIN" ? "red" : u.role === "ADMIN" ? "blue" : "green"}`}>{u.role}</span></td>
                    <td>{u.emailVerified ? "✓" : "—"}</td>
                    <td style={{ color: "#9ca3af", fontSize: 13 }}>{new Date(u.createdAt).toLocaleDateString()}</td>
                    <td>
                      <select value={u.role} onChange={e => changeRole(u.id, e.target.value)} style={{ width: "auto", fontSize: 13, padding: "4px 8px" }}>
                        <option value="USER">USER</option>
                        <option value="ADMIN">ADMIN</option>
                        <option value="SUPER_ADMIN">SUPER_ADMIN</option>
                      </select>
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
