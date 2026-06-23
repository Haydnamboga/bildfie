"use client";
import { useEffect } from "react";
import type { JSX } from "react";

const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:4000";

export function usePresenceHeartbeat(): void {
  useEffect(() => {
    const ping = async () => {
      try {
        const token = localStorage.getItem("bildfie_token");
        if (!token) return;
        await fetch(`${API_URL}/users/me/presence`, {
          method: "POST",
          headers: { Authorization: `Bearer ${token}` },
          signal: AbortSignal.timeout(5000),
        });
      } catch {
        // ignore
      }
    };
    ping();
    const id = setInterval(ping, 30_000);
    return () => clearInterval(id);
  }, []);
}

export type PresenceStatus = "online" | "away" | "offline";

export function presenceStatus(lastSeenAt: string | null | undefined): PresenceStatus {
  if (!lastSeenAt) return "offline";
  const diff = (Date.now() - new Date(lastSeenAt).getTime()) / 1000;
  if (diff < 120) return "online";
  if (diff < 900) return "away";
  return "offline";
}

export function PresenceDot({ lastSeenAt, size = 9 }: { lastSeenAt?: string | null; size?: number }): JSX.Element {
  const status = presenceStatus(lastSeenAt);
  const colors: Record<PresenceStatus, string> = {
    online: "#22c55e",
    away:   "#f97316",
    offline: "#94a3b8",
  };
  const labels: Record<PresenceStatus, string> = {
    online: "Online",
    away:   "Away",
    offline: "Offline",
  };
  return (
    <span
      title={labels[status]}
      style={{
        display: "inline-block",
        width: size,
        height: size,
        borderRadius: "50%",
        background: colors[status],
        border: "2px solid #fff",
        flexShrink: 0,
      }}
    />
  );
}
