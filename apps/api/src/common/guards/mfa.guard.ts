import { CanActivate, ExecutionContext, Injectable, ForbiddenException } from "@nestjs/common";
import { hasRole } from "@bildfie/auth";

/**
 * MFA is required for ADMIN and SUPER_ADMIN sessions (§11). Apply on top of
 * AuthGuard + RolesGuard for privileged routes.
 */
@Injectable()
export class MfaGuard implements CanActivate {
  canActivate(context: ExecutionContext): boolean {
    const request = context.switchToHttp().getRequest();
    const user = request.user;
    if (user && hasRole(user.role, "ADMIN") && !request.mfaVerified) {
      throw new ForbiddenException("MFA required");
    }
    return true;
  }
}
