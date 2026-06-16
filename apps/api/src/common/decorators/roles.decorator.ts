import { SetMetadata } from "@nestjs/common";
import type { UserRole } from "@bildfie/types";

export const ROLES_KEY = "roles";

/** Restrict a route to one or more roles, e.g. @Roles('ADMIN'). */
export const Roles = (...roles: UserRole[]) => SetMetadata(ROLES_KEY, roles);
