import { Injectable, NotFoundException, ForbiddenException } from "@nestjs/common";
import { prisma } from "@bildfie/db";
import { CreatePortfolioItemDto } from "./dto/create-portfolio-item.dto";

@Injectable()
export class PortfolioService {
  async getPortfolio(userId: string) {
    return prisma.portfolioItem.findMany({
      where: { userId },
      orderBy: { createdAt: "desc" },
    });
  }

  async addItem(userId: string, dto: CreatePortfolioItemDto) {
    return prisma.portfolioItem.create({
      data: {
        userId,
        title: dto.title,
        description: dto.description,
        imageUrl: dto.imageUrl,
        category: dto.category,
        completedAt: dto.completedAt ? new Date(dto.completedAt) : undefined,
      },
    });
  }

  async deleteItem(userId: string, itemId: string) {
    const item = await prisma.portfolioItem.findUnique({ where: { id: itemId } });
    if (!item) throw new NotFoundException("Portfolio item not found");
    if (item.userId !== userId) throw new ForbiddenException();
    await prisma.portfolioItem.delete({ where: { id: itemId } });
  }
}
