"use client";
import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { AppNav } from "@/components/AppNav";

type Proposal = {
  id: string;
  coverLetter: string;
  amount: number;
  timeline?: string;
  status: string;
  jobPost?: { id: string; title: string };
};

export default function MyProposalsPage() {
  const [proposals, setProposals] = useState<Proposal[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  async function load() {
    setLoading(true);
    try {
      const data = await api.myProposals() as Proposal[];
      setProposals(data);
    } catch {
      setError("Failed to load proposals.");
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => { load(); }, []);

  async function withdraw(id: string) {
    try {
      await api.withdrawProposal(id);
      setProposals(prev => prev.filter(p => p.id !== id));
    } catch (err: unknown) {
      alert((err as { message?: string }).message ?? "Failed to withdraw proposal.");
    }
  }

  return (
    <>
      <AppNav />
      <div className="container" style={{ padding: "32px 20px" }}>
        <div className="page-header">
          <h1>My Proposals</h1>
        </div>

        {loading ? (
          <div className="card empty"><p>Loading…</p></div>
        ) : error ? (
          <p className="error">{error}</p>
        ) : proposals.length === 0 ? (
          <div className="card empty"><p>You haven&apos;t submitted any proposals yet.</p></div>
        ) : (
          <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
            {proposals.map(p => (
              <div key={p.id} className="card" style={{ display: "flex", alignItems: "flex-start", gap: 16 }}>
                <div style={{ flex: 1 }}>
                  <div style={{ display: "flex", alignItems: "center", gap: 8, marginBottom: 6 }}>
                    <h3 style={{ fontWeight: 600 }}>{p.jobPost?.title ?? "Job"}</h3>
                    <span className={`badge badge-${
                      p.status === "ACCEPTED" ? "green" :
                      p.status === "REJECTED" ? "red" :
                      p.status === "SHORTLISTED" ? "blue" : "yellow"
                    }`}>{p.status}</span>
                  </div>
                  <p style={{ fontSize: 14, color: "#374151", marginBottom: 6 }}>
                    {p.coverLetter.length > 120 ? p.coverLetter.slice(0, 120) + "…" : p.coverLetter}
                  </p>
                  <div style={{ fontSize: 13, color: "#6b7280", display: "flex", gap: 16 }}>
                    <span>KES {Number(p.amount).toLocaleString()}</span>
                    {p.timeline && <span>Timeline: {p.timeline}</span>}
                  </div>
                </div>
                {p.status === "PENDING" && (
                  <button
                    className="btn btn-secondary"
                    style={{ fontSize: 13, padding: "4px 12px", color: "#dc2626", whiteSpace: "nowrap" }}
                    onClick={() => withdraw(p.id)}
                  >
                    Withdraw
                  </button>
                )}
              </div>
            ))}
          </div>
        )}
      </div>
    </>
  );
}
