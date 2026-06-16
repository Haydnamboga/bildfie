import { CanActivate, ExecutionContext, Injectable, NotFoundException } from "@nestjs/common";
import { Reflector } from "@nestjs/core";
import { hasRole } from "@bildfie/auth";
import type { UserRole } from "@bildfie/types";
import { ROLES_KEY } from "../decorators/roles.decorator";

/**
 * Enforces @Roles() server-side. Returns 404 (not 403) so the existence of
 * protected back-office / super-admin surfaces stays invisible (§11).
 */
@Injectable()
export class RolesGuard implements CanActivate {
  constructor(private readonly reflector: Reflector) {}

  canActivate(context: ExecutionContext): boolean {
    const required = this.reflector.getAllAndOverride<UserRole[]>(ROLES_KEY, [
      context.getHandler(),
      context.getClass(),
    ]);
    if (!required || required.length === 0) {
      return true;
    }

    const request = context.switchToHttp().getRequest();
    const user = request.user;
    const allowed = user && required.some((r) => hasRole(user.role, r));
    if (!allowed) {
      // Hide existence — 404, never 403.
      throw new NotFoundException();
    }
    return true;
  }
}
