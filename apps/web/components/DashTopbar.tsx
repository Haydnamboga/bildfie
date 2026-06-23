"use client";

import { useState, useRef, useEffect } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { clearTokens, getCurrentUser, getRefreshToken } from "@/lib/auth";
import { api } from "@/lib/api";

interface DashTopbarProps {
  title?: string;
  onMenuToggle: () => void;
}

export function DashTopbar({ title = "Dashboard", onMenuToggle }: DashTopbarProps) {
  const router = useRouter();
  const user = getCurrentUser();
  const [dropdownOpen, setDropdownOpen] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);

  const initials = user
    ? user.fullName
        .split(" ")
        .slice(0, 2)
        .map((n: string) => n[0])
        .join("")
        .toUpperCase()
    : "?";

  // Close dropdown when clicking outside
  useEffect(() => {
    function handleClickOutside(e: MouseEvent) {
      if (
        dropdownRef.current &&
        !dropdownRef.current.contains(e.target as Node)
      ) {
        setDropdownOpen(false);
      }
    }
    if (dropdownOpen) {
      document.addEventListener("mousedown", handleClickOutside);
    }
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, [dropdownOpen]);

  async function handleLogout() {
    setDropdownOpen(false);
    try {
      const rt = getRefreshToken();
      if (rt) await api.logout(rt);
    } catch {
      // ignore
    }
    clearTokens();
    router.push("/login");
  }

  return (
    <header className="bl-topbar">
      {/* Hamburger — mobile only */}
      <button
        onClick={onMenuToggle}
        className="bl-nav-icon"
        aria-label="Toggle sidebar"
        style={{ flexShrink: 0 }}
      >
        <svg
          width="18"
          height="18"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeWidth="2"
          strokeLinecap="round"
          aria-hidden="true"
        >
          <line x1="3" y1="6" x2="21" y2="6" />
          <line x1="3" y1="12" x2="21" y2="12" />
          <line x1="3" y1="18" x2="21" y2="18" />
        </svg>
      </button>

      {/* Page title */}
      <div className="bl-topbar-title">{title}</div>

      {/* Actions */}
      <div className="bl-topbar-actions">
        {/* Notifications */}
        <button className="bl-nav-icon" aria-label="Notifications">
          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
          >
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9 M13.73 21a2 2 0 0 1-3.46 0" />
          </svg>
          <span className="bl-nav-badge" aria-hidden="true" />
        </button>

        {/* Messages */}
        <button className="bl-nav-icon" aria-label="Messages">
          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
          >
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
          </svg>
        </button>

        {/* Post a Project CTA */}
        <Link href="/projects/new" className="bl-btn bl-btn-accent bl-btn-sm">
          Post a Project
        </Link>

        {/* Avatar dropdown */}
        <div className="bl-dropdown" ref={dropdownRef}>
          <button
            className="bl-avatar-btn"
            onClick={() => setDropdownOpen((v) => !v)}
            aria-label="User menu"
            aria-expanded={dropdownOpen}
            aria-haspopup="true"
          >
            {initials}
          </button>

          {dropdownOpen && (
            <div
              className="bl-dropdown-menu"
              role="menu"
              aria-label="User options"
            >
              {user && (
                <>
                  <div style={{ padding: "8px 12px 10px" }}>
                    <div
                      style={{
                        fontSize: 13,
                        fontWeight: 600,
                        color: "var(--bl-navy)",
                      }}
                    >
                      {user.fullName}
                    </div>
                    <div style={{ fontSize: 12, color: "var(--bl-muted)" }}>
                      {user.email}
                    </div>
                  </div>
                  <div className="bl-dropdown-divider" />
                </>
              )}

              <Link
                href="/profile"
                className="bl-dropdown-item"
                role="menuitem"
                onClick={() => setDropdownOpen(false)}
              >
                <svg
                  width="14"
                  height="14"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  aria-hidden="true"
                >
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                  <circle cx="12" cy="7" r="4" />
                </svg>
                Profile
              </Link>

              <Link
                href="/settings"
                className="bl-dropdown-item"
                role="menuitem"
                onClick={() => setDropdownOpen(false)}
              >
                <svg
                  width="14"
                  height="14"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  aria-hidden="true"
                >
                  <circle cx="12" cy="12" r="3" />
                  <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
                </svg>
                Settings
              </Link>

              <div className="bl-dropdown-divider" />

              <button
                className="bl-dropdown-item danger"
                role="menuitem"
                onClick={handleLogout}
              >
                <svg
                  width="14"
                  height="14"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  aria-hidden="true"
                >
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4 M16 17l5-5-5-5 M21 12H9" />
                </svg>
                Sign out
              </button>
            </div>
          )}
        </div>
      </div>
    </header>
  );
}
