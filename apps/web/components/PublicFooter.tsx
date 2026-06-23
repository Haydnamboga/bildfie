import Link from "next/link";

export function PublicFooter() {
  const year = new Date().getFullYear();

  return (
    <footer className="bl-footer">
      <div className="bl-container">
        <div className="bl-footer-grid">
          {/* Col 1 — Brand */}
          <div className="bl-footer-col">
            <div
              style={{
                display: "flex",
                alignItems: "center",
                gap: 8,
                marginBottom: 12,
              }}
            >
              <span
                style={{
                  width: 26,
                  height: 26,
                  background: "var(--bl-accent)",
                  borderRadius: 6,
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                }}
              >
                <svg
                  width="14"
                  height="14"
                  viewBox="0 0 24 24"
                  fill="white"
                  aria-hidden="true"
                >
                  <path d="M12 3L2 10h2v11h16V10h2L12 3zm0 2.18L20 11v9H4v-9l8-5.82zM9 13h6v6H9v-6z" />
                </svg>
              </span>
              <span
                style={{
                  fontFamily: "var(--font-sora, Sora, sans-serif)",
                  fontWeight: 700,
                  fontSize: 17,
                  letterSpacing: "-0.04em",
                  color: "#fff",
                }}
              >
                bildfie
              </span>
            </div>
            <p className="bl-footer-tagline">
              Build Smarter. Connect Better.
            </p>
            <p
              className="bl-footer-tagline"
              style={{ marginTop: 8, fontSize: 12 }}
            >
              Trusted across Kenya, East Africa and beyond.
            </p>
            <div style={{ display: "flex", gap: 10, marginTop: 16 }}>
              {/* Social icons placeholder */}
              {[
                { label: "Twitter", path: "M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z" },
                { label: "LinkedIn", path: "M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z M4 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" },
              ].map((s) => (
                <a
                  key={s.label}
                  href="#"
                  aria-label={s.label}
                  style={{
                    width: 32,
                    height: 32,
                    borderRadius: 8,
                    background: "rgba(255,255,255,.08)",
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                    transition: "background .15s",
                  }}
                >
                  <svg
                    width="14"
                    height="14"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="rgba(255,255,255,.7)"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                  >
                    <path d={s.path} />
                  </svg>
                </a>
              ))}
            </div>
          </div>

          {/* Col 2 — For Clients */}
          <div className="bl-footer-col">
            <h4>For Clients</h4>
            <ul>
              {[
                { label: "Post a Project", href: "/signup" },
                { label: "Find Professionals", href: "/marketplace" },
                { label: "How it works", href: "/how-it-works" },
                { label: "Escrow Payments", href: "/how-it-works#payments" },
                { label: "Hire a Team", href: "/marketplace" },
              ].map((l) => (
                <li key={l.href}>
                  <Link href={l.href}>{l.label}</Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Col 3 — For Professionals */}
          <div className="bl-footer-col">
            <h4>For Professionals</h4>
            <ul>
              {[
                { label: "Create a Profile", href: "/signup" },
                { label: "Browse Projects", href: "/signup" },
                { label: "Submit Proposals", href: "/signup" },
                { label: "Get Verified", href: "/how-it-works#verified" },
                { label: "Manage Invoices", href: "/signup" },
              ].map((l) => (
                <li key={l.label}>
                  <Link href={l.href}>{l.label}</Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Col 4 — Company */}
          <div className="bl-footer-col">
            <h4>Company</h4>
            <ul>
              {[
                { label: "About bildfie", href: "/about" },
                { label: "Blog", href: "/blog" },
                { label: "Careers", href: "/careers" },
                { label: "Contact", href: "/contact" },
                { label: "Press", href: "/press" },
              ].map((l) => (
                <li key={l.label}>
                  <Link href={l.href}>{l.label}</Link>
                </li>
              ))}
            </ul>
          </div>
        </div>
      </div>

      {/* Bottom bar */}
      <div className="bl-footer-bottom">
        <div className="bl-container" style={{ width: "100%" }}>
          <div className="bl-footer-bottom">
            <span>© {year} bildfie. All rights reserved.</span>
            <div style={{ display: "flex", gap: 20 }}>
              <Link href="/privacy">Privacy Policy</Link>
              <Link href="/terms">Terms of Service</Link>
              <Link href="/cookies">Cookies</Link>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}
