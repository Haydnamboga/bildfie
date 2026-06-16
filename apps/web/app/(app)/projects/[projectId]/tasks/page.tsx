"use client";
import { use, useEffect, useState } from "react";
import { api } from "@/lib/api";
import { AppNav } from "@/components/AppNav";

type Task = { id: string; title: string; status: string; assigneeId?: string; description?: string };

const STATUSES = ["TODO", "IN_PROGRESS", "IN_REVIEW", "DONE"];

export default function TasksPage({ params }: { params: Promise<{ projectId: string }> }) {
  const { projectId } = use(params);
  const [tasks, setTasks] = useState<Task[]>([]);
  const [form, setForm] = useState({ title: "", description: "" });
  const [adding, setAdding] = useState(false);

  useEffect(() => { api.listTasks(projectId).then(r => setTasks(r as Task[])).catch(() => {}); }, [projectId]);

  async function addTask(e: React.FormEvent) {
    e.preventDefault();
    const t = await api.createTask(projectId, form) as Task;
    setTasks(prev => [...prev, t]);
    setForm({ title: "", description: "" });
    setAdding(false);
  }

  async function moveTask(taskId: string, status: string) {
    await api.updateTask(projectId, taskId, { status });
    setTasks(prev => prev.map(t => t.id === taskId ? { ...t, status } : t));
  }

  async function deleteTask(taskId: string) {
    await api.deleteTask(projectId, taskId);
    setTasks(prev => prev.filter(t => t.id !== taskId));
  }

  return (
    <>
      <AppNav />
      <div className="container" style={{ padding: "32px 20px" }}>
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24 }}>
          <div className="page-header" style={{ marginBottom: 0 }}><h1>Tasks</h1></div>
          <button className="btn btn-primary" onClick={() => setAdding(c => !c)}>+ Add task</button>
        </div>

        {adding && (
          <div className="card" style={{ marginBottom: 24 }}>
            <form onSubmit={addTask} style={{ display: "flex", gap: 12 }}>
              <input placeholder="Task title" value={form.title} onChange={e => setForm(f => ({ ...f, title: e.target.value }))} required style={{ flex: 1 }} />
              <input placeholder="Description" value={form.description} onChange={e => setForm(f => ({ ...f, description: e.target.value }))} style={{ flex: 1 }} />
              <button type="submit" className="btn btn-primary">Add</button>
              <button type="button" className="btn btn-secondary" onClick={() => setAdding(false)}>Cancel</button>
            </form>
          </div>
        )}

        <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 16 }}>
          {STATUSES.map(status => (
            <div key={status}>
              <h3 style={{ fontSize: 13, fontWeight: 600, color: "#6b7280", textTransform: "uppercase", letterSpacing: ".05em", marginBottom: 12 }}>
                {status.replace(/_/g, " ")} ({tasks.filter(t => t.status === status).length})
              </h3>
              <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
                {tasks.filter(t => t.status === status).map(t => (
                  <div key={t.id} className="card" style={{ padding: 12 }}>
                    <p style={{ fontWeight: 500, fontSize: 14, marginBottom: 8 }}>{t.title}</p>
                    {t.description && <p style={{ fontSize: 12, color: "#9ca3af", marginBottom: 8 }}>{t.description}</p>}
                    <div style={{ display: "flex", gap: 4, flexWrap: "wrap" }}>
                      {STATUSES.filter(s => s !== status).map(s => (
                        <button key={s} onClick={() => moveTask(t.id, s)} className="btn btn-secondary" style={{ fontSize: 11, padding: "3px 8px" }}>→ {s.replace(/_/g, " ")}</button>
                      ))}
                      <button onClick={() => deleteTask(t.id)} style={{ fontSize: 11, padding: "3px 8px", background: "#fee2e2", color: "#991b1b", border: "none", borderRadius: 4, cursor: "pointer" }}>Delete</button>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      </div>
    </>
  );
}
