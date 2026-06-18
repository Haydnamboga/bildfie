"use client";
import { useEffect, useState } from "react";
import { AdminNav } from "@/components/AdminNav";

type Project = { id: string; title: string; status: string; owner: { fullName: string; email: string }; _count: { team: number; tasks: number }; createdAt: string };

export default function AdminProjectsPage() {
  const [projects, setProjects] = useState<Project[]>([]);

  useEffect(() => {
    const token = localStorage.getItem("bildfie_token");
    fetch(`${process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:4000"}/admin/projects?pageSize=50`, {
      headers: { Authorization: `Bearer ${token}` },
    }).then(r => r.json()).then(d => setProjects(d.data ?? [])).catch(() => {});
  }, []);

  return (
    <div style={{ minHeight: "100vh" }}>
      <div className="sidebar-layout">
        <AdminNav />
        <div className="main-content">
          <div className="page-header"><h1>Projects ({projects.length})</h1></div>
          <div className="card" style={{ padding: 0, overflow: "hidden" }}>
            <table>
              <thead><tr><th>Title</th><th>Owner</th><th>Status</th><th>Team</th><th>Tasks</th><th>Created</th></tr></thead>
              <tbody>
                {projects.map(p => (
                  <tr key={p.id}>
                    <td style={{ fontWeight: 500 }}>{p.title}</td>
                    <td style={{ fontSize: 13, color: "#6b7280" }}>{p.owner.fullName}</td>
                    <td><span className={`badge badge-${p.status === "ACTIVE" ? "green" : p.status === "DRAFT" ? "yellow" : "blue"}`}>{p.status}</span></td>
                    <td>{p._count.team}</td>
                    <td>{p._count.tasks}</td>
                    <td style={{ fontSize: 13, color: "#9ca3af" }}>{new Date(p.createdAt).toLocaleDateString()}</td>
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
