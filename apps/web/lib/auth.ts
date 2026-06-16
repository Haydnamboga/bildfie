"use client";

const TOKEN_KEY = "bildfie_token";
const REFRESH_KEY = "bildfie_refresh";

export function getToken(): string | null {
  if (typeof window === "undefined") return null;
  return localStorage.getItem(TOKEN_KEY);
}

export function getRefreshToken(): string | null {
  if (typeof window === "undefined") return null;
  return localStorage.getItem(REFRESH_KEY);
}

export function setTokens(accessToken: string, refreshToken: string): void {
  localStorage.setItem(TOKEN_KEY, accessToken);
  localStorage.setItem(REFRESH_KEY, refreshToken);
  document.cookie = `session=${accessToken}; path=/; max-age=${60 * 15}; SameSite=Lax`;
}

export function clearTokens(): void {
  localStorage.removeItem(TOKEN_KEY);
  localStorage.removeItem(REFRESH_KEY);
  document.cookie = "session=; path=/; max-age=0";
}

export function decodeToken(token: string): { role?: string; sub?: string; fullName?: string; email?: string; exp?: number } | null {
  try {
    const parts = token.split(".");
    if (parts.length !== 3) return null;
    const payload = JSON.parse(atob(parts[1].replace(/-/g, "+").replace(/_/g, "/")));
    if (payload.exp && Math.floor(Date.now() / 1000) > payload.exp) return null;
    return payload;
  } catch {
    return null;
  }
}

export function getCurrentUser(): { id: string; email: string; fullName: string; role: string } | null {
  const token = getToken();
  if (!token) return null;
  const payload = decodeToken(token);
  if (!payload?.sub) return null;
  return {
    id: payload.sub,
    email: payload.email ?? "",
    fullName: payload.fullName ?? "",
    role: payload.role ?? "USER",
  };
}
