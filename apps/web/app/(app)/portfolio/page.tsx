"use client";
import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { getCurrentUser } from "@/lib/auth";

const TRADE_CATEGORIES = [
  "ELECTRICAL", "PLUMBING", "CARPENTRY", "CIVIL", "TILING",
  "PAINTING", "ROOFING", "HVAC", "LANDSCAPING", "MASONRY", "WELDING", "GENERAL",
];

type PortfolioItem = {
  id: string;
  title: string;
  description?: string;
  imageUrl?: string;
  category?: string;
  completedAt?: string;
};

export default function PortfolioPage() {
  const user = getCurrentUser();
  const [items, setItems] = useState<PortfolioItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [adding, setAdding] = useState(false);
  const [form, setForm] = useState({
    title: "",
    description: "",
    imageUrl: "",
    category: "",
    completedAt: "",
  });
  const [formError, setFormError] = useState("");

  useEffect(() => {
    if (!user) return;
    api.getPortfolio(user.id)
      .then(data => setItems(data as PortfolioItem[]))
      .catch(() => setError("Failed to load portfolio."))
      .finally(() => setLoading(false));
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  async function addItem(e: React.FormEvent) {
    e.preventDefault();
    setFormError("");
    try {
      const body: Record<string, unknown> = { title: form.title };
      if (form.description) body.description = form.description;
      if (form.imageUrl) body.imageUrl = form.imageUrl;
      if (form.category) body.category = form.category;
      if (form.completedAt) body.completedAt = form.completedAt;
      const item = await api.addPortfolioItem(body as Parameters<typeof api.addPortfolioItem>[0]) as PortfolioItem;
      setItems(prev => [item, ...prev]);
      setForm({ title: "", description: "", imageUrl: "", category: "", completedAt: "" });
      setAdding(false);
    } catch (err: unknown) {
      setFormError((err as { message?: string }).message ?? "Failed to add item.");
    }
  }

  async function deleteItem(id: string) {
    try {
      await api.deletePortfolioItem(id);
      setItems(prev => prev.filter(i => i.id !== id));
    } catch (err: unknown) {
      alert((err as { message?: string }).message ?? "Failed to delete item.");
    }
  }

  return (
    <>
      
      <div className="container" style={{ padding: "32px 20px" }}>
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24 }}>
          <div className="page-header" style={{ marginBottom: 0 }}>
            <h1>My Portfolio</h1>
          </div>
          <button className="btn btn-primary" onClick={() => setAdding(c => !c)}>+ Add Item</button>
        </div>

        {adding && (
          <div className="card" style={{ marginBottom: 24 }}>
            <h2 style={{ fontSize: 16, fontWeight: 600, marginBottom: 16 }}>Add Portfolio Item</h2>
            <form onSubmit={addItem} style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              <div className="form-group">
                <label className="label">Title</label>
                <input
                  placeholder="e.g. Kitchen renovation – Westlands"
                  value={form.title}
                  onChange={e => setForm(f => ({ ...f, title: e.target.value }))}
                  required
                />
              </div>
              <div className="form-group">
                <label className="label">Description (optional)</label>
                <textarea
                  placeholder="Describe the project…"
                  value={form.description}
                  onChange={e => setForm(f => ({ ...f, description: e.target.value }))}
                  rows={3}
                />
              </div>
              <div style={{ display: "flex", gap: 12 }}>
                <div className="form-group" style={{ flex: 2 }}>
                  <label className="label">Image URL (optional)</label>
                  <input
                    placeholder="https://…"
                    value={form.imageUrl}
                    onChange={e => setForm(f => ({ ...f, imageUrl: e.target.value }))}
                  />
                </div>
                <div className="form-group" style={{ flex: 1 }}>
                  <label className="label">Category (optional)</label>
                  <select value={form.category} onChange={e => setForm(f => ({ ...f, category: e.target.value }))}>
                    <option value="">Select category</option>
                    {TRADE_CATEGORIES.map(c => <option key={c} value={c}>{c}</option>)}
                  </select>
                </div>
                <div className="form-group" style={{ flex: 1 }}>
                  <label className="label">Completed At (optional)</label>
                  <input
                    type="date"
                    value={form.completedAt}
                    onChange={e => setForm(f => ({ ...f, completedAt: e.target.value }))}
                  />
                </div>
              </div>
              {formError && <p className="error">{formError}</p>}
              <div style={{ display: "flex", gap: 8 }}>
                <button type="submit" className="btn btn-primary">Add</button>
                <button type="button" className="btn btn-secondary" onClick={() => setAdding(false)}>Cancel</button>
              </div>
            </form>
          </div>
        )}

        {loading ? (
          <div className="card empty"><p>Loading…</p></div>
        ) : error ? (
          <p className="error">{error}</p>
        ) : items.length === 0 ? (
          <div className="card empty"><p>No portfolio items yet. Add your first project!</p></div>
        ) : (
          <div className="grid-2">
            {items.map(item => (
              <div key={item.id} className="card">
                {item.imageUrl && (
                  <img
                    src={item.imageUrl}
                    alt={item.title}
                    style={{ width: "100%", height: 160, objectFit: "cover", borderRadius: 6, marginBottom: 12 }}
                  />
                )}
                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", marginBottom: 6 }}>
                  <h3 style={{ fontWeight: 600 }}>{item.title}</h3>
                  {item.category && <span className="badge badge-blue">{item.category}</span>}
                </div>
                {item.description && (
                  <p style={{ fontSize: 14, color: "#6b7280", marginBottom: 8 }}>
                    {item.description.length > 100 ? item.description.slice(0, 100) + "…" : item.description}
                  </p>
                )}
                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                  {item.completedAt && (
                    <span style={{ fontSize: 13, color: "#9ca3af" }}>
                      Completed {new Date(item.completedAt).toLocaleDateString()}
                    </span>
                  )}
                  <button
                    className="btn btn-secondary"
                    style={{ fontSize: 13, padding: "4px 12px", color: "#dc2626", marginLeft: "auto" }}
                    onClick={() => deleteItem(item.id)}
                  >
                    Delete
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </>
  );
}
