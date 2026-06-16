import { createParamDecorator, ExecutionContext } from "@nestjs/common";
import type { SessionUser } from "@bildfie/types";

/** Inject the authenticated user resolved by AuthGuard. */
export const CurrentUser = createParamDecorator(
  (_data: unknown, ctx: ExecutionContext): SessionUser | undefined => {
    const request = ctx.switchToHttp().getRequest();
    return request.user;
  },
);
