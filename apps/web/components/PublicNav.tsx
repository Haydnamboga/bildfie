"use client";

import { useState } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";

export function PublicNav() {
  const [menuOpen, setMenuOpen] = useState(false);
  const pathname = usePathname();

  function isActive(href: string) {
    return pathname === href || pathname.startsWith(href + "/");
  }

  return (
    <>
      <nav className="bl-navbar">
        <div className="bl-navbar-inner">
          {/* Brand */}
          <Link href="/" className="bl-brand">
            <span className="bl-brand-icon" aria-hidden="true">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z" opacity=".3"/>
                <path d="M12 3L2 10h2v11h16V10h2L12 3zm0 2.18L20 11v9H4v-9l8-5.82zM9 13h6v6H9v-6z"/>
              </svg>
            </span>
            bildfie
          </Link>

          {/* Desktop nav links */}
          <div className="bl-nav-links">
            <Link
              href="/marketplace"
              className={`bl-nav-link${isActive("/marketplace") ? " active" : ""}`}
            >
              Find Professionals
            </Link>
            <Link
              href="/how-it-works"
              className={`bl-nav-link${isActive("/how-it-works") ? " active" : ""}`}
            >
              How it works
            </Link>
          </div>

          {/* Desktop actions */}
          <div className="bl-nav-actions">
            <Link href="/login" className="bl-btn bl-btn-outline bl-btn-sm">
              Sign in
            </Link>
            <Link href="/signup" className="bl-btn bl-btn-accent bl-btn-sm">
              Post a Project
            </Link>

            {/* Mobile hamburger */}
            <button
              className="bl-hamburger"
              aria-label="Toggle menu"
              aria-expanded={menuOpen}
              onClick={() => setMenuOpen((v) => !v)}
            >
              <span
                style={
                  menuOpen
                    ? { transform: "rotate(45deg) translate(5px, 5px)" }
                    : undefined
                }
              />
              <span style={menuOpen ? { opacity: 0 } : undefined} />
              <span
                style={
                  menuOpen
                    ? { transform: "rotate(-45deg) translate(5px, -5px)" }
                    : undefined
                }
              />
            </button>
          </div>
        </div>
      </nav>

      {/* Mobile drawer */}
      {menuOpen && (
        <div
          style={{
            position: "fixed",
            top: "var(--bl-nav-h)",
            left: 0,
            right: 0,
            background: "var(--bl-surface)",
            borderBottom: "1px solid var(--bl-rule)",
            padding: "16px 20px 20px",
            zIndex: 49,
            display: "flex",
            flexDirection: "column",
            gap: "4px",
            boxShadow: "var(--bl-shadow-hover)",
          }}
        >
          <Link
            href="/marketplace"
            className="bl-nav-link"
            onClick={() => setMenuOpen(false)}
          >
            Find Professionals
          </Link>
          <Link
            href="/how-it-works"
            className="bl-nav-link"
            onClick={() => setMenuOpen(false)}
          >
            How it works
          </Link>
          <div
            style={{
              height: 1,
              background: "var(--bl-rule)",
              margin: "8px 0",
            }}
          />
          <Link
            href="/login"
            className="bl-btn bl-btn-outline"
            style={{ marginBottom: 8 }}
            onClick={() => setMenuOpen(false)}
          >
            Sign in
          </Link>
          <Link
            href="/signup"
            className="bl-btn bl-btn-accent"
            onClick={() => setMenuOpen(false)}
          >
            Post a Project
          </Link>
        </div>
      )}
    </>
  );
}
