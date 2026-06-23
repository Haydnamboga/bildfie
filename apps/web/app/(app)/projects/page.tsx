"use client";
import { useEffect, useState } from "react";
import Link from "next/link";
import { api } from "@/lib/api";

type Project = { id: string; title: string; description?: string; status: string; _count: { tasks: number; team: number; milestones: number } };

export default function ProjectsPage() {
  const [projects, setProjects] = useState<Project[]>([]);
  const [creating, setCreating] = useState(false);
  const [form, setForm] = useState({ title: "", description: "" });
  const [error, setError] = useState("");

  useEffect(() => { api.listProjects().then(r => setProjects(r as Project[])).catch(() => {}); }, []);

  async function create(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    try {
      const p = await api.createProject(form) as Project;
      setProjects(prev => [p, ...prev]);
      setForm({ title: "", description: "" });
      setCreating(false);
    } catch (err: unknown) {
      setError((err as { message?: string }).message ?? "Failed");
    }
  }

  return (
    <>
      
      <div className="container" style={{ padding: "32px 20px" }}>
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24 }}>
          <div className="page-header" style={{ marginBottom: 0 }}>
            <h1>Projects</h1>
          </div>
          <button className="btn btn-primary" onClick={() => setCreating(c => !c)}>+ New project</button>
        </div>

        {creating && (
          <div className="card" style={{ marginBottom: 24 }}>
            <h2 style={{ fontSize: 16, fontWeight: 600, marginBottom: 16 }}>Create project</h2>
            <form onSubmit={create} style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              <input placeholder="Project title" value={form.title} onChange={e => setForm(f => ({ ...f, title: e.target.value }))} required />
              <textarea placeholder="Description (optional)" value={form.description} onChange={e => setForm(f => ({ ...f, description: e.target.value }))} rows={2} />
              {error && <p className="error">{error}</p>}
              <div style={{ display: "flex", gap: 8 }}>
                <button type="submit" className="btn btn-primary">Create</button>
                <button type="button" className="btn btn-secondary" onClick={() => setCreating(false)}>Cancel</button>
              </div>
            </form>
          </div>
        )}

        {projects.length === 0 ? (
          <div className="card empty"><p>No projects yet. Create your first one!</p></div>
        ) : (
          <div className="grid-2">
            {projects.map(p => (
              <div key={p.id} className="card">
                <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 8 }}>
                  <h3 style={{ fontWeight: 600 }}>{p.title}</h3>
                  <span className={`badge badge-${p.status === "ACTIVE" ? "green" : p.status === "DRAFT" ? "yellow" : "blue"}`}>{p.status}</span>
                </div>
                {p.description && <p style={{ color: "#6b7280", fontSize: 14, marginBottom: 12 }}>{p.description}</p>}
                <div style={{ fontSize: 13, color: "#6b7280", marginBottom: 12 }}>
                  {p._count.tasks} tasks · {p._count.team} members · {p._count.milestones} milestones
                </div>
                <div style={{ display: "flex", gap: 8 }}>
                  <Link href={`/projects/${p.id}/tasks`} className="btn btn-secondary" style={{ fontSize: 13, padding: "5px 12px" }}>Tasks</Link>
                  <Link href={`/projects/${p.id}/team`} className="btn btn-secondary" style={{ fontSize: 13, padding: "5px 12px" }}>Team</Link>
                  <Link href={`/projects/${p.id}/milestones`} className="btn btn-secondary" style={{ fontSize: 13, padding: "5px 12px" }}>Milestones</Link>
                  <Link href={`/projects/${p.id}/messages`} className="btn btn-secondary" style={{ fontSize: 13, padding: "5px 12px" }}>Chat</Link>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </>
  );
}
