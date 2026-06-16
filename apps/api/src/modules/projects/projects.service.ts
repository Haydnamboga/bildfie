import { Injectable, NotFoundException, ForbiddenException } from "@nestjs/common";
import { prisma } from "@bildfie/db";

@Injectable()
export class ProjectsService {
  async create(ownerId: string, dto: { title: string; description?: string }) {
    return prisma.project.create({
      data: { ownerId, title: dto.title, description: dto.description },
    });
  }

  async findAll(userId: string) {
    return prisma.project.findMany({
      where: {
        OR: [
          { ownerId: userId },
          { team: { some: { userId } } },
        ],
      },
      include: {
        _count: { select: { tasks: true, team: true, milestones: true } },
      },
      orderBy: { updatedAt: "desc" },
    });
  }

  async findOne(userId: string, projectId: string) {
    const project = await prisma.project.findUnique({
      where: { id: projectId },
      include: {
        team: { include: { user: { select: { id: true, fullName: true, headline: true } } } },
        milestones: { orderBy: { createdAt: "asc" } },
        _count: { select: { tasks: true, messages: true } },
      },
    });
    if (!project) throw new NotFoundException("Project not found");
    const isMember =
      project.ownerId === userId || project.team.some((m) => m.userId === userId);
    if (!isMember) throw new NotFoundException("Project not found");
    return project;
  }

  async update(userId: string, projectId: string, dto: { title?: string; description?: string; status?: string }) {
    await this.assertOwner(userId, projectId);
    return prisma.project.update({ where: { id: projectId }, data: dto });
  }

  async remove(userId: string, projectId: string) {
    await this.assertOwner(userId, projectId);
    await prisma.project.delete({ where: { id: projectId } });
  }

  private async assertOwner(userId: string, projectId: string) {
    const project = await prisma.project.findUnique({ where: { id: projectId } });
    if (!project) throw new NotFoundException("Project not found");
    if (project.ownerId !== userId) throw new ForbiddenException();
    return project;
  }
}
