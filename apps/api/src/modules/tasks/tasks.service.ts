import { Injectable, NotFoundException, ForbiddenException } from "@nestjs/common";
import { prisma } from "@bildfie/db";

@Injectable()
export class TasksService {
  private async assertAccess(userId: string, projectId: string) {
    const project = await prisma.project.findUnique({
      where: { id: projectId },
      include: { team: true },
    });
    if (!project) throw new NotFoundException("Project not found");
    const isMember =
      project.ownerId === userId || project.team.some((m) => m.userId === userId);
    if (!isMember) throw new NotFoundException("Project not found");
    return project;
  }

  async create(userId: string, projectId: string, dto: {
    title: string;
    description?: string;
    milestoneId?: string;
    assigneeId?: string;
    dueDate?: string;
  }) {
    await this.assertAccess(userId, projectId);
    return prisma.task.create({
      data: {
        projectId,
        title: dto.title,
        description: dto.description,
        milestoneId: dto.milestoneId,
        assigneeId: dto.assigneeId,
        dueDate: dto.dueDate ? new Date(dto.dueDate) : undefined,
      },
    });
  }

  async findAll(userId: string, projectId: string) {
    await this.assertAccess(userId, projectId);
    return prisma.task.findMany({
      where: { projectId },
      orderBy: [{ status: "asc" }, { createdAt: "asc" }],
    });
  }

  async update(userId: string, projectId: string, taskId: string, dto: {
    title?: string;
    description?: string;
    status?: string;
    assigneeId?: string;
    milestoneId?: string;
    dueDate?: string;
  }) {
    await this.assertAccess(userId, projectId);
    const task = await prisma.task.findUnique({ where: { id: taskId } });
    if (!task || task.projectId !== projectId) throw new NotFoundException("Task not found");

    return prisma.task.update({
      where: { id: taskId },
      data: {
        ...dto,
        dueDate: dto.dueDate ? new Date(dto.dueDate) : undefined,
      },
    });
  }

  async remove(userId: string, projectId: string, taskId: string) {
    const project = await prisma.project.findUnique({ where: { id: projectId } });
    if (!project) throw new NotFoundException("Project not found");
    if (project.ownerId !== userId) throw new ForbiddenException();

    const task = await prisma.task.findUnique({ where: { id: taskId } });
    if (!task || task.projectId !== projectId) throw new NotFoundException("Task not found");

    await prisma.task.delete({ where: { id: taskId } });
  }
}
