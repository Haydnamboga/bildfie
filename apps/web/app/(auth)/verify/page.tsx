"use client";
import { Suspense, useEffect, useState } from "react";
import Link from "next/link";
import { useSearchParams } from "next/navigation";

function VerifyContent() {
  const params = useSearchParams();
  const token = params.get("token");
  const [status, setStatus] = useState<"pending" | "ok" | "error">("pending");

  useEffect(() => {
    if (!token) { setStatus("error"); return; }
    setStatus("ok");
  }, [token]);

  return (
    <div className="auth-page">
      <div className="auth-card" style={{ textAlign: "center" }}>
        {status === "pending" && <p>Verifying…</p>}
        {status === "ok" && (
          <>
            <h1 style={{ color: "#16a34a" }}>Email verified!</h1>
            <p style={{ marginTop: 8 }}>Your account is confirmed.</p>
            <Link href="/login" className="btn btn-primary" style={{ marginTop: 20 }}>Log in</Link>
          </>
        )}
        {status === "error" && (
          <>
            <h1 style={{ color: "#dc2626" }}>Invalid link</h1>
            <p style={{ marginTop: 8 }}>This verification link is invalid or expired.</p>
            <Link href="/login" className="btn btn-secondary" style={{ marginTop: 20 }}>Go to login</Link>
          </>
        )}
      </div>
    </div>
  );
}

export default function VerifyPage() {
  return (
    <Suspense fallback={<div className="auth-page"><div className="auth-card"><p>Loading…</p></div></div>}>
      <VerifyContent />
    </Suspense>
  );
}
