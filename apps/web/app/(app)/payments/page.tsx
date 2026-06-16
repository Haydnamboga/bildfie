"use client";
import { AppNav } from "@/components/AppNav";

export default function PaymentsPage() {
  return (
    <>
      <AppNav />
      <div className="container" style={{ padding: "32px 20px" }}>
        <div className="page-header">
          <h1>Payments</h1>
          <p>Payments are managed per milestone inside each project.</p>
        </div>
        <div className="card empty">
          <p>Navigate to a project → Milestones to initiate or track payments.</p>
        </div>
      </div>
    </>
  );
}
