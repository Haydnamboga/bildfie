import { createApiClient } from "@bildfie/api-client";
import { getToken } from "./auth";

export const api = createApiClient({
  baseUrl: process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:4000",
  getToken,
});
