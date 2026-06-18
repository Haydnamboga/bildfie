import { Injectable, NotFoundException, ForbiddenException, BadRequestException } from "@nestjs/common";
import { prisma } from "@bildfie/db";

@Injectable()
export class MilestonesService {
  private async assertOwner(userId: string, projectId: string) {
    const project = await prisma.project.findUnique({ where: { id: projectId } });
    if (!project) throw new NotFoundException("Project not found");
    if (project.ownerId !== userId) throw new ForbiddenException();
    return project;
  }

  private async assertMember(userId: string, projectId: string) {
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
    amount: number;
    dueDate?: string;
  }) {
    await this.assertOwner(userId, projectId);
    return prisma.milestone.create({
      data: {
        projectId,
        title: dto.title,
        description: dto.description,
        amount: dto.amount,
        dueDate: dto.dueDate ? new Date(dto.dueDate) : undefined,
      },
    });
  }

  async findAll(userId: string, projectId: string) {
    await this.assertMember(userId, projectId);
    return prisma.milestone.findMany({
      where: { projectId },
      include: {
        _count: { select: { tasks: true } },
        payment: { select: { id: true, status: true, provider: true } },
      },
      orderBy: { createdAt: "asc" },
    });
  }

  async update(userId: string, projectId: string, milestoneId: string, dto: {
    title?: string;
    description?: string;
    status?: string;
    amount?: number;
    dueDate?: string;
  }) {
    await this.assertOwner(userId, projectId);
    const milestone = await prisma.milestone.findUnique({ where: { id: milestoneId } });
    if (!milestone || milestone.projectId !== projectId) throw new NotFoundException("Milestone not found");

    return prisma.milestone.update({
      where: { id: milestoneId },
      data: {
        ...dto,
        dueDate: dto.dueDate ? new Date(dto.dueDate) : undefined,
      },
    });
  }

  async remove(userId: string, projectId: string, milestoneId: string) {
    await this.assertOwner(userId, projectId);
    const milestone = await prisma.milestone.findUnique({ where: { id: milestoneId } });
    if (!milestone || milestone.projectId !== projectId) throw new NotFoundException("Milestone not found");

    await prisma.milestone.delete({ where: { id: milestoneId } });
  }

  async submitForReview(userId: string, projectId: string, milestoneId: string, notes?: string) {
    await this.assertMember(userId, projectId);
    const milestone = await prisma.milestone.findUnique({ where: { id: milestoneId } });
    if (!milestone || milestone.projectId !== projectId) throw new NotFoundException();
    if (milestone.status !== "ACTIVE") throw new BadRequestException("Milestone must be ACTIVE to submit");
    const autoReleaseAt = new Date(Date.now() + 14 * 24 * 60 * 60 * 1000);
    await prisma.milestone.update({ where: { id: milestoneId }, data: { status: "SUBMITTED" } });
    return prisma.milestoneSubmission.create({
      data: { milestoneId, submittedById: userId, notes, autoReleaseAt },
    });
  }

  async approveSubmission(userId: string, projectId: string, milestoneId: string) {
    const project = await prisma.project.findUnique({ where: { id: projectId } });
    if (!project || project.ownerId !== userId) throw new NotFoundException();
    await prisma.milestoneSubmission.update({
      where: { milestoneId },
      data: { status: "APPROVED", reviewedAt: new Date() },
    });
    return prisma.milestone.update({ where: { id: milestoneId }, data: { status: "COMPLETED" } });
  }

  async requestRevision(userId: string, projectId: string, milestoneId: string) {
    const project = await prisma.project.findUnique({ where: { id: projectId } });
    if (!project || project.ownerId !== userId) throw new NotFoundException();
    await prisma.milestoneSubmission.update({
      where: { milestoneId },
      data: { status: "REVISION_REQUESTED", reviewedAt: new Date() },
    });
    return prisma.milestone.update({ where: { id: milestoneId }, data: { status: "ACTIVE" } });
  }
}
