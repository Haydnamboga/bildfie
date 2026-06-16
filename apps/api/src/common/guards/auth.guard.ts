import { CanActivate, ExecutionContext, Injectable, UnauthorizedException } from "@nestjs/common";

/**
 * Validates the session/JWT and attaches the user to the request.
 * TODO: verify the token via @bildfie/auth and load the user.
 */
@Injectable()
export class AuthGuard implements CanActivate {
  async canActivate(context: ExecutionContext): Promise<boolean> {
    const request = context.switchToHttp().getRequest();
    const token = (request.headers.authorization ?? "").replace("Bearer ", "");
    if (!token) {
      throw new UnauthorizedException();
    }
    // TODO: const payload = verifyJwt(token); request.user = await loadUser(payload.sub);
    return true;
  }
}
