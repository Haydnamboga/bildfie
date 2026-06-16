"use client";
import { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { api } from "@/lib/api";
import { setTokens } from "@/lib/auth";

export default function LoginPage() {
  const router = useRouter();
  const [form, setForm] = useState({ email: "", password: "", mfaToken: "" });
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const [needsMfa, setNeedsMfa] = useState(false);

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    setLoading(true);
    try {
      const res = await api.login({ email: form.email, password: form.password, ...(needsMfa ? { mfaToken: form.mfaToken } : {}) });
      setTokens(res.accessToken, res.refreshToken);
      const role = res.user.role;
      if (role === "SUPER_ADMIN") router.push("/super-admin/system");
      else if (role === "ADMIN") router.push("/back-office/users");
      else router.push("/dashboard");
    } catch (err: unknown) {
      const msg = (err as { message?: string }).message ?? "Login failed";
      if (msg.toLowerCase().includes("mfa")) setNeedsMfa(true);
      setError(msg);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="auth-page">
      <div className="auth-card">
        <h1>Welcome back</h1>
        <p>Log in to your bildfie account.</p>
        <form onSubmit={onSubmit} style={{ display: "flex", flexDirection: "column", gap: 16 }}>
          <div className="form-group">
            <label className="label">Email</label>
            <input type="email" value={form.email} onChange={e => setForm(f => ({ ...f, email: e.target.value }))} required autoComplete="email" />
          </div>
          <div className="form-group">
            <label className="label">Password</label>
            <input type="password" value={form.password} onChange={e => setForm(f => ({ ...f, password: e.target.value }))} required autoComplete="current-password" />
          </div>
          {needsMfa && (
            <div className="form-group">
              <label className="label">MFA code</label>
              <input type="text" inputMode="numeric" maxLength={6} value={form.mfaToken} onChange={e => setForm(f => ({ ...f, mfaToken: e.target.value }))} placeholder="6-digit code" />
            </div>
          )}
          {error && <p className="error">{error}</p>}
          <button type="submit" className="btn btn-primary" disabled={loading} style={{ marginTop: 4 }}>
            {loading ? "Logging in…" : "Log in"}
          </button>
        </form>
        <p style={{ marginTop: 20, textAlign: "center", fontSize: 14, color: "#6b7280" }}>
          No account? <Link href="/signup">Sign up</Link>
        </p>
        <p style={{ textAlign: "center", fontSize: 14, color: "#6b7280", marginTop: 4 }}>
          <Link href="/reset">Forgot password?</Link>
        </p>
      </div>
    </div>
  );
}
