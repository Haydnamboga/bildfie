"use client";
import { AdminNav } from "@/components/AdminNav";

export default function AdminMarketplacePage() {
  return (
    <div style={{ minHeight: "100vh" }}>
      <div className="sidebar-layout">
        <AdminNav />
        <div className="main-content">
          <div className="page-header"><h1>Marketplace moderation</h1><p>Review profiles and offers flagged for moderation.</p></div>
          <div className="card empty"><p>No items pending moderation.</p></div>
        </div>
      </div>
    </div>
  );
}
