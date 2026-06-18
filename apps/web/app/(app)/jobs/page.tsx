"use client";
import { useEffect, useState } from "react";
import Link from "next/link";
import { api } from "@/lib/api";
import { AppNav } from "@/components/AppNav";

const TRADE_CATEGORIES = [
  "ELECTRICAL", "PLUMBING", "CARPENTRY", "CIVIL", "TILING",
  "PAINTING", "ROOFING", "HVAC", "LANDSCAPING", "MASONRY", "WELDING", "GENERAL",
];

type JobPost = {
  id: string;
  title: string;
  category: string;
  location: string;
  budgetMin?: number;
  budgetMax?: number;
  createdAt: string;
  status: string;
};

export default function JobsPage() {
  const [jobs, setJobs] = useState<JobPost[]>([]);
  const [loading, setLoading] = useState(true);
  const [posting, setPosting] = useState(false);
  const [error, setError] = useState("");

  // Search filters
  const [q, setQ] = useState("");
  const [category, setCategory] = useState("");
  const [location, setLocation] = useState("");

  // Create form
  const [form, setForm] = useState({
    title: "",
    description: "",
    category: "",
    location: "",
    budgetMin: "",
    budgetMax: "",
    dueDate: "",
  });
  const [formError, setFormError] = useState("");

  async function load() {
    setLoading(true);
    try {
      const params: Record<string, string> = {};
      if (q) params.q = q;
      if (category) params.category = category;
      if (location) params.location = location;
      const results = await api.listJobPosts(params) as JobPost[];
      setJobs(results);
    } catch {
      setError("Failed to load jobs.");
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => { load(); }, []); // eslint-disable-line react-hooks/exhaustive-deps

  async function search(e: React.FormEvent) {
    e.preventDefault();
    load();
  }

  async function createJob(e: React.FormEvent) {
    e.preventDefault();
    setFormError("");
    try {
      const body: Record<string, unknown> = {
        title: form.title,
        description: form.description,
        category: form.category,
        location: form.location,
      };
      if (form.budgetMin) body.budgetMin = Number(form.budgetMin);
      if (form.budgetMax) body.budgetMax = Number(form.budgetMax);
      if (form.dueDate) body.dueDate = form.dueDate;
      const job = await api.createJobPost(body as Parameters<typeof api.createJobPost>[0]) as JobPost;
      setJobs(prev => [job, ...prev]);
      setForm({ title: "", description: "", category: "", location: "", budgetMin: "", budgetMax: "", dueDate: "" });
      setPosting(false);
    } catch (err: unknown) {
      setFormError((err as { message?: string }).message ?? "Failed to post job.");
    }
  }

  return (
    <>
      <AppNav />
      <div className="container" style={{ padding: "32px 20px" }}>
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24 }}>
          <div className="page-header" style={{ marginBottom: 0 }}>
            <h1>Job Board</h1>
          </div>
          <button className="btn btn-primary" onClick={() => setPosting(c => !c)}>+ Post a Job</button>
        </div>

        {/* Post a Job Form */}
        {posting && (
          <div className="card" style={{ marginBottom: 24 }}>
            <h2 style={{ fontSize: 16, fontWeight: 600, marginBottom: 16 }}>Post a Job</h2>
            <form onSubmit={createJob} style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              <div className="form-group">
                <label className="label">Title</label>
                <input
                  placeholder="e.g. Electrical wiring for 3-bedroom house"
                  value={form.title}
                  onChange={e => setForm(f => ({ ...f, title: e.target.value }))}
                  required
                />
              </div>
              <div className="form-group">
                <label className="label">Description</label>
                <textarea
                  placeholder="Describe the work needed…"
                  value={form.description}
                  onChange={e => setForm(f => ({ ...f, description: e.target.value }))}
                  rows={3}
                  required
                />
              </div>
              <div style={{ display: "flex", gap: 12 }}>
                <div className="form-group" style={{ flex: 1 }}>
                  <label className="label">Category</label>
                  <select value={form.category} onChange={e => setForm(f => ({ ...f, category: e.target.value }))} required>
                    <option value="">Select category</option>
                    {TRADE_CATEGORIES.map(c => <option key={c} value={c}>{c}</option>)}
                  </select>
                </div>
                <div className="form-group" style={{ flex: 1 }}>
                  <label className="label">Location</label>
                  <input
                    placeholder="e.g. Nairobi, Westlands"
                    value={form.location}
                    onChange={e => setForm(f => ({ ...f, location: e.target.value }))}
                    required
                  />
                </div>
              </div>
              <div style={{ display: "flex", gap: 12 }}>
                <div className="form-group" style={{ flex: 1 }}>
                  <label className="label">Budget Min (KES)</label>
                  <input
                    type="number"
                    placeholder="0"
                    value={form.budgetMin}
                    onChange={e => setForm(f => ({ ...f, budgetMin: e.target.value }))}
                  />
                </div>
                <div className="form-group" style={{ flex: 1 }}>
                  <label className="label">Budget Max (KES)</label>
                  <input
                    type="number"
                    placeholder="0"
                    value={form.budgetMax}
                    onChange={e => setForm(f => ({ ...f, budgetMax: e.target.value }))}
                  />
                </div>
                <div className="form-group" style={{ flex: 1 }}>
                  <label className="label">Due Date (optional)</label>
                  <input
                    type="date"
                    value={form.dueDate}
                    onChange={e => setForm(f => ({ ...f, dueDate: e.target.value }))}
                  />
                </div>
              </div>
              {formError && <p className="error">{formError}</p>}
              <div style={{ display: "flex", gap: 8 }}>
                <button type="submit" className="btn btn-primary">Post Job</button>
                <button type="button" className="btn btn-secondary" onClick={() => setPosting(false)}>Cancel</button>
              </div>
            </form>
          </div>
        )}

        {/* Search */}
        <form onSubmit={search} className="card" style={{ marginBottom: 24 }}>
          <div style={{ display: "flex", gap: 12, flexWrap: "wrap" }}>
            <input
              placeholder="Search jobs…"
              value={q}
              onChange={e => setQ(e.target.value)}
              style={{ flex: 2, minWidth: 160 }}
            />
            <select value={category} onChange={e => setCategory(e.target.value)} style={{ flex: 1, minWidth: 140 }}>
              <option value="">All categories</option>
              {TRADE_CATEGORIES.map(c => <option key={c} value={c}>{c}</option>)}
            </select>
            <input
              placeholder="Location"
              value={location}
              onChange={e => setLocation(e.target.value)}
              style={{ flex: 1, minWidth: 120 }}
            />
            <button type="submit" className="btn btn-primary">Search</button>
          </div>
        </form>

        {/* Job List */}
        {loading ? (
          <div className="card empty"><p>Loading…</p></div>
        ) : error ? (
          <p className="error">{error}</p>
        ) : jobs.length === 0 ? (
          <div className="card empty"><p>No jobs found. Be the first to post one!</p></div>
        ) : (
          <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
            {jobs.map(job => (
              <Link key={job.id} href={`/jobs/${job.id}`} style={{ textDecoration: "none" }}>
                <div className="card" style={{ cursor: "pointer" }}>
                  <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", marginBottom: 8 }}>
                    <h3 style={{ fontWeight: 600, color: "#111827" }}>{job.title}</h3>
                    <span className="badge badge-blue">{job.category}</span>
                  </div>
                  <div style={{ fontSize: 14, color: "#6b7280", display: "flex", gap: 16, flexWrap: "wrap" }}>
                    <span>📍 {job.location}</span>
                    {(job.budgetMin || job.budgetMax) && (
                      <span>
                        KES {job.budgetMin ? Number(job.budgetMin).toLocaleString() : "?"} – {job.budgetMax ? Number(job.budgetMax).toLocaleString() : "?"}
                      </span>
                    )}
                    <span>{new Date(job.createdAt).toLocaleDateString()}</span>
                  </div>
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>
    </>
  );
}
