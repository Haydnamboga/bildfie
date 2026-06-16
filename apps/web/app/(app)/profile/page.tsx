"use client";
import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { AppNav } from "@/components/AppNav";

type Profile = { id: string; email: string; fullName: string; role: string; headline?: string; bio?: string; skills: string[]; hourlyRate?: string; mfaEnabled: boolean };

export default function ProfilePage() {
  const [profile, setProfile] = useState<Profile | null>(null);
  const [form, setForm] = useState({ fullName: "", headline: "", bio: "", skills: "", hourlyRate: "" });
  const [saved, setSaved] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    api.getUser("me").then(r => {
      const p = r as Profile;
      setProfile(p);
      setForm({ fullName: p.fullName, headline: p.headline ?? "", bio: p.bio ?? "", skills: p.skills.join(", "), hourlyRate: p.hourlyRate ?? "" });
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
      });
      setSaved(true);
    } catch (err: unknown) {
      setError((err as { message?: string }).message ?? "Failed");
    }
  }

  if (!profile) return <><AppNav /><div className="container" style={{ padding: 32 }}>Loading…</div></>;

  return (
    <>
      <AppNav />
      <div className="container" style={{ padding: "32px 20px", maxWidth: 640 }}>
        <div className="page-header"><h1>My profile</h1><p>{profile.email} · {profile.role}</p></div>
        <div className="card">
          <form onSubmit={save} style={{ display: "flex", flexDirection: "column", gap: 16 }}>
            <div className="form-group"><label className="label">Full name</label><input value={form.fullName} onChange={e => setForm(f => ({ ...f, fullName: e.target.value }))} /></div>
            <div className="form-group"><label className="label">Headline</label><input value={form.headline} onChange={e => setForm(f => ({ ...f, headline: e.target.value }))} placeholder="e.g. Senior Electrician with 10 years experience" /></div>
            <div className="form-group"><label className="label">Bio</label><textarea value={form.bio} onChange={e => setForm(f => ({ ...f, bio: e.target.value }))} rows={4} placeholder="Tell clients about yourself…" /></div>
            <div className="form-group"><label className="label">Skills (comma-separated)</label><input value={form.skills} onChange={e => setForm(f => ({ ...f, skills: e.target.value }))} placeholder="Plumbing, Electrical, Tiling" /></div>
            <div className="form-group"><label className="label">Hourly rate (KES)</label><input type="number" value={form.hourlyRate} onChange={e => setForm(f => ({ ...f, hourlyRate: e.target.value }))} /></div>
            {error && <p className="error">{error}</p>}
            {saved && <p style={{ color: "#16a34a", fontSize: 14 }}>Profile saved!</p>}
            <button type="submit" className="btn btn-primary">Save changes</button>
          </form>
        </div>
      </div>
    </>
  );
}
