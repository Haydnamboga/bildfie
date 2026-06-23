"use client";
import { useEffect, useState, useCallback } from "react";

const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:4000";

interface UnreadCounts {
  notifications: number;
  messages: number;
}

export function useUnreadCounts(): UnreadCounts {
  const [counts, setCounts] = useState<UnreadCounts>({ notifications: 0, messages: 0 });

  const fetch_counts = useCallback(async () => {
    try {
      const token = typeof window !== "undefined" ? localStorage.getItem("bildfie_token") : null;
      if (!token) return;
      const res = await fetch(`${API_URL}/users/me/unread-counts`, {
        headers: { Authorization: `Bearer ${token}` },
        signal: AbortSignal.timeout(5000),
      });
      if (res.ok) {
        const data = await res.json();
        setCounts({
          notifications: data.notifications ?? 0,
          messages: data.messages ?? 0,
        });
      }
    } catch {
      // silently ignore — counts default to 0
    }
  }, []);

  useEffect(() => {
    fetch_counts();
    const id = setInterval(fetch_counts, 60_000); // poll every minute
    return () => clearInterval(id);
  }, [fetch_counts]);

  return counts;
}
