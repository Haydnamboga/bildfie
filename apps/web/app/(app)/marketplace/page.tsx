"use client";
import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { AppNav } from "@/components/AppNav";

type Professional = { id: string; fullName: string; headline?: string; bio?: string; skills: string[]; hourlyRate?: string; avgRating?: number; reviewCount?: number };

export default function MarketplacePage() {
  const [results, setResults] = useState<Professional[]>([]);
  const [q, setQ] = useState("");
  const [loading, setLoading] = useState(false);

  useEffect(() => { search(); }, []);

  async function search(e?: React.FormEvent) {
    e?.preventDefault();
    setLoading(true);
    const r = await api.searchProfessionals({ q: q || undefined });
    setResults((r.data as Professional[]) ?? []);
    setLoading(false);
  }

  return (
    <>
      <AppNav />
      <div className="container" style={{ padding: "32px 20px" }}>
        <div className="page-header">
          <h1>Find professionals</h1>
          <p>Search by name, skill, or keyword.</p>
        </div>

        <form onSubmit={search} style={{ display: "flex", gap: 12, marginBottom: 24 }}>
          <input placeholder="Search…" value={q} onChange={e => setQ(e.target.value)} style={{ flex: 1 }} />
          <button type="submit" className="btn btn-primary" disabled={loading}>Search</button>
        </form>

        {results.length === 0 ? (
          <div className="card empty"><p>{loading ? "Searching…" : "No results."}</p></div>
        ) : (
          <div className="grid-2">
            {results.map(p => (
              <div key={p.id} className="card">
                <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 8 }}>
                  <h3 style={{ fontWeight: 600 }}>{p.fullName}</h3>
                  {p.avgRating != null && (
                    <span style={{ fontSize: 13, color: "#f59e0b", fontWeight: 600 }}>★ {p.avgRating.toFixed(1)} ({p.reviewCount})</span>
                  )}
                </div>
                {p.headline && <p style={{ color: "#6b7280", fontSize: 14, marginBottom: 8 }}>{p.headline}</p>}
                <div style={{ marginBottom: 12 }}>
                  {p.skills.slice(0, 5).map(s => <span key={s} className="tag">{s}</span>)}
                </div>
                {p.hourlyRate && <p style={{ fontWeight: 600, color: "#2563eb" }}>KES {Number(p.hourlyRate).toLocaleString()}/hr</p>}
              </div>
            ))}
          </div>
        )}
      </div>
    </>
  );
}
