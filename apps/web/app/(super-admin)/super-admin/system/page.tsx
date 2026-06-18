"use client";
import { useEffect, useState } from "react";
import { AdminNav } from "@/components/AdminNav";

export default function SystemPage() {
  const [health, setHealth] = useState<{ status: string; db: string; uptime: number; time: string } | null>(null);

  useEffect(() => {
    const token = localStorage.getItem("bildfie_token");
    fetch(`${process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:4000"}/system/health`, {
      headers: { Authorization: `Bearer ${token}` },
    }).then(r => r.json()).then(setHealth).catch(() => {});
  }, []);

  return (
    <div style={{ minHeight: "100vh" }}>
      <div className="sidebar-layout">
        <AdminNav />
        <div className="main-content">
          <div className="page-header"><h1>System health</h1></div>
          {health ? (
            <div className="grid-3">
              <div className="card stat">
                <div className="stat-value" style={{ color: health.status === "ok" ? "#16a34a" : "#dc2626" }}>
                  {health.status.toUpperCase()}
                </div>
                <div className="stat-label">API status</div>
              </div>
              <div className="card stat">
                <div className="stat-value" style={{ color: health.db === "connected" ? "#16a34a" : "#dc2626", fontSize: 18 }}>
                  {health.db}
                </div>
                <div className="stat-label">Database</div>
              </div>
              <div className="card stat">
                <div className="stat-value" style={{ fontSize: 18 }}>{Math.floor(health.uptime / 60)}m</div>
                <div className="stat-label">Uptime</div>
              </div>
            </div>
          ) : <div className="card empty"><p>Loading…</p></div>}
        </div>
      </div>
    </div>
  );
}
