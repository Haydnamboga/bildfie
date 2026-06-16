import { CallHandler, ExecutionContext, Injectable, NestInterceptor } from "@nestjs/common";
import { Observable } from "rxjs";
import { tap } from "rxjs/operators";

/**
 * Records every privileged change to the audit log (who, what, when) — §11.
 * TODO: persist via @bildfie/db AuditLog instead of console.
 */
@Injectable()
export class AuditInterceptor implements NestInterceptor {
  intercept(context: ExecutionContext, next: CallHandler): Observable<unknown> {
    const request = context.switchToHttp().getRequest();
    const { method, url, user } = request;
    return next.handle().pipe(
      tap(() => {
        if (method !== "GET") {
          // eslint-disable-next-line no-console
          console.log(`[audit] ${user?.id ?? "anon"} ${method} ${url}`);
        }
      }),
    );
  }
}
