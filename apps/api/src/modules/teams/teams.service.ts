import { Injectable, NotFoundException, ForbiddenException, ConflictException } from "@nestjs/common";
import { prisma } from "@bildfie/db";

@Injectable()
export class TeamsService {
  async invite(requesterId: string, projectId: string, dto: { userId: string; roleLabel?: string }) {
    const project = await prisma.project.findUnique({ where: { id: projectId } });
    if (!project) throw new NotFoundException("Project not found");
    if (project.ownerId !== requesterId) throw new ForbiddenException();

    const exists = await prisma.teamMember.findUnique({
      where: { projectId_userId: { projectId, userId: dto.userId } },
    });
    if (exists) throw new ConflictException("User already on team");

    return prisma.teamMember.create({
      data: { projectId, userId: dto.userId, roleLabel: dto.roleLabel },
      include: { user: { select: { id: true, fullName: true, headline: true } } },
    });
  }

  async list(requesterId: string, projectId: string) {
    const project = await prisma.project.findUnique({
      where: { id: projectId },
      include: { team: true },
    });
    if (!project) throw new NotFoundException("Project not found");
    const isMember =
      project.ownerId === requesterId || project.team.some((m) => m.userId === requesterId);
    if (!isMember) throw new NotFoundException("Project not found");

    return prisma.teamMember.findMany({
      where: { projectId },
      include: { user: { select: { id: true, fullName: true, headline: true, skills: true } } },
    });
  }

  async remove(requesterId: string, projectId: string, memberId: string) {
    const project = await prisma.project.findUnique({ where: { id: projectId } });
    if (!project) throw new NotFoundException("Project not found");
    if (project.ownerId !== requesterId) throw new ForbiddenException();

    const member = await prisma.teamMember.findUnique({ where: { id: memberId } });
    if (!member || member.projectId !== projectId) throw new NotFoundException("Member not found");

    await prisma.teamMember.delete({ where: { id: memberId } });
  }
}
