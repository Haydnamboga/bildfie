"use client";
import { useEffect, useState } from "react";
import { api } from "@/lib/api";

const TRADE_CATEGORIES = [
  "ELECTRICAL", "PLUMBING", "CARPENTRY", "CIVIL", "TILING",
  "PAINTING", "ROOFING", "HVAC", "LANDSCAPING", "MASONRY", "WELDING", "GENERAL",
];

type Profile = { id: string; email: string; fullName: string; role: string; headline?: string; bio?: string; skills: string[]; hourlyRate?: string; mfaEnabled: boolean; location?: string; category?: string; proLevel?: string };

export default function ProfilePage() {
  const [profile, setProfile] = useState<Profile | null>(null);
  const [form, setForm] = useState({ fullName: "", headline: "", bio: "", skills: "", hourlyRate: "", location: "", category: "" });
  const [saved, setSaved] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    api.getUser("me").then(r => {
      const p = r as Profile;
      setProfile(p);
      setForm({ fullName: p.fullName, headline: p.headline ?? "", bio: p.bio ?? "", skills: p.skills.join(", "), hourlyRate: p.hourlyRate ?? "", location: p.location ?? "", category: p.category ?? "" });
    }).catch(() => {});
  }, []);

  async function save(e: React.FormEvent) {
    e.preventDefault();
    setError(""); setSaved(false);
    try {
      await api.updateProfile({
        fullName: form.fullName || undefined,
        headline: form.headline || undefined,
        bio: form.bio || undefined,
        skills: form.skills ? form.skills.split(",").map(s => s.trim()).filter(Boolean) : undefined,
        hourlyRate: form.hourlyRate ? Number(form.hourlyRate) : undefined,
        location: form.location || undefined,
        category: form.category || undefined,
      } as Parameters<typeof api.updateProfile>[0]);
      setSaved(true);
    } catch (err: unknown) {
      setError((err as { message?: string }).message ?? "Failed");
    }
  }

  if (!profile) return <><div className="container" style={{ padding: 32 }}>Loading…</div></>;

  return (
    <>
      
      <div className="container" style={{ padding: "32px 20px", maxWidth: 640 }}>
        <div className="page-header">
          <div style={{ display: "flex", alignItems: "center", gap: 12, flexWrap: "wrap" }}>
            <h1>My profile</h1>
            {profile.proLevel && (
              <span className={`badge badge-${profile.proLevel === "TOP_RATED" ? "green" : profile.proLevel === "VERIFIED" ? "blue" : "yellow"}`}>
                {profile.proLevel}
              </span>
            )}
          </div>
          <p>{profile.email} · {profile.role}</p>
        </div>
        <div className="card">
          <form onSubmit={save} style={{ display: "flex", flexDirection: "column", gap: 16 }}>
            <div className="form-group"><label className="label">Full name</label><input value={form.fullName} onChange={e => setForm(f => ({ ...f, fullName: e.target.value }))} /></div>
            <div className="form-group"><label className="label">Headline</label><input value={form.headline} onChange={e => setForm(f => ({ ...f, headline: e.target.value }))} placeholder="e.g. Senior Electrician with 10 years experience" /></div>
            <div className="form-group"><label className="label">Bio</label><textarea value={form.bio} onChange={e => setForm(f => ({ ...f, bio: e.target.value }))} rows={4} placeholder="Tell clients about yourself…" /></div>
            <div className="form-group"><label className="label">Skills (comma-separated)</label><input value={form.skills} onChange={e => setForm(f => ({ ...f, skills: e.target.value }))} placeholder="Plumbing, Electrical, Tiling" /></div>
            <div className="form-group"><label className="label">Hourly rate (KES)</label><input type="number" value={form.hourlyRate} onChange={e => setForm(f => ({ ...f, hourlyRate: e.target.value }))} /></div>
            <div className="form-group"><label className="label">Location</label><input value={form.location} onChange={e => setForm(f => ({ ...f, location: e.target.value }))} placeholder="e.g. Nairobi, Kenya" /></div>
            <div className="form-group">
              <label className="label">Trade Category</label>
              <select value={form.category} onChange={e => setForm(f => ({ ...f, category: e.target.value }))}>
                <option value="">Select category</option>
                {TRADE_CATEGORIES.map(c => <option key={c} value={c}>{c}</option>)}
              </select>
            </div>
            {error && <p className="error">{error}</p>}
            {saved && <p style={{ color: "#16a34a", fontSize: 14 }}>Profile saved!</p>}
            <button type="submit" className="btn btn-primary">Save changes</button>
          </form>
        </div>
      </div>
    </>
  );
}
