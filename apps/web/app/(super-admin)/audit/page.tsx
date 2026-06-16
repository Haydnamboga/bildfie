"use client";
import { useEffect, useState } from "react";
import { AdminNav } from "@/components/AdminNav";

type Log = { id: string; action: string; entity?: string; entityId?: string; createdAt: string; actor?: { fullName: string; email: string; role: string } };

export default function SuperAuditPage() {
  const [logs, setLogs] = useState<Log[]>([]);
  const [total, setTotal] = useState(0);

  useEffect(() => {
    const token = localStorage.getItem("bildfie_token");
    fetch(`${process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:4000"}/system/audit?pageSize=100`, {
      headers: { Authorization: `Bearer ${token}` },
    }).then(r => r.json()).then(d => { setLogs(d.data ?? []); setTotal(d.total ?? 0); }).catch(() => {});
  }, []);

  return (
    <div style={{ minHeight: "100vh" }}>
      <div className="sidebar-layout">
        <AdminNav />
        <div className="main-content">
          <div className="page-header"><h1>Full audit log ({total} entries)</h1></div>
          <div className="card" style={{ padding: 0, overflow: "hidden" }}>
            <table>
              <thead><tr><th>Time</th><th>Actor</th><th>Role</th><th>Action</th><th>Entity</th></tr></thead>
              <tbody>
                {logs.map(l => (
                  <tr key={l.id}>
                    <td style={{ fontSize: 12, color: "#9ca3af", whiteSpace: "nowrap" }}>{new Date(l.createdAt).toLocaleString()}</td>
                    <td style={{ fontSize: 13 }}>{l.actor?.fullName ?? "system"}</td>
                    <td>{l.actor?.role ? <span className={`badge badge-${l.actor.role === "SUPER_ADMIN" ? "red" : l.actor.role === "ADMIN" ? "blue" : "green"}`}>{l.actor.role}</span> : "—"}</td>
                    <td style={{ fontFamily: "monospace", fontSize: 13 }}>{l.action}</td>
                    <td style={{ fontSize: 13, color: "#6b7280" }}>{l.entity}{l.entityId ? ` #${l.entityId.slice(0, 8)}` : ""}</td>
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
