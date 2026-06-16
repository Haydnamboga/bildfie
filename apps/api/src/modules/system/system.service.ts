import { Injectable } from "@nestjs/common";
import { prisma } from "@bildfie/db";

@Injectable()
export class SystemService {
  async getHealth() {
    const dbOk = await prisma.$queryRaw`SELECT 1`.then(() => true).catch(() => false);
    return {
      status: dbOk ? "ok" : "degraded",
      db: dbOk ? "connected" : "unreachable",
      uptime: process.uptime(),
      time: new Date().toISOString(),
    };
  }

  async listAdmins() {
    return prisma.user.findMany({
      where: { role: { in: ["ADMIN", "SUPER_ADMIN"] } },
      select: { id: true, email: true, fullName: true, role: true, mfaEnabled: true, createdAt: true },
      orderBy: { role: "desc" },
    });
  }

  async promoteToSuperAdmin(targetId: string) {
    return prisma.user.update({
      where: { id: targetId },
      data: { role: "SUPER_ADMIN" },
      select: { id: true, email: true, fullName: true, role: true },
    });
  }

  async getFullAuditLog(opts: { page?: number; pageSize?: number }) {
    const { page = 1, pageSize = 100 } = opts;
    const [data, total] = await Promise.all([
      prisma.auditLog.findMany({
        include: { actor: { select: { id: true, fullName: true, email: true, role: true } } },
        orderBy: { createdAt: "desc" },
        skip: (page - 1) * pageSize,
        take: pageSize,
      }),
      prisma.auditLog.count(),
    ]);
    return { data, total, page, pageSize };
  }
}
