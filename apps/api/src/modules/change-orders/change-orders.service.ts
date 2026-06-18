import { Injectable, NotFoundException, ForbiddenException } from "@nestjs/common";
import { prisma } from "@bildfie/db";
import { CreateChangeOrderDto } from "./dto/create-change-order.dto";
import { ChangeOrderStatus } from "@prisma/client";

@Injectable()
export class ChangeOrdersService {
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

  async create(userId: string, projectId: string, dto: CreateChangeOrderDto) {
    await this.assertProjectAccess(userId, projectId);
    return prisma.changeOrder.create({
      data: {
        projectId,
        requestedById: userId,
        title: dto.title,
        description: dto.description,
        amount: dto.amount,
      },
    });
  }

  async list(userId: string, projectId: string) {
    await this.assertProjectAccess(userId, projectId);
    return prisma.changeOrder.findMany({
      where: { projectId },
      include: {
        requestedBy: { select: { id: true, name: true } },
      },
      orderBy: { createdAt: "desc" },
    });
  }

  async respond(userId: string, changeOrderId: string, action: "approve" | "reject") {
    const changeOrder = await prisma.changeOrder.findUnique({ where: { id: changeOrderId } });
    if (!changeOrder) throw new NotFoundException("Change order not found");

    const project = await prisma.project.findUnique({ where: { id: changeOrder.projectId } });
    if (!project || project.ownerId !== userId) throw new ForbiddenException();

    const status: ChangeOrderStatus = action === "approve" ? ChangeOrderStatus.APPROVED : ChangeOrderStatus.REJECTED;
    return prisma.changeOrder.update({
      where: { id: changeOrderId },
      data: { status },
    });
  }
}
