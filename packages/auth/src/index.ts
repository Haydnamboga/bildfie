// Sessions, JWT, RBAC roles & guards — enforced identically across all surfaces.
// The API is the real wall; UI gating is convenience only.
import type { UserRole } from "@bildfie/types";

// Role hierarchy — higher roles inherit lower-role access.
export const ROLE_RANK: Record<UserRole, number> = {
  USER: 1,
  ADMIN: 2,
  SUPER_ADMIN: 3,
};

/** True if `role` meets or exceeds the `required` role. */
export function hasRole(role: UserRole, required: UserRole): boolean {
  return ROLE_RANK[role] >= ROLE_RANK[required];
}

/** Zones in the web app and the minimum role each requires. */
export const ZONE_MIN_ROLE = {
  app: "USER",
  "back-office": "ADMIN",
  "super-admin": "SUPER_ADMIN",
} as const satisfies Record<string, UserRole>;

export type Zone = keyof typeof ZONE_MIN_ROLE;

/** Whether a role may enter a given zone. Used by middleware + guards. */
export function canAccessZone(role: UserRole, zone: Zone): boolean {
  return hasRole(role, ZONE_MIN_ROLE[zone]);
}
