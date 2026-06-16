import Link from "next/link";

export default function PublicMarketplacePage() {
  return (
    <>
      <nav>
        <div className="container nav-inner">
          <Link href="/" className="nav-logo">bildfie</Link>
          <div className="nav-links">
            <Link href="/how-it-works">How it works</Link>
            <Link href="/login">Log in</Link>
            <Link href="/signup" className="btn btn-primary">Sign up</Link>
          </div>
        </div>
      </nav>
      <div className="container" style={{ padding: "32px 20px" }}>
        <div className="page-header">
          <h1>Browse professionals</h1>
          <p>Find skilled tradespeople for your project. Sign in to send an offer.</p>
        </div>
        <div className="card" style={{ marginBottom: 24, display: "flex", gap: 12 }}>
          <input placeholder="Search by name or skill…" style={{ flex: 1 }} disabled />
          <button className="btn btn-primary" disabled>Search</button>
        </div>
        <div className="card empty">
          <p>Sign in to browse professionals.</p>
          <Link href="/signup" className="btn btn-primary" style={{ marginTop: 16 }}>Create free account</Link>
        </div>
      </div>
    </>
  );
}
