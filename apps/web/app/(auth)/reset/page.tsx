"use client";
import { useState } from "react";
import Link from "next/link";

export default function ResetPage() {
  const [email, setEmail] = useState("");
  const [sent, setSent] = useState(false);

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    // TODO: call api.requestPasswordReset(email) when endpoint exists
    setSent(true);
  }

  return (
    <div className="auth-page">
      <div className="auth-card">
        <h1>Reset password</h1>
        <p>Enter your email and we{"'"}ll send a reset link.</p>
        {sent ? (
          <div className="card" style={{ background: "#f0fdf4", border: "1px solid #bbf7d0", marginTop: 16 }}>
            <p style={{ color: "#166534" }}>If an account exists for <strong>{email}</strong>, a reset link has been sent.</p>
          </div>
        ) : (
          <form onSubmit={onSubmit} style={{ display: "flex", flexDirection: "column", gap: 16 }}>
            <div className="form-group">
              <label className="label">Email</label>
              <input type="email" value={email} onChange={e => setEmail(e.target.value)} required />
            </div>
            <button type="submit" className="btn btn-primary">Send reset link</button>
          </form>
        )}
        <p style={{ marginTop: 20, textAlign: "center", fontSize: 14, color: "#6b7280" }}>
          <Link href="/login">← Back to login</Link>
        </p>
      </div>
    </div>
  );
}
