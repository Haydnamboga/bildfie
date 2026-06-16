import { CanActivate, ExecutionContext, Injectable, UnauthorizedException } from "@nestjs/common";
import { verifyAccessToken } from "@bildfie/auth/server";

@Injectable()
export class AuthGuard implements CanActivate {
  canActivate(context: ExecutionContext): boolean {
    const request = context.switchToHttp().getRequest();
    const raw = (request.headers.authorization ?? "") as string;
    const token = raw.startsWith("Bearer ") ? raw.slice(7).trim() : "";
    if (!token) throw new UnauthorizedException();

    try {
      const payload = verifyAccessToken(token);
      request.user = {
        id: payload.sub,
        email: payload.email,
        fullName: payload.fullName,
        role: payload.role,
        mfaVerified: payload.mfaVerified,
      };
    } catch {
      throw new UnauthorizedException("Token invalid or expired");
    }
    return true;
  }
}
