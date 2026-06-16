import Link from "next/link";

const steps = [
  { n: 1, title: "Create your account", desc: "Sign up once — the same account lets you hire and get hired." },
  { n: 2, title: "Build your profile", desc: "Add your skills, hourly rate, and portfolio to attract clients." },
  { n: 3, title: "Find or receive offers", desc: "Search the marketplace or wait for clients to send you an offer." },
  { n: 4, title: "Manage your project", desc: "Track tasks, milestones, and team members in your project CRM." },
  { n: 5, title: "Get paid securely", desc: "Clients fund milestones upfront; funds release when you deliver." },
];

export default function HowItWorksPage() {
  return (
    <>
      <nav>
        <div className="container nav-inner">
          <Link href="/" className="nav-logo">bildfie</Link>
          <div className="nav-links">
            <Link href="/marketplace">Browse</Link>
            <Link href="/login">Log in</Link>
            <Link href="/signup" className="btn btn-primary">Sign up</Link>
          </div>
        </div>
      </nav>
      <div className="container" style={{ padding: "48px 20px" }}>
        <div className="page-header" style={{ textAlign: "center", marginBottom: 48 }}>
          <h1>How bildfie works</h1>
          <p>A simple flow from hiring to payment.</p>
        </div>
        <div style={{ maxWidth: 640, margin: "0 auto", display: "flex", flexDirection: "column", gap: 24 }}>
          {steps.map((s) => (
            <div key={s.n} className="card" style={{ display: "flex", gap: 20, alignItems: "flex-start" }}>
              <div style={{ width: 40, height: 40, borderRadius: "50%", background: "#2563eb", color: "#fff", display: "flex", alignItems: "center", justifyContent: "center", fontWeight: 700, flexShrink: 0 }}>{s.n}</div>
              <div>
                <h3 style={{ fontWeight: 600, marginBottom: 4 }}>{s.title}</h3>
                <p style={{ color: "#6b7280" }}>{s.desc}</p>
              </div>
            </div>
          ))}
        </div>
        <div style={{ textAlign: "center", marginTop: 48 }}>
          <Link href="/signup" className="btn btn-primary" style={{ fontSize: 16, padding: "12px 28px" }}>Get started</Link>
        </div>
      </div>
    </>
  );
}
