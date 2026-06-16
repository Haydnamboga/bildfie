import type { Metadata } from "next";
import type { ReactNode } from "react";

export const metadata: Metadata = {
  title: { default: "bildfie", template: "%s | bildfie" },
  description: "Marketplace and project CRM for construction professionals.",
};

export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html lang="en">
      <head>
        <style>{`
          *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
          body { font-family: system-ui, -apple-system, sans-serif; background: #f9fafb; color: #111827; line-height: 1.5; }
          a { color: #2563eb; text-decoration: none; }
          a:hover { text-decoration: underline; }
          input, textarea, select { font: inherit; border: 1px solid #d1d5db; border-radius: 6px; padding: 8px 12px; width: 100%; outline: none; }
          input:focus, textarea:focus, select:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
          button { font: inherit; cursor: pointer; border: none; border-radius: 6px; padding: 9px 18px; }
          .btn { display: inline-flex; align-items: center; gap: 6px; font-weight: 500; }
          .btn-primary { background: #2563eb; color: #fff; }
          .btn-primary:hover { background: #1d4ed8; }
          .btn-secondary { background: #fff; color: #374151; border: 1px solid #d1d5db; }
          .btn-secondary:hover { background: #f3f4f6; }
          .btn-danger { background: #dc2626; color: #fff; }
          .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; }
          .container { max-width: 1100px; margin: 0 auto; padding: 0 20px; }
          .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; }
          .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
          .flex { display: flex; }
          .flex-col { flex-direction: column; }
          .gap-4 { gap: 16px; }
          .gap-2 { gap: 8px; }
          .label { font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px; display: block; }
          .error { color: #dc2626; font-size: 14px; margin-top: 4px; }
          .badge { display: inline-block; font-size: 12px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }
          .badge-blue { background: #dbeafe; color: #1d4ed8; }
          .badge-green { background: #d1fae5; color: #065f46; }
          .badge-yellow { background: #fef3c7; color: #92400e; }
          .badge-red { background: #fee2e2; color: #991b1b; }
          nav { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 12px 0; }
          nav .nav-inner { display: flex; align-items: center; justify-content: space-between; }
          nav .nav-logo { font-weight: 700; font-size: 18px; color: #111827; }
          nav .nav-links { display: flex; gap: 20px; align-items: center; font-size: 15px; }
          .page-header { margin-bottom: 24px; }
          .page-header h1 { font-size: 24px; font-weight: 700; }
          .page-header p { color: #6b7280; margin-top: 4px; }
          table { width: 100%; border-collapse: collapse; font-size: 14px; }
          th { text-align: left; padding: 8px 12px; border-bottom: 2px solid #e5e7eb; font-weight: 600; color: #374151; }
          td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; }
          tr:hover td { background: #f9fafb; }
          .stat { text-align: center; }
          .stat-value { font-size: 28px; font-weight: 700; color: #2563eb; }
          .stat-label { font-size: 13px; color: #6b7280; margin-top: 4px; }
          .form-group { display: flex; flex-direction: column; gap: 4px; }
          .auth-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f0f4ff; }
          .auth-card { background: #fff; border-radius: 12px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
          .auth-card h1 { font-size: 22px; font-weight: 700; margin-bottom: 6px; }
          .auth-card p { color: #6b7280; font-size: 14px; margin-bottom: 24px; }
          .sidebar-layout { display: grid; grid-template-columns: 220px 1fr; gap: 24px; min-height: calc(100vh - 57px); }
          .sidebar { background: #fff; border-right: 1px solid #e5e7eb; padding: 20px 0; }
          .sidebar a { display: block; padding: 8px 20px; color: #374151; font-size: 14px; font-weight: 500; }
          .sidebar a:hover, .sidebar a.active { background: #eff6ff; color: #2563eb; text-decoration: none; }
          .main-content { padding: 24px; }
          .empty { text-align: center; padding: 48px; color: #9ca3af; }
          .tag { display: inline-block; background: #f3f4f6; color: #374151; font-size: 12px; padding: 2px 8px; border-radius: 4px; margin: 2px; }
        `}</style>
      </head>
      <body>{children}</body>
    </html>
  );
}
