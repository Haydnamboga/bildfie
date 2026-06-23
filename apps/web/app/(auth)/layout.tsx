import type { ReactNode } from "react";
import Link from "next/link";

export default function AuthLayout({ children }: { children: ReactNode }) {
  return (
    <div className="bl-auth-page">
      <Link href="/" className="bl-auth-logo">
        <span
          style={{
            width: 28,
            height: 28,
            background: "var(--bl-accent)",
            borderRadius: 6,
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
          }}
          aria-hidden="true"
        >
          <svg width="15" height="15" viewBox="0 0 24 24" fill="white" aria-hidden="true">
            <path d="M12 3L2 10h2v11h16V10h2L12 3zm0 2.18L20 11v9H4v-9l8-5.82zM9 13h6v6H9v-6z" />
          </svg>
        </span>
        bildfie
      </Link>
      {children}
    </div>
  );
}
