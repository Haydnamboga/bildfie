import { createApiClient } from "@bildfie/api-client";
import { TokenStore } from "./token";

export const api = createApiClient({
  baseUrl: process.env.EXPO_PUBLIC_API_URL ?? "http://localhost:4000",
  getToken: () => TokenStore.get(),
});
