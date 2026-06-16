import { CallHandler, ExecutionContext, Injectable, NestInterceptor } from "@nestjs/common";
import { Observable } from "rxjs";
import { tap } from "rxjs/operators";
import { AuditService } from "../../modules/audit/audit.service";

@Injectable()
export class AuditInterceptor implements NestInterceptor {
  constructor(private readonly auditService: AuditService) {}

  intercept(context: ExecutionContext, next: CallHandler): Observable<unknown> {
    const request = context.switchToHttp().getRequest<{
      method: string;
      url: string;
      user?: { id: string };
    }>();
    const { method, url, user } = request;
    return next.handle().pipe(
      tap(() => {
        if (method !== "GET") {
          void this.auditService.log({
            actorId: user?.id ?? null,
            action: `${method} ${url}`,
          });
        }
      }),
    );
  }
}
