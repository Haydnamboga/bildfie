"use client";
import { use, useEffect, useState } from "react";
import { api } from "@/lib/api";
import { getCurrentUser } from "@/lib/auth";

type Milestone = { id: string; title: string; amount: string; status: string; dueDate?: string; _count?: { tasks: number }; payment?: { status: string } | null };
type Project = { id: string; ownerId: string };

export default function MilestonesPage({ params }: { params: Promise<{ projectId: string }> }) {
  const { projectId } = use(params);
  const user = getCurrentUser();
  const [milestones, setMilestones] = useState<Milestone[]>([]);
  const [project, setProject] = useState<Project | null>(null);
  const [form, setForm] = useState({ title: "", amount: "", description: "" });
  const [adding, setAdding] = useState(false);
  // Track per-milestone submit notes
  const [submitNotes, setSubmitNotes] = useState<Record<string, string>>({});
  const [submittingId, setSubmittingId] = useState<string | null>(null);

  useEffect(() => {
    api.listMilestones(projectId).then(r => setMilestones(r as Milestone[])).catch(() => {});
    api.getProject(projectId).then(r => setProject(r as Project)).catch(() => {});
  }, [projectId]);

  async function add(e: React.FormEvent) {
    e.preventDefault();
    const m = await api.createMilestone(projectId, { title: form.title, amount: Number(form.amount), description: form.description }) as Milestone;
    setMilestones(prev => [...prev, m]);
    setForm({ title: "", amount: "", description: "" });
    setAdding(false);
  }

  async function submitForReview(milestoneId: string) {
    setSubmittingId(milestoneId);
    try {
      const updated = await api.submitMilestone(projectId, milestoneId, submitNotes[milestoneId] || undefined) as Milestone;
      setMilestones(prev => prev.map(m => m.id === milestoneId ? { ...m, ...updated } : m));
      setSubmitNotes(prev => { const n = { ...prev }; delete n[milestoneId]; return n; });
    } catch (err: unknown) {
      alert((err as { message?: string }).message ?? "Failed to submit milestone.");
    } finally {
      setSubmittingId(null);
    }
  }

  async function approveMilestone(milestoneId: string) {
    try {
      const updated = await api.approveMilestone(projectId, milestoneId) as Milestone;
      setMilestones(prev => prev.map(m => m.id === milestoneId ? { ...m, ...updated } : m));
    } catch (err: unknown) {
      alert((err as { message?: string }).message ?? "Failed to approve milestone.");
    }
  }

  async function requestRevision(milestoneId: string) {
    try {
      const updated = await api.requestMilestoneRevision(projectId, milestoneId) as Milestone;
      setMilestones(prev => prev.map(m => m.id === milestoneId ? { ...m, ...updated } : m));
    } catch (err: unknown) {
      alert((err as { message?: string }).message ?? "Failed to request revision.");
    }
  }

  const isOwner = user && project && project.ownerId === user.id;

  return (
    <>
      
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
                  {/* Submit for review — shown to non-owners on ACTIVE milestones */}
                  {!isOwner && m.status === "ACTIVE" && (
                    <div style={{ marginTop: 8, display: "flex", gap: 8, alignItems: "center" }}>
                      <input
                        placeholder="Notes (optional)"
                        value={submitNotes[m.id] ?? ""}
                        onChange={e => setSubmitNotes(prev => ({ ...prev, [m.id]: e.target.value }))}
                        style={{ fontSize: 13, padding: "4px 8px" }}
                      />
                      <button
                        className="btn btn-primary"
                        style={{ fontSize: 13, padding: "4px 12px" }}
                        disabled={submittingId === m.id}
                        onClick={() => submitForReview(m.id)}
                      >
                        {submittingId === m.id ? "Submitting…" : "Submit for review"}
                      </button>
                    </div>
                  )}
                  {/* Approve / Request revision — shown to owner on SUBMITTED milestones */}
                  {isOwner && m.status === "SUBMITTED" && (
                    <div style={{ marginTop: 8, display: "flex", gap: 8 }}>
                      <button
                        className="btn btn-primary"
                        style={{ fontSize: 13, padding: "4px 12px" }}
                        onClick={() => approveMilestone(m.id)}
                      >
                        Approve
                      </button>
                      <button
                        className="btn btn-secondary"
                        style={{ fontSize: 13, padding: "4px 12px" }}
                        onClick={() => requestRevision(m.id)}
                      >
                        Request revision
                      </button>
                    </div>
                  )}
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
