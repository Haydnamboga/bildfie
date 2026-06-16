"use client";
import { use, useEffect, useState } from "react";
import { api } from "@/lib/api";
import { AppNav } from "@/components/AppNav";

type Milestone = { id: string; title: string; amount: string; status: string; dueDate?: string; _count?: { tasks: number }; payment?: { status: string } | null };

export default function MilestonesPage({ params }: { params: Promise<{ projectId: string }> }) {
  const { projectId } = use(params);
  const [milestones, setMilestones] = useState<Milestone[]>([]);
  const [form, setForm] = useState({ title: "", amount: "", description: "" });
  const [adding, setAdding] = useState(false);

  useEffect(() => { api.listMilestones(projectId).then(r => setMilestones(r as Milestone[])).catch(() => {}); }, [projectId]);

  async function add(e: React.FormEvent) {
    e.preventDefault();
    const m = await api.createMilestone(projectId, { title: form.title, amount: Number(form.amount), description: form.description }) as Milestone;
    setMilestones(prev => [...prev, m]);
    setForm({ title: "", amount: "", description: "" });
    setAdding(false);
  }

  return (
    <>
      <AppNav />
      <div className="container" style={{ padding: "32px 20px" }}>
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24 }}>
          <div className="page-header" style={{ marginBottom: 0 }}><h1>Milestones</h1></div>
          <button className="btn btn-primary" onClick={() => setAdding(c => !c)}>+ Add milestone</button>
        </div>

        {adding && (
          <div className="card" style={{ marginBottom: 24 }}>
            <form onSubmit={add} style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              <div style={{ display: "flex", gap: 12 }}>
                <input placeholder="Title" value={form.title} onChange={e => setForm(f => ({ ...f, title: e.target.value }))} required style={{ flex: 2 }} />
                <input placeholder="Amount (KES)" type="number" value={form.amount} onChange={e => setForm(f => ({ ...f, amount: e.target.value }))} required style={{ flex: 1 }} />
              </div>
              <input placeholder="Description (optional)" value={form.description} onChange={e => setForm(f => ({ ...f, description: e.target.value }))} />
              <div style={{ display: "flex", gap: 8 }}>
                <button type="submit" className="btn btn-primary">Add</button>
                <button type="button" className="btn btn-secondary" onClick={() => setAdding(false)}>Cancel</button>
              </div>
            </form>
          </div>
        )}

        {milestones.length === 0 ? (
          <div className="card empty"><p>No milestones yet.</p></div>
        ) : (
          <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
            {milestones.map(m => (
              <div key={m.id} className="card" style={{ display: "flex", alignItems: "center", gap: 16 }}>
                <div style={{ flex: 1 }}>
                  <div style={{ display: "flex", alignItems: "center", gap: 8, marginBottom: 4 }}>
                    <h3 style={{ fontWeight: 600 }}>{m.title}</h3>
                    <span className={`badge badge-${m.status === "PAID" ? "green" : m.status === "ACTIVE" ? "blue" : "yellow"}`}>{m.status}</span>
                    {m.payment && <span className={`badge badge-${m.payment.status === "RELEASED" ? "green" : "yellow"}`}>Payment: {m.payment.status}</span>}
                  </div>
                  <p style={{ fontSize: 14, color: "#6b7280" }}>{m._count?.tasks ?? 0} tasks{m.dueDate ? ` · Due ${new Date(m.dueDate).toLocaleDateString()}` : ""}</p>
                </div>
                <div style={{ fontSize: 18, fontWeight: 700, color: "#2563eb" }}>KES {Number(m.amount).toLocaleString()}</div>
              </div>
            ))}
          </div>
        )}
      </div>
    </>
  );
}
