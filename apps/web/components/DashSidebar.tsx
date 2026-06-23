"use client";

import { useState, useCallback } from "react";
import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import { clearTokens, getCurrentUser, getRefreshToken } from "@/lib/auth";
import { api } from "@/lib/api";

type NavItem = {
  label: string;
  href: string;
  icon?: React.ReactNode;
};

type NavGroup = {
  id: string;
  label: string;
  icon: React.ReactNode;
  items: NavItem[];
};

// SVG icon helpers
function Icon({ d, size = 16 }: { d: string; size?: number }) {
  return (
    <svg
      width={size}
      height={size}
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
      aria-hidden="true"
    >
      <path d={d} />
    </svg>
  );
}

const NAV_GROUPS: NavGroup[] = [
  {
    id: "projects",
    label: "Projects",
    icon: <Icon d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z M9 22V12h6v10" />,
    items: [
      { label: "Projects", href: "/projects" },
      { label: "Tasks", href: "/projects/tasks" },
      { label: "Milestones", href: "/projects/milestones" },
      { label: "Timelines", href: "/projects/timelines" },
      { label: "Kanban", href: "/projects/kanban" },
      { label: "Teams", href: "/projects/teams" },
    ],
  },
  {
    id: "sales",
    label: "Sales",
    icon: <Icon d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />,
    items: [
      { label: "Orders", href: "/orders" },
      { label: "Proposals", href: "/proposals" },
      { label: "Estimates", href: "/estimates" },
      { label: "Contracts", href: "/contracts" },
      { label: "Invoices", href: "/invoices" },
      { label: "Credit Notes", href: "/credit-notes" },
      { label: "Subscriptions", href: "/subscriptions" },
    ],
  },
  {
    id: "utilities",
    label: "Utilities",
    icon: <Icon d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />,
    items: [
      { label: "Files & Documents", href: "/files" },
      { label: "Media", href: "/media" },
      { label: "Calendar", href: "/calendar" },
    ],
  },
  {
    id: "reports",
    label: "Reports & Analytics",
    icon: <Icon d="M18 20V10 M12 20V4 M6 20v-6" />,
    items: [
      { label: "Executive Dashboard", href: "/reports/executive" },
      { label: "Sales Reports", href: "/reports/sales" },
      { label: "Financial", href: "/reports/financial" },
      { label: "Project Reports", href: "/reports/projects" },
      { label: "Team Reports", href: "/reports/team" },
      { label: "Marketing", href: "/reports/marketing" },
    ],
  },
];

interface DashSidebarProps {
  open: boolean;
  onClose: () => void;
}

