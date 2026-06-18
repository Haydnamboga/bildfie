"use client";
import { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import { api } from "@/lib/api";
import { getCurrentUser } from "@/lib/auth";
import { AppNav } from "@/components/AppNav";

type JobPost = {
  id: string;
  title: string;
  description: string;
  category: string;
  location: string;
  budgetMin?: number;
  budgetMax?: number;
  dueDate?: string;
  createdAt: string;
  status: string;
  ownerId: string;
};

type Proposal = {
  id: string;
  coverLetter: string;
  amount: number;
  timeline?: string;
  status: string;
  proId: string;
  pro?: { fullName: string; email: string };
};

export default function JobDetailPage() {
  const { id } = useParams<{ id: string }>();
  const user = getCurrentUser();

  const [job, setJob] = useState<JobPost | null>(null);
  const [proposals, setProposals] = useState<Proposal[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  // Proposal form
  const [proposalForm, setProposalForm] = useState({ coverLetter: "", amount: "", timeline: "" });
  const [submitting, setSubmitting] = useState(false);
  const [proposalError, setProposalError] = useState("");
  const [proposalSuccess, setProposalSuccess] = useState(false);

  useEffect(() => {
    async function load() {
      try {
        const j = await api.getJobPost(id) as JobPost;
        setJob(j);
        if (user && j.ownerId === user.id) {
          const p = await api.getJobPostProposals(id) as Proposal[];
          setProposals(p);
        }
      } catch {
        setError("Failed to load job.");
      } finally {
        setLoading(false);
      }
    }
    load();
  }, [id]); // eslint-disable-line react-hooks/exhaustive-deps

  async function submitProposal(e: React.FormEvent) {
    e.preventDefault();
    setProposalError("");
    setSubmitting(true);
    try {
      await api.submitProposal(id, {
        coverLetter: proposalForm.coverLetter,
        amount: Number(proposalForm.amount),
        timeline: proposalForm.timeline || undefined,
      });
      setProposalSuccess(true);
      setProposalForm({ coverLetter: "", amount: "", timeline: "" });
    } catch (err: unknown) {
      setProposalError((err as { message?: string }).message ?? "Failed to submit proposal.");
    } finally {
      setSubmitting(false);
    }
  }

  async function respond(proposalId: string, action: "shortlist" | "accept" | "reject") {
    try {
      const updated = await api.respondToProposal(proposalId, action) as Proposal;
      setProposals(prev => prev.map(p => p.id === proposalId ? { ...p, ...updated } : p));
    } catch (err: unknown) {
      alert((err as { message?: string }).message ?? "Action failed.");
    }
  }

  if (loading) return <><AppNav /><div className="container" style={{ padding: 32 }}>Loading…</div></>;
  if (error || !job) return <><AppNav /><div className="container" style={{ padding: 32 }}><p className="error">{error || "Job not found."}</p></div></>;

  const isOwner = user && job.ownerId === user.id;

  return (
    <>
      <AppNav />
      <div className="container" style={{ padding: "32px 20px", maxWidth: 800 }}>
        {/* Job Details */}
        <div className="page-header">
          <div style={{ display: "flex", alignItems: "center", gap: 12, flexWrap: "wrap" }}>
            <h1>{job.title}</h1>
            <span className="badge badge-blue">{job.category}</span>
            <span className={`badge badge-${job.status === "OPEN" ? "green" : "yellow"}`}>{job.status}</span>
          </div>
        </div>

        <div className="card" style={{ marginBottom: 24 }}>
          <p style={{ color: "#374151", lineHeight: 1.6, marginBottom: 16 }}>{job.description}</p>
          <div style={{ display: "flex", gap: 24, fontSize: 14, color: "#6b7280", flexWrap: "wrap" }}>
            <span>📍 {job.location}</span>
            {(job.budgetMin || job.budgetMax) && (
              <span>
                💰 KES {job.budgetMin ? Number(job.budgetMin).toLocaleString() : "?"} – {job.budgetMax ? Number(job.budgetMax).toLocaleString() : "?"}
              </span>
            )}
            {job.dueDate && <span>📅 Due {new Date(job.dueDate).toLocaleDateString()}</span>}
            <span>Posted {new Date(job.createdAt).toLocaleDateString()}</span>
          </div>
        </div>

        {/* Submit Proposal (non-owners) */}
        {!isOwner && (
          <div className="card" style={{ marginBottom: 24 }}>
            <h2 style={{ fontSize: 16, fontWeight: 600, marginBottom: 16 }}>Submit a Proposal</h2>
            {proposalSuccess ? (
              <p style={{ color: "#16a34a" }}>Your proposal was submitted successfully!</p>
            ) : (
              <form onSubmit={submitProposal} style={{ display: "flex", flexDirection: "column", gap: 12 }}>
                <div className="form-group">
                  <label className="label">Cover Letter</label>
                  <textarea
                    placeholder="Tell the client why you're the right fit…"
                    value={proposalForm.coverLetter}
                    onChange={e => setProposalForm(f => ({ ...f, coverLetter: e.target.value }))}
                    rows={4}
                    required
                  />
                </div>
                <div style={{ display: "flex", gap: 12 }}>
                  <div className="form-group" style={{ flex: 1 }}>
                    <label className="label">Amount (KES)</label>
                    <input
                      type="number"
                      placeholder="Your proposed amount"
                      value={proposalForm.amount}
                      onChange={e => setProposalForm(f => ({ ...f, amount: e.target.value }))}
                      required
                    />
                  </div>
                  <div className="form-group" style={{ flex: 1 }}>
                    <label className="label">Timeline (optional)</label>
                    <input
                      placeholder="e.g. 2 weeks"
                      value={proposalForm.timeline}
                      onChange={e => setProposalForm(f => ({ ...f, timeline: e.target.value }))}
                    />
                  </div>
                </div>
                {proposalError && <p className="error">{proposalError}</p>}
                <div>
                  <button type="submit" className="btn btn-primary" disabled={submitting}>
                    {submitting ? "Submitting…" : "Submit Proposal"}
                  </button>
                </div>
              </form>
            )}
          </div>
        )}

        {/* My Proposals section (owner only) */}
        {isOwner && (
          <div className="card">
            <h2 style={{ fontSize: 16, fontWeight: 600, marginBottom: 16 }}>Proposals ({proposals.length})</h2>
            {proposals.length === 0 ? (
              <p style={{ color: "#6b7280", fontSize: 14 }}>No proposals yet.</p>
            ) : (
              <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
                {proposals.map(p => (
                  <div key={p.id} style={{ borderTop: "1px solid #e5e7eb", paddingTop: 12 }}>
                    <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 8 }}>
                      <div>
                        <span style={{ fontWeight: 600 }}>{p.pro?.fullName ?? "Pro"}</span>
                        <span style={{ fontSize: 13, color: "#6b7280", marginLeft: 8 }}>{p.pro?.email}</span>
                      </div>
                      <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                        <span style={{ fontWeight: 600, color: "#2563eb" }}>KES {Number(p.amount).toLocaleString()}</span>
                        <span className={`badge badge-${p.status === "ACCEPTED" ? "green" : p.status === "REJECTED" ? "red" : p.status === "SHORTLISTED" ? "blue" : "yellow"}`}>
                          {p.status}
                        </span>
                      </div>
                    </div>
                    <p style={{ fontSize: 14, color: "#374151", marginBottom: 8 }}>{p.coverLetter}</p>
                    {p.timeline && <p style={{ fontSize: 13, color: "#6b7280", marginBottom: 8 }}>Timeline: {p.timeline}</p>}
                    {p.status === "PENDING" || p.status === "SHORTLISTED" ? (
                      <div style={{ display: "flex", gap: 8 }}>
                        {p.status === "PENDING" && (
                          <button className="btn btn-secondary" style={{ fontSize: 13, padding: "4px 12px" }} onClick={() => respond(p.id, "shortlist")}>
                            Shortlist
                          </button>
                        )}
                        <button className="btn btn-primary" style={{ fontSize: 13, padding: "4px 12px" }} onClick={() => respond(p.id, "accept")}>
                          Accept
                        </button>
                        <button className="btn btn-secondary" style={{ fontSize: 13, padding: "4px 12px", color: "#dc2626" }} onClick={() => respond(p.id, "reject")}>
                          Reject
                        </button>
                      </div>
                    ) : null}
                  </div>
                ))}
              </div>
            )}
          </div>
        )}
      </div>
    </>
  );
}
