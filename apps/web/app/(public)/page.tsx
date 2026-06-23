import Link from "next/link";

const CATEGORIES = [
  "Architect",
  "Civil Engineer",
  "Electrician",
  "Plumber",
  "Carpenter",
  "Painter",
  "Mason",
  "Structural Engineer",
  "Quantity Surveyor",
  "Interior Designer",
];

const TICKER_ITEMS = [
  "500+ verified professionals",
  "1,200+ projects completed",
  "KES 2B+ in transactions",
  "M-Pesa & Stripe payments",
  "Trusted across East Africa",
];

type PlaceholderPro = {
  id: number;
  name: string;
  title: string;
  location: string;
  rating: number;
  reviews: number;
  rate: string;
  initials: string;
  coverColor: string;
  verified: boolean;
  vetted: boolean;
  insured: boolean;
  skills: string[];
};

const PLACEHOLDER_PROS: PlaceholderPro[] = [
  {
    id: 1,
    name: "James Mwangi",
    title: "Structural Engineer",
    location: "Nairobi, Kenya",
    rating: 4.9,
    reviews: 42,
    rate: "KES 8,500/day",
    initials: "JM",
    coverColor: "linear-gradient(135deg, #011D47 0%, #1a3560 100%)",
    verified: true,
    vetted: true,
    insured: true,
    skills: ["Structural Design", "AutoCAD", "Site Supervision"],
  },
  {
    id: 2,
    name: "Amina Hassan",
    title: "Interior Designer",
    location: "Mombasa, Kenya",
    rating: 4.8,
    reviews: 28,
    rate: "KES 6,000/day",
    initials: "AH",
    coverColor: "linear-gradient(135deg, #C43100 0%, #a02800 100%)",
    verified: true,
    vetted: false,
    insured: true,
    skills: ["Space Planning", "3D Rendering", "Procurement"],
  },
  {
    id: 3,
    name: "Peter Kamau",
    title: "Certified Electrician",
    location: "Kisumu, Kenya",
    rating: 4.7,
    reviews: 61,
    rate: "KES 3,500/day",
    initials: "PK",
    coverColor: "linear-gradient(135deg, #D4A017 0%, #b88a12 100%)",
    verified: true,
    vetted: true,
    insured: false,
    skills: ["Solar Installation", "Industrial Wiring", "Panel Boards"],
  },
];

type PlaceholderProject = {
  id: number;
  title: string;
  budget: string;
  location: string;
  category: string;
  bids: number;
  daysLeft: number;
  description: string;
};

const PLACEHOLDER_PROJECTS: PlaceholderProject[] = [
  {
    id: 1,
    title: "3-bedroom house construction",
    budget: "KES 4,500,000",
    location: "Karen, Nairobi",
    category: "Civil Engineer",
    bids: 7,
    daysLeft: 5,
    description:
      "Looking for a qualified civil engineer to oversee the construction of a 3-bedroom bungalow on a 50×100 plot.",
  },
  {
    id: 2,
    title: "Office electrical rewiring",
    budget: "KES 180,000",
    location: "Westlands, Nairobi",
    category: "Electrician",
    bids: 12,
    daysLeft: 2,
    description:
      "Full electrical rewiring of a 3-floor office block. Must comply with KeBS standards. Previous quotes available.",
  },
  {
    id: 3,
    title: "Luxury apartment interior fit-out",
    budget: "KES 1,200,000",
    location: "Kilimani, Nairobi",
    category: "Interior Designer",
    bids: 4,
    daysLeft: 9,
    description:
      "High-end interior design and fit-out for a 2BR luxury apartment. Looking for a designer with a modern aesthetic.",
  },
];

function StarRating({ rating }: { rating: number }) {
  return (
    <div className="bl-pro-rating">
      {[1, 2, 3, 4, 5].map((i) => (
        <span
          key={i}
          className={i <= Math.round(rating) ? "bl-star" : "bl-star-empty"}
        >
          ★
        </span>
      ))}
      <span style={{ color: "var(--bl-muted)", fontSize: 11 }}>
        {rating.toFixed(1)}
      </span>
    </div>
  );
}

