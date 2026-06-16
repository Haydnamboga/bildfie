// Typed SDK that every client (web + mobile) calls. Wraps fetch and points
// at the one API. No client ever touches the database directly.
import type { ApiError, SessionUser } from "@bildfie/types";

export interface ApiClientOptions {
  baseUrl: string;
  getToken?: () => string | null | Promise<string | null>;
}

export class ApiClient {
  constructor(private readonly opts: ApiClientOptions) {}

  private async request<T>(path: string, init: RequestInit = {}): Promise<T> {
    const token = (await this.opts.getToken?.()) ?? null;
    const res = await fetch(`${this.opts.baseUrl}${path}`, {
      ...init,
      headers: {
        "Content-Type": "application/json",
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        ...init.headers,
      },
    });

    if (!res.ok) {
      const body = (await res.json().catch(() => ({}))) as Partial<ApiError>;
      throw Object.assign(new Error(body.message ?? res.statusText), {
        statusCode: res.status,
      });
    }
    return res.json() as Promise<T>;
  }

  // --- example surface; expand per module ---
  me() {
    return this.request<SessionUser>("/auth/me");
  }

  health() {
    return this.request<{ status: string }>("/health");
  }
}

export function createApiClient(opts: ApiClientOptions): ApiClient {
  return new ApiClient(opts);
}
