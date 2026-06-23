"use client";

import { useState } from "react";
import type { ReactNode } from "react";
import { DashSidebar } from "@/components/DashSidebar";
import { DashTopbar } from "@/components/DashTopbar";

export default function AppLayout({ children }: { children: ReactNode }) {
  const [sidebarOpen, setSidebarOpen] = useState(false);

  return (
    <div className="bl-shell">
      <DashSidebar
        open={sidebarOpen}
        onClose={() => setSidebarOpen(false)}
      />
      <div className="bl-main">
        <DashTopbar
          onMenuToggle={() => setSidebarOpen((v) => !v)}
        />
        <div className="bl-body">{children}</div>
      </div>
    </div>
  );
}
