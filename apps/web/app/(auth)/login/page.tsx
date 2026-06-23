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
  const [showPassword, setShowPassword] = useState(false);

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    setLoading(true);
    try {
      const res = await api.login({
        email: form.email,
        password: form.password,
        ...(needsMfa ? { mfaToken: form.mfaToken } : {}),
      });
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

  function set(field: keyof typeof form) {
    return (e: React.ChangeEvent<HTMLInputElement>) =>
      setForm((f) => ({ ...f, [field]: e.target.value }));
  }

  return (
    <div className="bl-auth-card">
      <h1>Welcome back</h1>
      <p>Sign in to your bildfie account.</p>

      {/* Google placeholder */}
      <button type="button" className="bl-social-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
          <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
          <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
          <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        Continue with Google
      </button>

      <div className="bl-or-divider">or continue with email</div>

      <form onSubmit={onSubmit}>
        <div className="bl-form-group">
          <label htmlFor="email" className="bl-label">
            Email address
          </label>
          <input
            id="email"
            type="email"
            className="bl-input"
            value={form.email}
            onChange={set("email")}
            required
            autoComplete="email"
            placeholder="you@example.com"
          />
        </div>

        <div className="bl-form-group">
          <div
            style={{
              display: "flex",
              alignItems: "center",
              justifyContent: "space-between",
            }}
          >
            <label htmlFor="password" className="bl-label">
              Password
            </label>
            <Link
              href="/reset"
              style={{
                fontSize: 12,
                color: "var(--bl-accent)",
              }}
            >
              Forgot password?
            </Link>
          </div>
          <div style={{ position: "relative" }}>
            <input
              id="password"
              type={showPassword ? "text" : "password"}
              className="bl-input"
              value={form.password}
              onChange={set("password")}
              required
              autoComplete="current-password"
              placeholder="••••••••"
              style={{ paddingRight: 44 }}
            />
            <button
              type="button"
              onClick={() => setShowPassword((v) => !v)}
              style={{
                position: "absolute",
                right: 12,
                top: "50%",
                transform: "translateY(-50%)",
                color: "var(--bl-muted)",
                fontSize: 12,
                fontWeight: 500,
              }}
              aria-label={showPassword ? "Hide password" : "Show password"}
            >
              {showPassword ? "Hide" : "Show"}
            </button>
          </div>
        </div>

        {needsMfa && (
          <div className="bl-form-group">
            <label htmlFor="mfa" className="bl-label">
              Two-factor code
            </label>
            <input
              id="mfa"
              type="text"
              inputMode="numeric"
              maxLength={6}
              className="bl-input"
              value={form.mfaToken}
              onChange={set("mfaToken")}
              placeholder="6-digit code"
              autoComplete="one-time-code"
            />
          </div>
        )}

        {error && (
          <p
            className="bl-field-error"
            role="alert"
            style={{ marginBottom: 12 }}
          >
            {error}
          </p>
        )}

        <button
          type="submit"
          className="bl-btn bl-btn-accent bl-btn-full"
          disabled={loading}
          style={{ marginBottom: 16 }}
        >
          {loading ? "Signing in…" : "Sign in"}
        </button>
      </form>

      <p
        style={{
          textAlign: "center",
          fontSize: 13,
          color: "var(--bl-muted)",
        }}
      >
        Don&rsquo;t have an account?{" "}
        <Link
          href="/signup"
          style={{ color: "var(--bl-accent)", fontWeight: 600 }}
        >
          Join free
        </Link>
      </p>
    </div>
  );
}
