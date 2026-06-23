"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { api } from "@/lib/api";
import { getCurrentUser } from "@/lib/auth";

type Project = {
  id: string;
  title: string;
  status: string;
  _count: { tasks: number; team: number };
};

type Notification = {
  id: string;
  title: string;
  body?: string;
  read: boolean;
};

function greeting(): string {
  const hour = new Date().getHours();
  if (hour < 12) return "Good morning";
  if (hour < 17) return "Good afternoon";
  return "Good evening";
}

function statusBadgeClass(status: string): string {
  const map: Record<string, string> = {
    ACTIVE: "bl-badge-active",
    DRAFT: "bl-badge-draft",
    FINISHED: "bl-badge-finished",
    ON_HOLD: "bl-badge-on_hold",
    CANCELLED: "bl-badge-cancelled",
  };
  return `bl-badge ${map[status] ?? "bl-badge-open"}`;
}

function KpiCard({
  label,
  value,
  iconClass,
  iconPath,
}: {
  label: string;
  value: string | number;
  iconClass: string;
  iconPath: string;
}) {
  return (
    <div className="bl-kpi-card">
      <div className={`bl-kpi-icon ${iconClass}`}>
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
          <path d={iconPath} />
        </svg>
      </div>
      <div className="bl-kpi-value">{value}</div>
      <div className="bl-kpi-label">{label}</div>
    </div>
  );
}

