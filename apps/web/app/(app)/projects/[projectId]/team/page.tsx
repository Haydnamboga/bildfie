"use client";
import { use, useEffect, useState } from "react";
import { api } from "@/lib/api";
import { AppNav } from "@/components/AppNav";

type Member = { id: string; userId: string; roleLabel?: string; user: { id: string; fullName: string; headline?: string } };

export default function TeamPage({ params }: { params: Promise<{ projectId: string }> }) {
  const { projectId } = use(params);
  const [members, setMembers] = useState<Member[]>([]);
  const [form, setForm] = useState({ userId: "", roleLabel: "" });
  const [adding, setAdding] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => { api.listTeam(projectId).then(r => setMembers(r as Member[])).catch(() => {}); }, [projectId]);

  async function invite(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    try {
      const m = await api.inviteToTeam(projectId, { userId: form.userId, roleLabel: form.roleLabel || undefined }) as Member;
      setMembers(prev => [...prev, m]);
      setForm({ userId: "", roleLabel: "" });
      setAdding(false);
    } catch (err: unknown) {
      setError((err as { message?: string }).message ?? "Failed");
    }
  }

  async function remove(memberId: string) {
    await api.removeFromTeam(projectId, memberId);
    setMembers(prev => prev.filter(m => m.id !== memberId));
  }

  return (
    <>
      <AppNav />
      <div className="container" style={{ padding: "32px 20px" }}>
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24 }}>
          <div className="page-header" style={{ marginBottom: 0 }}><h1>Team</h1></div>
          <button className="btn btn-primary" onClick={() => setAdding(c => !c)}>+ Invite member</button>
        </div>

        {adding && (
          <div className="card" style={{ marginBottom: 24 }}>
            <form onSubmit={invite} style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              <input placeholder="User ID" value={form.userId} onChange={e => setForm(f => ({ ...f, userId: e.target.value }))} required />
              <input placeholder="Role (e.g. Lead Carpenter)" value={form.roleLabel} onChange={e => setForm(f => ({ ...f, roleLabel: e.target.value }))} />
              {error && <p className="error">{error}</p>}
              <div style={{ display: "flex", gap: 8 }}>
                <button type="submit" className="btn btn-primary">Invite</button>
                <button type="button" className="btn btn-secondary" onClick={() => setAdding(false)}>Cancel</button>
              </div>
            </form>
          </div>
        )}

        {members.length === 0 ? (
          <div className="card empty"><p>No team members yet.</p></div>
        ) : (
          <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
            {members.map(m => (
              <div key={m.id} className="card" style={{ display: "flex", alignItems: "center", gap: 16 }}>
                <div style={{ flex: 1 }}>
                  <p style={{ fontWeight: 600 }}>{m.user.fullName}</p>
                  {m.roleLabel && <p style={{ fontSize: 14, color: "#6b7280" }}>{m.roleLabel}</p>}
                  {m.user.headline && <p style={{ fontSize: 13, color: "#9ca3af" }}>{m.user.headline}</p>}
                </div>
                <button onClick={() => remove(m.id)} className="btn btn-danger" style={{ fontSize: 13, padding: "5px 12px" }}>Remove</button>
              </div>
            ))}
          </div>
        )}
      </div>
    </>
  );
}
