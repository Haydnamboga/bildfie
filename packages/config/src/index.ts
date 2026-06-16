// Env access, shared constants, and feature flags.

export const APP_NAME = "bildfie";

export const FEATURE_FLAGS = {
  mpesaPayments: true,
  stripePayments: true,
  inProjectMessaging: true,
  mobileApp: false, // ship once the API is stable (build order §10.7)
} as const;

/** Read a required env var or throw — call at startup, not in hot paths. */
export function requireEnv(key: string): string {
  const value = process.env[key];
  if (!value) {
    throw new Error(`Missing required environment variable: ${key}`);
  }
  return value;
}
