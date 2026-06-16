import { Injectable, NotFoundException } from "@nestjs/common";
import { prisma } from "@bildfie/db";

@Injectable()
export class MessagingService {
  private async assertMember(userId: string, projectId: string) {
    const project = await prisma.project.findUnique({
      where: { id: projectId },
      include: { team: true },
    });
    if (!project) throw new NotFoundException("Project not found");
    const isMember =
      project.ownerId === userId || project.team.some((m) => m.userId === userId);
    if (!isMember) throw new NotFoundException("Project not found");
  }

  async list(userId: string, projectId: string, opts: { page?: number; pageSize?: number }) {
    await this.assertMember(userId, projectId);
    const { page = 1, pageSize = 50 } = opts;
    return prisma.message.findMany({
      where: { projectId },
      include: { author: { select: { id: true, fullName: true } } },
      orderBy: { createdAt: "desc" },
      skip: (page - 1) * pageSize,
      take: pageSize,
    });
  }

  async send(userId: string, projectId: string, body: string) {
    await this.assertMember(userId, projectId);
    return prisma.message.create({
      data: { projectId, authorId: userId, body },
      include: { author: { select: { id: true, fullName: true } } },
    });
  }
}
