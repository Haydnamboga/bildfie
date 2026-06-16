"use client";
import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { api } from "@/lib/api";
import { getCurrentUser } from "@/lib/auth";
import { AppNav } from "@/components/AppNav";

export default function DashboardPage() {
  const router = useRouter();
  const user = getCurrentUser();
  const [projects, setProjects] = useState<unknown[]>([]);
  const [notifications, setNotifications] = useState<unknown[]>([]);

  useEffect(() => {
    if (!user) { router.push("/login"); return; }
    api.listProjects().then(setProjects).catch(() => {});
    api.listMessages("").catch(() => {});
    fetch(`${process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:4000"}/notifications`, {
      headers: { Authorization: `Bearer ${localStorage.getItem("bildfie_token")}` },
    }).then(r => r.ok ? r.json() : []).then(setNotifications).catch(() => {});
  }, []);

  const ps = projects as Array<{ id: string; title: string; status: string; _count: { tasks: number; team: number } }>;

  return (
    <>
      <AppNav />
      <div className="container" style={{ padding: "32px 20px" }}>
        <div className="page-header">
          <h1>Welcome back{user ? `, ${user.fullName.split(" ")[0]}` : ""}!</h1>
          <p>Here{"'"}s what{"'"}s happening on your projects.</p>
        </div>

        <div className="grid-3" style={{ marginBottom: 32 }}>
          <div className="card stat">
            <div className="stat-value">{ps.length}</div>
            <div className="stat-label">Projects</div>
          </div>
          <div className="card stat">
            <div className="stat-value">{ps.reduce((s, p) => s + (p._count?.tasks ?? 0), 0)}</div>
            <div className="stat-label">Total tasks</div>
          </div>
          <div className="card stat">
            <div className="stat-value">{(notifications as Array<{ read: boolean }>).filter(n => !n.read).length}</div>
            <div className="stat-label">Unread notifications</div>
          </div>
        </div>

        <div className="grid-2">
          <div className="card">
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 16 }}>
              <h2 style={{ fontSize: 16, fontWeight: 600 }}>Recent projects</h2>
              <Link href="/projects" style={{ fontSize: 14 }}>View all</Link>
            </div>
            {ps.length === 0 ? (
              <div className="empty">
                <p>No projects yet.</p>
                <Link href="/projects" className="btn btn-primary" style={{ marginTop: 12 }}>Create project</Link>
              </div>
            ) : ps.slice(0, 4).map(p => (
              <Link key={p.id} href={`/projects/${p.id}/tasks`} style={{ display: "flex", justifyContent: "space-between", padding: "10px 0", borderBottom: "1px solid #f3f4f6", textDecoration: "none", color: "inherit" }}>
                <span style={{ fontWeight: 500 }}>{p.title}</span>
                <span className={`badge badge-${p.status === "ACTIVE" ? "green" : p.status === "DRAFT" ? "yellow" : "blue"}`}>{p.status}</span>
              </Link>
            ))}
          </div>

          <div className="card">
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 16 }}>
              <h2 style={{ fontSize: 16, fontWeight: 600 }}>Notifications</h2>
            </div>
            {(notifications as Array<{ id: string; title: string; body?: string; read: boolean }>).length === 0 ? (
              <div className="empty"><p>No notifications.</p></div>
            ) : (notifications as Array<{ id: string; title: string; body?: string; read: boolean }>).slice(0, 5).map(n => (
              <div key={n.id} style={{ padding: "10px 0", borderBottom: "1px solid #f3f4f6", opacity: n.read ? 0.5 : 1 }}>
                <p style={{ fontWeight: 500, fontSize: 14 }}>{n.title}</p>
                {n.body && <p style={{ fontSize: 13, color: "#6b7280" }}>{n.body}</p>}
              </div>
            ))}
          </div>
        </div>
      </div>
    </>
  );
}