export default function DashboardPage() {
  const router = useRouter();
  const user = getCurrentUser();
  const [projects, setProjects] = useState<Project[]>([]);
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) {
      router.push("/login");
      return;
    }
    Promise.all([
      api.listProjects().catch(() => [] as Project[]),
      fetch(
        `${process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:4000"}/notifications`,
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("bildfie_token")}`,
          },
        }
      )
        .then((r) => (r.ok ? r.json() : []))
        .catch(() => []),
    ]).then(([ps, ns]) => {
      setProjects(ps as Project[]);
      setNotifications(ns as Notification[]);
      setLoading(false);
    });
  }, []);

  const firstName = user?.fullName?.split(" ")[0] ?? "";
  const unreadCount = notifications.filter((n) => !n.read).length;
  const totalTasks = projects.reduce(
    (sum, p) => sum + (p._count?.tasks ?? 0),
    0
  );

  return (
    <>
      {/* Page header */}
      <div className="bl-page-header">
        <div>
          <h1 style={{ fontFamily: "Sora, sans-serif", fontSize: "1.4rem" }}>
            {greeting()}{firstName ? `, ${firstName}` : ""}! 👋
          </h1>
          <p className="bl-text-muted" style={{ marginTop: 4 }}>
            Here&rsquo;s what&rsquo;s happening across your projects.
          </p>
        </div>
        <Link href="/projects/new" className="bl-btn bl-btn-accent">
          + New Project
        </Link>
      </div>

      {/* KPI row */}
      <div className="bl-grid-4" style={{ marginBottom: 28 }}>
        <KpiCard
          label="My Projects"
          value={loading ? "—" : projects.length}
          iconClass="bl-kpi-icon-navy"
          iconPath="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z M9 22V12h6v10"
        />
        <KpiCard
          label="Invoices Outstanding"
          value="—"
          iconClass="bl-kpi-icon-accent"
          iconPath="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M16 13H8 M16 17H8 M10 9H8"
        />
        <KpiCard
          label="Wallet Balance"
          value="KES —"
          iconClass="bl-kpi-icon-green"
          iconPath="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"
        />
        <KpiCard
          label="Bids Received"
          value={loading ? "—" : unreadCount}
          iconClass="bl-kpi-icon-gold"
          iconPath="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
        />
      </div>

      {/* Two-column content */}
      <div
        style={{
          display: "grid",
          gridTemplateColumns: "repeat(auto-fit, minmax(340px, 1fr))",
          gap: 20,
        }}
      >
        {/* Recent projects */}
        <div className="bl-card">
          <div
            className="bl-card-body"
            style={{
              display: "flex",
              alignItems: "center",
              justifyContent: "space-between",
              borderBottom: "1px solid var(--bl-rule)",
              paddingBottom: 14,
              marginBottom: 0,
            }}
          >
            <h2 style={{ fontFamily: "Sora, sans-serif", fontSize: 15, fontWeight: 600 }}>
              Recent Projects
            </h2>
            <Link
              href="/projects"
              style={{ fontSize: 13, color: "var(--bl-accent)" }}
            >
              View all →
            </Link>
          </div>

          {loading ? (
            <div className="bl-empty">
              <p>Loading…</p>
            </div>
          ) : projects.length === 0 ? (
            <div className="bl-empty">
              <div className="bl-empty-icon">📋</div>
              <p>No projects yet. Create your first project to get started.</p>
              <Link href="/projects/new" className="bl-btn bl-btn-accent bl-btn-sm" style={{ marginTop: 4 }}>
                Create Project
              </Link>
            </div>
          ) : (
            <div>
              {projects.slice(0, 5).map((p, i) => (
                <Link
                  key={p.id}
                  href={`/projects/${p.id}`}
                  style={{
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "space-between",
                    padding: "11px 20px",
                    borderBottom:
                      i < Math.min(projects.length, 5) - 1
                        ? "1px solid var(--bl-rule)"
                        : "none",
                    textDecoration: "none",
                    transition: "background .15s",
                  }}
                  className="bl-table-row-link"
                >
                  <div>
                    <div
                      style={{
                        fontSize: 13,
                        fontWeight: 600,
                        color: "var(--bl-navy)",
                        marginBottom: 2,
                      }}
                    >
                      {p.title}
                    </div>
                    <div style={{ fontSize: 12, color: "var(--bl-muted)" }}>
                      {p._count?.tasks ?? 0} tasks · {p._count?.team ?? 0} members
                    </div>
                  </div>
                  <span className={statusBadgeClass(p.status)}>
                    {p.status.charAt(0) + p.status.slice(1).toLowerCase().replace("_", " ")}
                  </span>
                </Link>
              ))}
            </div>
          )}
        </div>

        {/* Notifications */}
        <div className="bl-card">
          <div
            className="bl-card-body"
            style={{
              display: "flex",
              alignItems: "center",
              justifyContent: "space-between",
              borderBottom: "1px solid var(--bl-rule)",
              paddingBottom: 14,
            }}
          >
            <h2 style={{ fontFamily: "Sora, sans-serif", fontSize: 15, fontWeight: 600 }}>
              Notifications
              {unreadCount > 0 && (
                <span
                  className="bl-badge bl-badge-new"
                  style={{ marginLeft: 8 }}
                >
                  {unreadCount}
                </span>
              )}
            </h2>
          </div>

          {notifications.length === 0 ? (
            <div className="bl-empty">
              <div className="bl-empty-icon">🔔</div>
              <p>No notifications yet.</p>
            </div>
          ) : (
            <div>
              {notifications.slice(0, 5).map((n, i) => (
                <div
                  key={n.id}
                  style={{
                    padding: "11px 20px",
                    borderBottom:
                      i < Math.min(notifications.length, 5) - 1
                        ? "1px solid var(--bl-rule)"
                        : "none",
                    opacity: n.read ? 0.55 : 1,
                  }}
                >
                  {!n.read && (
                    <div
                      style={{
                        width: 7,
                        height: 7,
                        borderRadius: "50%",
                        background: "var(--bl-accent)",
                        display: "inline-block",
                        marginRight: 6,
                        verticalAlign: "middle",
                      }}
                    />
                  )}
                  <span
                    style={{
                      fontSize: 13,
                      fontWeight: n.read ? 400 : 600,
                      color: "var(--bl-navy)",
                    }}
                  >
                    {n.title}
                  </span>
                  {n.body && (
                    <p
                      style={{
                        fontSize: 12,
                        color: "var(--bl-muted)",
                        marginTop: 2,
                      }}
                    >
                      {n.body}
                    </p>
                  )}
                </div>
              ))}
            </div>
          )}
        </div>
      </div>

      {/* Quick actions */}
      <div style={{ marginTop: 28 }}>
        <h2
          style={{
            fontFamily: "Sora, sans-serif",
            fontSize: 14,
            fontWeight: 600,
            color: "var(--bl-muted)",
            textTransform: "uppercase",
            letterSpacing: ".05em",
            marginBottom: 14,
          }}
        >
          Quick Actions
        </h2>
        <div style={{ display: "flex", flexWrap: "wrap", gap: 10 }}>
          {[
            { label: "Post a Project", href: "/projects/new", variant: "bl-btn-accent" },
            { label: "Find Professionals", href: "/marketplace", variant: "bl-btn-outline" },
            { label: "Create Invoice", href: "/invoices/new", variant: "bl-btn-outline" },
            { label: "View Reports", href: "/reports/executive", variant: "bl-btn-outline" },
          ].map((action) => (
            <Link
              key={action.href}
              href={action.href}
              className={`bl-btn ${action.variant} bl-btn-sm`}
            >
              {action.label}
            </Link>
          ))}
        </div>
      </div>

      {/* Stats summary */}
      {!loading && projects.length > 0 && (
        <div
          className="bl-card"
          style={{ marginTop: 28 }}
        >
          <div className="bl-card-body">
            <h2
              style={{
                fontFamily: "Sora, sans-serif",
                fontSize: 15,
                fontWeight: 600,
                marginBottom: 16,
              }}
            >
              Summary
            </h2>
            <div
              style={{
                display: "grid",
                gridTemplateColumns: "repeat(auto-fit, minmax(120px, 1fr))",
                gap: 16,
                textAlign: "center",
              }}
            >
              {[
                { label: "Total projects", value: projects.length },
                { label: "Active", value: projects.filter((p) => p.status === "ACTIVE").length },
                { label: "Total tasks", value: totalTasks },
                { label: "Team members", value: projects.reduce((s, p) => s + (p._count?.team ?? 0), 0) },
              ].map((stat) => (
                <div key={stat.label}>
                  <div
                    style={{
                      fontSize: 26,
                      fontWeight: 700,
                      color: "var(--bl-navy)",
                      lineHeight: 1,
                    }}
                  >
                    {stat.value}
                  </div>
                  <div
                    style={{
                      fontSize: 12,
                      color: "var(--bl-muted)",
                      marginTop: 4,
                    }}
                  >
                    {stat.label}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
    </>
  );
}
