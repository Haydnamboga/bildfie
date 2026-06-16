"use client";
import { AdminNav } from "@/components/AdminNav";

export default function AdminPaymentsPage() {
  return (
    <div style={{ minHeight: "100vh" }}>
      <div className="sidebar-layout">
        <AdminNav />
        <div className="main-content">
          <div className="page-header"><h1>Payments oversight</h1><p>View payment disputes and escrow status.</p></div>
          <div className="card empty"><p>Payment oversight coming soon.</p></div>
        </div>
      </div>
    </div>
  );
}
