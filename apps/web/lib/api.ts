// Web's entry point to the typed SDK. Components call this, never fetch directly.
import { createApiClient } from "@bildfie/api-client";

export const api = createApiClient({
  baseUrl: process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:4000",
});
