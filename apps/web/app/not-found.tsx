import Link from "next/link";

export default function NotFound() {
  return (
    <div style={{ minHeight: "100vh", display: "flex", alignItems: "center", justifyContent: "center", flexDirection: "column", gap: 16 }}>
      <h1 style={{ fontSize: 64, fontWeight: 700, color: "#e5e7eb" }}>404</h1>
      <p style={{ color: "#6b7280" }}>Page not found.</p>
      <Link href="/" className="btn btn-primary">Go home</Link>
    </div>
  );
}