function ProCard({ pro }: { pro: PlaceholderPro }) {
  return (
    <div className="bl-pro-card">
      <div className="bl-pro-card-cover" style={{ background: pro.coverColor }}>
        <div className="bl-pro-card-cover-overlay" />
        <div className="bl-pro-avatar">{pro.initials}</div>
      </div>
      <div className="bl-pro-card-body">
        <div className="bl-pro-name">{pro.name}</div>
        <div className="bl-pro-title">{pro.title}</div>
        <div className="bl-pro-location">
          <svg
            width="11"
            height="11"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            aria-hidden="true"
          >
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
            <circle cx="12" cy="10" r="3" />
          </svg>
          {pro.location}
        </div>
        <StarRating rating={pro.rating} />
        <span
          style={{ fontSize: 11, color: "var(--bl-muted)", marginLeft: 2 }}
        >
          ({pro.reviews} reviews)
        </span>

        <div style={{ marginTop: 10 }}>
          <span className="bl-pro-rate">{pro.rate}</span>
        </div>

        <div className="bl-trust-badges">
          {pro.verified && (
            <span className="bl-trust-badge bl-trust-verified">
              NCA Verified
            </span>
          )}
          {pro.vetted && (
            <span className="bl-trust-badge bl-trust-vetted">
              bildfie Vetted
            </span>
          )}
          {pro.insured && (
            <span className="bl-trust-badge bl-trust-insured">Insured</span>
          )}
        </div>

        <div className="bl-pro-skills">
          {pro.skills.map((s) => (
            <span key={s} className="bl-skill-tag">
              {s}
            </span>
          ))}
        </div>

        <div style={{ marginTop: 14 }}>
          <Link
            href="/signup"
            className="bl-btn bl-btn-dark bl-btn-sm"
            style={{ width: "100%", justifyContent: "center" }}
          >
            View Profile
          </Link>
        </div>
      </div>
    </div>
  );
}

function ProjectCard({ project }: { project: PlaceholderProject }) {
  return (
    <div className="bl-card">
      <div className="bl-card-body">
        <div
          style={{
            display: "flex",
            alignItems: "flex-start",
            justifyContent: "space-between",
            gap: 8,
            marginBottom: 10,
          }}
        >
          <h3
            style={{
              fontFamily: "var(--font-sora, Sora, sans-serif)",
              fontSize: 15,
              fontWeight: 600,
              color: "var(--bl-navy)",
              lineHeight: 1.3,
            }}
          >
            {project.title}
          </h3>
          <span className="bl-badge bl-badge-open" style={{ flexShrink: 0 }}>
            Open
          </span>
        </div>

        <p
          style={{
            fontSize: 13,
            color: "var(--bl-muted)",
            marginBottom: 14,
            lineHeight: 1.6,
          }}
        >
          {project.description}
        </p>

        <div
          style={{
            display: "flex",
            flexWrap: "wrap",
            gap: 12,
            fontSize: 12,
            color: "var(--bl-muted)",
            marginBottom: 14,
          }}
        >
          <span style={{ display: "flex", alignItems: "center", gap: 4 }}>
            <svg
              width="11"
              height="11"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              aria-hidden="true"
            >
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            {project.location}
          </span>
          <span style={{ display: "flex", alignItems: "center", gap: 4 }}>
            <svg
              width="11"
              height="11"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              aria-hidden="true"
            >
              <rect x="2" y="3" width="20" height="14" rx="2" />
              <path d="M8 21h8M12 17v4" />
            </svg>
            {project.category}
          </span>
          <span style={{ display: "flex", alignItems: "center", gap: 4 }}>
            <svg
              width="11"
              height="11"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              aria-hidden="true"
            >
              <circle cx="12" cy="12" r="10" />
              <path d="M12 6v6l4 2" />
            </svg>
            {project.daysLeft}d left
          </span>
        </div>

        <div
          style={{
            display: "flex",
            alignItems: "center",
            justifyContent: "space-between",
          }}
        >
          <div>
            <div
              style={{
                fontSize: 16,
                fontWeight: 700,
                color: "var(--bl-navy)",
              }}
            >
              {project.budget}
            </div>
            <div style={{ fontSize: 11, color: "var(--bl-muted)" }}>
              {project.bids} bids
            </div>
          </div>
          <Link href="/signup" className="bl-btn bl-btn-accent bl-btn-sm">
            Place Bid
          </Link>
        </div>
      </div>
    </div>
  );
}

