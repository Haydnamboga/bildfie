"use client";

import { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { api } from "@/lib/api";
import { setTokens } from "@/lib/auth";

type RoleType = "client" | "professional";

export default function SignupPage() {
  const router = useRouter();
  const [role, setRole] = useState<RoleType>("client");
  const [form, setForm] = useState({
    fullName: "",
    email: "",
    phone: "",
    password: "",
  });
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    if (form.password.length < 8) {
      setError("Password must be at least 8 characters");
      return;
    }
    setLoading(true);
    try {
      const res = await api.register({
        fullName: form.fullName,
        email: form.email,
        password: form.password,
      });
      setTokens(res.accessToken, res.refreshToken);
      router.push("/dashboard");
    } catch (err: unknown) {
      setError(
        (err as { message?: string }).message ?? "Registration failed"
      );
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
      <h1>Create your account</h1>
      <p>Join bildfie — Africa&rsquo;s construction marketplace.</p>

      {/* Role toggle */}
      <div className="bl-role-toggle" role="group" aria-label="Account type">
        <button
          type="button"
          className={`bl-role-btn${role === "client" ? " active" : ""}`}
          onClick={() => setRole("client")}
          aria-pressed={role === "client"}
        >
          I want to hire
        </button>
        <button
          type="button"
          className={`bl-role-btn${role === "professional" ? " active" : ""}`}
          onClick={() => setRole("professional")}
          aria-pressed={role === "professional"}
        >
          I&rsquo;m a professional
        </button>
      </div>

      {role === "professional" && (
        <div
          style={{
            background: "var(--bl-accent-light)",
            border: "1px solid rgba(196,49,0,.2)",
            borderRadius: 8,
            padding: "10px 14px",
            fontSize: 13,
            color: "var(--bl-accent)",
            marginBottom: 16,
          }}
          role="note"
        >
          After signing up you can complete your professional profile and start bidding on projects.
        </div>
      )}

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

      <div className="bl-or-divider">or sign up with email</div>

      <form onSubmit={onSubmit}>
        <div className="bl-form-group">
          <label htmlFor="fullName" className="bl-label">
            Full name
          </label>
          <input
            id="fullName"
            type="text"
            className="bl-input"
            value={form.fullName}
            onChange={set("fullName")}
            required
            autoComplete="name"
            placeholder="Jane Doe"
          />
        </div>

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
          <label htmlFor="phone" className="bl-label">
            Phone number{" "}
            <span style={{ color: "var(--bl-muted)", fontWeight: 400 }}>
              (optional)
            </span>
          </label>
          <input
            id="phone"
            type="tel"
            className="bl-input"
            value={form.phone}
            onChange={set("phone")}
            autoComplete="tel"
            placeholder="+254 7XX XXX XXX"
          />
        </div>

        <div className="bl-form-group">
          <label htmlFor="password" className="bl-label">
            Password
          </label>
          <div style={{ position: "relative" }}>
            <input
              id="password"
              type={showPassword ? "text" : "password"}
              className="bl-input"
              value={form.password}
              onChange={set("password")}
              required
              autoComplete="new-password"
              minLength={8}
              placeholder="At least 8 characters"
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
          {form.password.length > 0 && form.password.length < 8 && (
            <p className="bl-field-hint">
              {8 - form.password.length} more character
              {8 - form.password.length !== 1 ? "s" : ""} needed
            </p>
          )}
        </div>

        {error && (
          <p
            className="bl-field-error"
            role="alert"
            style={{ marginBottom: 12 }}
          >
            {error}
          </p>
        )}

        <p
          style={{
            fontSize: 11,
            color: "var(--bl-muted)",
            marginBottom: 14,
            lineHeight: 1.5,
          }}
        >
          By creating an account you agree to bildfie&rsquo;s{" "}
          <Link href="/terms" style={{ color: "var(--bl-accent)" }}>
            Terms of Service
          </Link>{" "}
          and{" "}
          <Link href="/privacy" style={{ color: "var(--bl-accent)" }}>
            Privacy Policy
          </Link>
          .
        </p>

        <button
          type="submit"
          className="bl-btn bl-btn-accent bl-btn-full"
          disabled={loading}
          style={{ marginBottom: 16 }}
        >
          {loading
            ? "Creating account…"
            : role === "professional"
            ? "Create professional account"
            : "Create account"}
        </button>
      </form>

      <p
        style={{
          textAlign: "center",
          fontSize: 13,
          color: "var(--bl-muted)",
        }}
      >
        Already have an account?{" "}
        <Link
          href="/login"
          style={{ color: "var(--bl-accent)", fontWeight: 600 }}
        >
          Sign in
        </Link>
      </p>
    </div>
  );
}