export function DashSidebar({ open, onClose }: DashSidebarProps) {
  const pathname = usePathname();
  const router = useRouter();
  const user = getCurrentUser();

  // Determine which group should be open by default based on the current path
  function defaultOpenGroup(): string | null {
    for (const group of NAV_GROUPS) {
      if (group.items.some((item) => pathname.startsWith(item.href))) {
        return group.id;
      }
    }
    return null;
  }

  const [openGroup, setOpenGroup] = useState<string | null>(defaultOpenGroup);

  function toggleGroup(id: string) {
    setOpenGroup((current) => (current === id ? null : id));
  }

  function isActive(href: string) {
    return pathname === href || pathname.startsWith(href + "/");
  }

  const handleLogout = useCallback(async () => {
    try {
      const rt = getRefreshToken();
      if (rt) await api.logout(rt);
    } catch {
      // ignore
    }
    clearTokens();
    router.push("/login");
  }, [router]);

  const initials = user
    ? user.fullName
        .split(" ")
        .slice(0, 2)
        .map((n: string) => n[0])
        .join("")
        .toUpperCase()
    : "?";

  return (
    <>
      {/* Overlay for mobile */}
      <div
        className={`bl-sidebar-overlay${open ? " open" : ""}`}
        onClick={onClose}
        aria-hidden="true"
      />

      <aside className={`bl-sidebar${open ? " open" : ""}`} aria-label="Dashboard navigation">
        {/* Logo */}
        <div className="bl-sidebar-logo">
          <Link href="/dashboard" onClick={onClose} style={{ display: "flex", alignItems: "center", gap: 8, textDecoration: "none", color: "inherit" }}>
            <span className="bl-sidebar-logo-icon" aria-hidden="true">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="white" aria-hidden="true">
                <path d="M12 3L2 10h2v11h16V10h2L12 3zm0 2.18L20 11v9H4v-9l8-5.82zM9 13h6v6H9v-6z" />
              </svg>
            </span>
            bildfie
          </Link>
        </div>

        {/* Navigation */}
        <nav className="bl-sidebar-nav">
          {/* Dashboard plain link */}
          <div className="bl-sidebar-section">
            <Link
              href="/dashboard"
              className={`bl-sidebar-link${isActive("/dashboard") ? " active" : ""}`}
              onClick={onClose}
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <rect x="3" y="3" width="7" height="7" />
                <rect x="14" y="3" width="7" height="7" />
                <rect x="14" y="14" width="7" height="7" />
                <rect x="3" y="14" width="7" height="7" />
              </svg>
              Dashboard
            </Link>

            <Link
              href="/marketplace"
              className={`bl-sidebar-link${isActive("/marketplace") ? " active" : ""}`}
              onClick={onClose}
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" /><circle cx="12" cy="10" r="3" />
              </svg>
              Find Professionals
            </Link>
          </div>

          {/* Accordion groups */}
          {NAV_GROUPS.map((group) => {
            const isGroupOpen = openGroup === group.id;
            const groupHasActive = group.items.some((item) =>
              isActive(item.href)
            );

            return (
              <div key={group.id} className="bl-sidebar-section">
                <div className="bl-sidebar-section-title" aria-hidden="true">
                  {group.label}
                </div>
                <button
                  className={`bl-sidebar-group-btn${isGroupOpen ? " open" : ""}${groupHasActive ? " active" : ""}`}
                  onClick={() => toggleGroup(group.id)}
                  aria-expanded={isGroupOpen}
                >
                  {group.icon}
                  {group.label}
                  <span className={`bl-sidebar-chevron${isGroupOpen ? " open" : ""}`}>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" aria-hidden="true">
                      <path d="M6 9l6 6 6-6" />
                    </svg>
                  </span>
                </button>
                <div className={`bl-sidebar-group-children${isGroupOpen ? " open" : ""}`}>
                  {group.items.map((item) => (
                    <Link
                      key={item.href}
                      href={item.href}
                      className={`bl-sidebar-sub-link${isActive(item.href) ? " active" : ""}`}
                      onClick={onClose}
                    >
                      {item.label}
                    </Link>
                  ))}
                </div>
              </div>
            );
          })}

          {/* Settings link */}
          <div className="bl-sidebar-section">
            <Link
              href="/settings"
              className={`bl-sidebar-link${isActive("/settings") ? " active" : ""}`}
              onClick={onClose}
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="3" />
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
              </svg>
              Settings
            </Link>
          </div>
        </nav>

        {/* User footer */}
        <div className="bl-sidebar-footer">
          <div className="bl-sidebar-user">
            <div className="bl-sidebar-user-avatar" aria-hidden="true">
              {initials}
            </div>
            <div style={{ flex: 1, minWidth: 0 }}>
              <div className="bl-sidebar-user-name">
                {user?.fullName ?? "Guest"}
              </div>
              <div className="bl-sidebar-user-role">
                {user?.role?.toLowerCase() ?? "member"}
              </div>
            </div>
            <button
              onClick={handleLogout}
              title="Sign out"
              style={{ color: "rgba(255,255,255,.5)", flexShrink: 0 }}
              aria-label="Sign out"
            >
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4 M16 17l5-5-5-5 M21 12H9" />
              </svg>
            </button>
          </div>
        </div>
      </aside>
    </>
  );
}
