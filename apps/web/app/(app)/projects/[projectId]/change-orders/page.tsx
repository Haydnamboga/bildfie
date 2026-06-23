"use client";
import { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import { api } from "@/lib/api";
import { getCurrentUser } from "@/lib/auth";

type ChangeOrder = {
  id: string;
  title: string;
  description: string;
  amount: number;
  status: string;
  requestedById: string;
  requestedBy?: { fullName: string };
};

type Project = { id: string; ownerId: string };

export default function ChangeOrdersPage() {
  const { projectId } = useParams<{ projectId: string }>();
  const user = getCurrentUser();

  const [changeOrders, setChangeOrders] = useState<ChangeOrder[]>([]);
  const [project, setProject] = useState<Project | null>(null);
  const [loading, setLoading] = useState(true);
  const [requesting, setRequesting] = useState(false);
  const [form, setForm] = useState({ title: "", description: "", amount: "" });
  const [formError, setFormError] = useState("");

  useEffect(() => {
    async function load() {
      try {
        const [orders, proj] = await Promise.all([
          api.listChangeOrders(projectId) as Promise<ChangeOrder[]>,
          api.getProject(projectId) as Promise<Project>,
        ]);
        setChangeOrders(orders);
        setProject(proj);
      } catch {
        // silently fail
      } finally {
        setLoading(false);
      }
    }
    load();
  }, [projectId]);

  async function requestChangeOrder(e: React.FormEvent) {
    e.preventDefault();
    setFormError("");
    try {
      const co = await api.createChangeOrder(projectId, {
        title: form.title,
        description: form.description,
        amount: Number(form.amount),
      }) as ChangeOrder;
      setChangeOrders(prev => [co, ...prev]);
      setForm({ title: "", description: "", amount: "" });
      setRequesting(false);
    } catch (err: unknown) {
      setFormError((err as { message?: string }).message ?? "Failed to create change order.");
    }
  }

  async function respond(id: string, action: "approve" | "reject") {
    try {
      const updated = await api.respondToChangeOrder(projectId, id, action) as ChangeOrder;
      setChangeOrders(prev => prev.map(co => co.id === id ? { ...co, ...updated } : co));
    } catch (err: unknown) {
      alert((err as { message?: string }).message ?? "Action failed.");
    }
  }

  const isOwner = user && project && project.ownerId === user.id;

  return (
    <>
      
      <div className="container" style={{ padding: "32px 20px" }}>
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24 }}>
          <div className="page-header" style={{ marginBottom: 0 }}>
            <h1>Change Orders</h1>
          </div>
          <button className="btn btn-primary" onClick={() => setRequesting(c => !c)}>+ Request Change Order</button>
        </div>

        {requesting && (
          <div className="card" style={{ marginBottom: 24 }}>
            <h2 style={{ fontSize: 16, fontWeight: 600, marginBottom: 16 }}>Request Change Order</h2>
            <form onSubmit={requestChangeOrder} style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              <div className="form-group">
                <label className="label">Title</label>
                <input
                  placeholder="Brief title of the change"
                  value={form.title}
                  onChange={e => setForm(f => ({ ...f, title: e.target.value }))}
                  required
                />
              </div>
              <div className="form-group">
                <label className="label">Description</label>
                <textarea
                  placeholder="Describe what change is needed and why…"
                  value={form.description}
                  onChange={e => setForm(f => ({ ...f, description: e.target.value }))}
                  rows={3}
                  required
                />
              </div>
              <div className="form-group" style={{ maxWidth: 200 }}>
                <label className="label">Amount (KES)</label>
                <input
                  type="number"
                  placeholder="0"
                  value={form.amount}
                  onChange={e => setForm(f => ({ ...f, amount: e.target.value }))}
                  required
                />
              </div>
              {formError && <p className="error">{formError}</p>}
              <div style={{ display: "flex", gap: 8 }}>
                <button type="submit" className="btn btn-primary">Submit</button>
                <button type="button" className="btn btn-secondary" onClick={() => setRequesting(false)}>Cancel</button>
              </div>
            </form>
          </div>
        )}

        {loading ? (
          <div className="card empty"><p>Loading…</p></div>
        ) : changeOrders.length === 0 ? (
          <div className="card empty"><p>No change orders yet.</p></div>
        ) : (
          <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
            {changeOrders.map(co => (
              <div key={co.id} className="card" style={{ display: "flex", alignItems: "flex-start", gap: 16 }}>
                <div style={{ flex: 1 }}>
                  <div style={{ display: "flex", alignItems: "center", gap: 8, marginBottom: 6 }}>
                    <h3 style={{ fontWeight: 600 }}>{co.title}</h3>
                    <span className={`badge badge-${
                      co.status === "APPROVED" ? "green" :
                      co.status === "REJECTED" ? "red" : "yellow"
                    }`}>{co.status}</span>
                  </div>
                  <p style={{ fontSize: 14, color: "#374151", marginBottom: 6 }}>{co.description}</p>
                  <div style={{ fontSize: 13, color: "#6b7280", display: "flex", gap: 16 }}>
                    <span style={{ fontWeight: 600, color: "#2563eb" }}>KES {Number(co.amount).toLocaleString()}</span>
                    {co.requestedBy && <span>Requested by {co.requestedBy.fullName}</span>}
                  </div>
                </div>
                {isOwner && co.status === "PENDING" && (
                  <div style={{ display: "flex", gap: 8, flexShrink: 0 }}>
                    <button
                      className="btn btn-primary"
                      style={{ fontSize: 13, padding: "4px 12px" }}
                      onClick={() => respond(co.id, "approve")}
                    >
                      Approve
                    </button>
                    <button
                      className="btn btn-secondary"
                      style={{ fontSize: 13, padding: "4px 12px", color: "#dc2626" }}
                      onClick={() => respond(co.id, "reject")}
                    >
                      Reject
                    </button>
                  </div>
                )}
              </div>
            ))}
          </div>
        )}
      </div>
    </>
  );
}
