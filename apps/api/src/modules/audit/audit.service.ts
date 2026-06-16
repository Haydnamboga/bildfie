import { Injectable } from "@nestjs/common";
import { prisma } from "@bildfie/db";

@Injectable()
export class AuditService {
  async log(opts: {
    actorId: string | null;
    action: string;
    entity?: string;
    entityId?: string;
    metadata?: Record<string, unknown>;
  }): Promise<void> {
    await prisma.auditLog.create({
      data: {
        action: opts.action,
        entity: opts.entity,
        entityId: opts.entityId,
        metadata: opts.metadata ?? undefined,
        actorId: opts.actorId,
      },
    });
  }
}