export default function HomePage() {
  return (
    <>
      {/* ── Hero ──────────────────────────────────────────── */}
      <section className="bl-hero">
        <div className="bl-container" style={{ position: "relative", zIndex: 1 }}>
          <div style={{ maxWidth: 660 }}>
            <h1 className="bl-hero-headline">
              Find and hire verified
              <br />
              construction professionals.
            </h1>
            <p className="bl-hero-sub">
              <em style={{ fontStyle: "italic", color: "var(--bl-muted)" }}>
                Post a project. Contractors bid. Pay when work is approved.
              </em>
            </p>

            {/* Search bar */}
            <div className="bl-search-bar">
              <input
                type="text"
                placeholder="Search for a professional or skill…"
                aria-label="Search professionals"
              />
              <select aria-label="Select trade">
                <option value="">All Trades</option>
                {CATEGORIES.map((c) => (
                  <option key={c} value={c}>
                    {c}
                  </option>
                ))}
              </select>
              <Link href="/marketplace" className="bl-btn bl-btn-dark">
                Search
              </Link>
            </div>

            {/* Category pills */}
            <div className="bl-pills">
              {CATEGORIES.map((cat) => (
                <Link
                  key={cat}
                  href={`/marketplace?trade=${encodeURIComponent(cat)}`}
                  className="bl-pill"
                >
                  {cat}
                </Link>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* ── Trust ticker ──────────────────────────────────── */}
      <div className="bl-ticker">
        <div className="bl-container">
          <div className="bl-ticker-inner">
            {TICKER_ITEMS.map((item, i) => (
              <span key={item} style={{ display: "flex", alignItems: "center", gap: 32 }}>
                {i > 0 && <span className="bl-ticker-sep">·</span>}
                {item}
              </span>
            ))}
          </div>
        </div>
      </div>

      {/* ── Featured professionals ─────────────────────────── */}
      <section className="bl-section">
        <div className="bl-container">
          <div
            style={{
              display: "flex",
              alignItems: "flex-end",
              justifyContent: "space-between",
              marginBottom: 32,
              gap: 16,
            }}
          >
            <div>
              <h2 className="bl-section-title">Featured Professionals</h2>
              <p className="bl-section-sub">
                Handpicked, NCA-verified construction experts ready to work on
                your project.
              </p>
            </div>
            <Link
              href="/marketplace"
              className="bl-btn bl-btn-outline"
              style={{ flexShrink: 0 }}
            >
              Browse all →
            </Link>
          </div>

          <div className="bl-grid-3">
            {PLACEHOLDER_PROS.map((pro) => (
              <ProCard key={pro.id} pro={pro} />
            ))}
          </div>
        </div>
      </section>

      {/* ── Open projects ─────────────────────────────────── */}
      <section className="bl-section" style={{ background: "var(--bl-surface2)", padding: "64px 0" }}>
        <div className="bl-container">
          <div
            style={{
              display: "flex",
              alignItems: "flex-end",
              justifyContent: "space-between",
              marginBottom: 32,
              gap: 16,
            }}
          >
            <div>
              <h2 className="bl-section-title">Open Projects</h2>
              <p className="bl-section-sub">
                Browse live projects and submit your proposal today.
              </p>
            </div>
            <Link
              href="/signup"
              className="bl-btn bl-btn-outline"
              style={{ flexShrink: 0 }}
            >
              Post a project →
            </Link>
          </div>

          <div className="bl-grid-3">
            {PLACEHOLDER_PROJECTS.map((project) => (
              <ProjectCard key={project.id} project={project} />
            ))}
          </div>
        </div>
      </section>

      {/* ── How it works ──────────────────────────────────── */}
      <section className="bl-section">
        <div className="bl-container">
          <div style={{ textAlign: "center", marginBottom: 48 }}>
            <h2 className="bl-section-title">How bildfie works</h2>
            <p className="bl-section-sub" style={{ margin: "0 auto" }}>
              From posting to payment — all in one place.
            </p>
          </div>

          <div
            style={{
              display: "grid",
              gridTemplateColumns: "repeat(auto-fit, minmax(240px, 1fr))",
              gap: 32,
            }}
          >
            {[
              {
                num: "1",
                title: "Post a Project",
                desc: "Describe your construction project, set your budget, and publish it to our network of verified professionals.",
              },
              {
                num: "2",
                title: "Get Bids",
                desc: "Qualified contractors review your project and submit competitive proposals. Compare profiles, ratings, and pricing.",
              },
              {
                num: "3",
                title: "Hire & Pay Securely",
                desc: "Select the best professional, agree on milestones, and pay through our escrow system — only release funds when satisfied.",
              },
            ].map((step) => (
              <div key={step.num} className="bl-step">
                <div className="bl-step-num">{step.num}</div>
                <div className="bl-step-title">{step.title}</div>
                <div className="bl-step-desc">{step.desc}</div>
              </div>
            ))}
          </div>

          <div
            style={{
              display: "flex",
              justifyContent: "center",
              gap: 12,
              marginTop: 48,
            }}
          >
            <Link href="/signup" className="bl-btn bl-btn-accent bl-btn-lg">
              Get started free
            </Link>
            <Link href="/how-it-works" className="bl-btn bl-btn-outline bl-btn-lg">
              Learn more
            </Link>
          </div>
        </div>
      </section>

      {/* ── CTA banner ────────────────────────────────────── */}
      <section
        style={{
          background: "var(--bl-navy)",
          padding: "56px 0",
          textAlign: "center",
        }}
      >
        <div className="bl-container">
          <h2
            style={{
              fontFamily: "Playfair Display, Georgia, serif",
              fontSize: "2rem",
              fontWeight: 800,
              color: "#fff",
              marginBottom: 12,
            }}
          >
            Ready to build something great?
          </h2>
          <p
            style={{
              fontSize: 16,
              color: "rgba(255,255,255,.7)",
              marginBottom: 32,
            }}
          >
            Join thousands of clients and professionals on bildfie.
          </p>
          <div style={{ display: "flex", gap: 12, justifyContent: "center" }}>
            <Link href="/signup" className="bl-btn bl-btn-accent bl-btn-lg">
              Post a Project
            </Link>
            <Link
              href="/marketplace"
              className="bl-btn bl-btn-lg"
              style={{
                border: "1.5px solid rgba(255,255,255,.4)",
                color: "#fff",
                background: "transparent",
              }}
            >
              Browse Professionals
            </Link>
          </div>
        </div>
      </section>
    </>
  );
}
