import Link from "next/link";

export default function HomePage() {
  return (
    <>
      <nav>
        <div className="container nav-inner">
          <span className="nav-logo">bildfie</span>
          <div className="nav-links">
            <Link href="/marketplace">Browse</Link>
            <Link href="/how-it-works">How it works</Link>
            <Link href="/login">Log in</Link>
            <Link href="/signup" className="btn btn-primary">Sign up</Link>
          </div>
        </div>
      </nav>

      <section style={{ background: "linear-gradient(135deg,#1e3a8a,#2563eb)", color: "#fff", padding: "80px 0", textAlign: "center" }}>
        <div className="container">
          <h1 style={{ fontSize: 48, fontWeight: 800, lineHeight: 1.2, marginBottom: 16 }}>
            Find skilled professionals.<br />Manage your projects.
          </h1>
          <p style={{ fontSize: 20, opacity: 0.85, marginBottom: 32 }}>
            One platform to hire, collaborate, and get paid — for construction & trades.
          </p>
          <div style={{ display: "flex", gap: 12, justifyContent: "center" }}>
            <Link href="/signup" className="btn btn-primary" style={{ background: "#fff", color: "#1e3a8a", fontSize: 16, padding: "12px 28px" }}>Get started free</Link>
            <Link href="/marketplace" className="btn btn-secondary" style={{ borderColor: "rgba(255,255,255,.4)", color: "#fff", background: "transparent", fontSize: 16, padding: "12px 28px" }}>Browse professionals</Link>
          </div>
        </div>
      </section>

      <section style={{ padding: "64px 0" }}>
        <div className="container">
          <h2 style={{ textAlign: "center", fontSize: 28, fontWeight: 700, marginBottom: 40 }}>Everything in one place</h2>
          <div className="grid-3">
            {[
              { title: "Marketplace", desc: "Post your skills or find the right professional for your project." },
              { title: "Project CRM", desc: "Manage tasks, milestones, and your team from a single dashboard." },
              { title: "Secure Payments", desc: "Pay through M-Pesa or Stripe — funds held in escrow until work is done." },
            ].map((f) => (
              <div key={f.title} className="card" style={{ textAlign: "center" }}>
                <h3 style={{ fontWeight: 600, marginBottom: 8 }}>{f.title}</h3>
                <p style={{ color: "#6b7280", fontSize: 15 }}>{f.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <footer style={{ borderTop: "1px solid #e5e7eb", padding: "24px 0", textAlign: "center", color: "#9ca3af", fontSize: 14 }}>
        © {new Date().getFullYear()} bildfie.com
      </footer>
    </>
  );
}
