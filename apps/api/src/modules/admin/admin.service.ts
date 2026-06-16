import { Injectable, NotFoundException, ForbiddenException, BadRequestException } from "@nestjs/common";
import { prisma } from "@bildfie/db";
import type { SessionUser } from "@bildfie/types";

@Injectable()
export class AdminService {
  async listUsers(opts: { page?: number; pageSize?: number; role?: string; q?: string }) {
    const { page = 1, pageSize = 50, role, q } = opts;
    const where = {
      ...(role ? { role: role as "USER" | "ADMIN" | "SUPER_ADMIN" } : {}),
      ...(q ? { OR: [
        { fullName: { contains: q, mode: "insensitive" as const } },
        { email: { contains: q, mode: "insensitive" as const } },
      ]} : {}),
    };
    const [data, total] = await Promise.all([
      prisma.user.findMany({
        where,
        select: { id: true, email: true, fullName: true, role: true, emailVerified: true, createdAt: true },
        orderBy: { createdAt: "desc" },
        skip: (page - 1) * pageSize,
        take: pageSize,
      }),
      prisma.user.count({ where }),
    ]);
    return { data, total, page, pageSize };
  }

  async changeUserRole(actor: SessionUser, targetId: string, newRole: string) {
    if (targetId === actor.id) throw new BadRequestException("Cannot change your own role");
    const target = await prisma.user.findUnique({ where: { id: targetId } });
    if (!target) throw new NotFoundException("User not found");

    // Super-admin can set any role; admin can only set USER
    if (actor.role === "ADMIN" && newRole !== "USER") {
      throw new ForbiddenException("Admins can only demote to USER");
    }
    // No one can promote to SUPER_ADMIN except an existing SUPER_ADMIN
    if (newRole === "SUPER_ADMIN" && actor.role !== "SUPER_ADMIN") {
      throw new ForbiddenException("Only super-admins can grant super-admin role");
    }

    return prisma.user.update({
      where: { id: targetId },
      data: { role: newRole as "USER" | "ADMIN" | "SUPER_ADMIN" },
      select: { id: true, email: true, fullName: true, role: true },
    });
  }

  async listProjects(opts: { page?: number; pageSize?: number; status?: string }) {
    const { page = 1, pageSize = 50, status } = opts;
    const where = status ? { status: status as "DRAFT" | "ACTIVE" | "COMPLETED" | "CANCELLED" } : {};
    const [data, total] = await Promise.all([
      prisma.project.findMany({
        where,
        include: {
          owner: { select: { id: true, fullName: true, email: true } },
          _count: { select: { team: true, tasks: true } },
        },
        orderBy: { updatedAt: "desc" },
        skip: (page - 1) * pageSize,
        take: pageSize,
      }),
      prisma.project.count({ where }),
    ]);
    return { data, total, page, pageSize };
  }

  async getAuditLogs(opts: { page?: number; pageSize?: number; actorId?: string }) {
    const { page = 1, pageSize = 100, actorId } = opts;
    const where = actorId ? { actorId } : {};
    const [data, total] = await Promise.all([
      prisma.auditLog.findMany({
        where,
        include: { actor: { select: { id: true, fullName: true, email: true } } },
        orderBy: { createdAt: "desc" },
        skip: (page - 1) * pageSize,
        take: pageSize,
      }),
      prisma.auditLog.count({ where }),
    ]);
    return { data, total, page, pageSize };
  }

  async getStats() {
    const [users, projects, payments, openOffers] = await Promise.all([
      prisma.user.count(),
      prisma.project.count(),
      prisma.payment.aggregate({ _sum: { amount: true }, where: { status: "RELEASED" } }),
      prisma.offer.count({ where: { status: "PENDING" } }),
    ]);
    return { users, projects, paymentsReleased: payments._sum.amount ?? 0, openOffers };
  }
}
