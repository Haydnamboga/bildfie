import { Injectable, NotFoundException } from "@nestjs/common";
import { prisma } from "@bildfie/db";
import { CreateDailyLogDto } from "./dto/create-daily-log.dto";

@Injectable()
export class DailyLogsService {
  private async assertProjectAccess(userId: string, projectId: string) {
    const project = await prisma.project.findUnique({
      where: { id: projectId },
      include: { team: { where: { userId } } },
    });
    if (!project) throw new NotFoundException("Project not found");
    if (project.ownerId !== userId && project.team.length === 0)
      throw new NotFoundException("Project not found");
    return project;
  }

  async list(userId: string, projectId: string) {
    await this.assertProjectAccess(userId, projectId);
    return prisma.dailyLog.findMany({
      where: { projectId },
      include: {
        author: { select: { id: true, name: true } },
      },
      orderBy: { logDate: "desc" },
    });
  }

  async create(userId: string, projectId: string, dto: CreateDailyLogDto) {
    await this.assertProjectAccess(userId, projectId);
    return prisma.dailyLog.create({
      data: {
        projectId,
        authorId: userId,
        logDate: new Date(dto.logDate),
        weather: dto.weather,
        workersCount: dto.workersCount,
        notes: dto.notes,
      },
    });
  }
}
