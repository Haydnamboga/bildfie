import { Injectable, NotFoundException, ForbiddenException, BadRequestException } from "@nestjs/common";
import { prisma } from "@bildfie/db";

@Injectable()
export class MarketplaceService {
  async searchProfessionals(params: {
    q?: string;
    skill?: string;
    minRate?: number;
    maxRate?: number;
    category?: string;
    location?: string;
    page?: number;
    pageSize?: number;
  }) {
    const { q = "", skill, minRate, maxRate, category, location, page = 1, pageSize = 12 } = params;
    const skip = (page - 1) * pageSize;

    const where = {
      OR: q
        ? [
            { fullName: { contains: q, mode: "insensitive" as const } },
            { headline: { contains: q, mode: "insensitive" as const } },
            { skills: { has: q } },
          ]
        : undefined,
      role: "USER" as const,
      emailVerified: true,
      ...(skill ? { skills: { has: skill } } : {}),
      ...(minRate !== undefined ? { hourlyRate: { gte: minRate } } : {}),
      ...(maxRate !== undefined ? { hourlyRate: { lte: maxRate } } : {}),
      ...(category ? { category: category as any } : {}),
      ...(location ? { location: { contains: location, mode: "insensitive" as const } } : {}),
    };

    const [data, total] = await Promise.all([
      prisma.user.findMany({
        where,
        select: {
          id: true,
          fullName: true,
          headline: true,
          bio: true,
          skills: true,
          hourlyRate: true,
          location: true,
          category: true,
          proLevel: true,
          reviewsReceived: {
            select: { rating: true },
          },
        },
        skip,
        take: pageSize,
        orderBy: { createdAt: "desc" },
      }),
      prisma.user.count({ where }),
    ]);

    const enriched = data.map((u) => {
      const reviews = u.reviewsReceived;
      const avgRating =
        reviews.length > 0
          ? reviews.reduce((sum, r) => sum + r.rating, 0) / reviews.length
          : null;
      return { ...u, reviewsReceived: undefined, avgRating, reviewCount: reviews.length };
    });

    return { data: enriched, total, page, pageSize };
  }

  async getProfile(userId: string) {
    const user = await prisma.user.findUnique({
      where: { id: userId },
      select: {
        id: true,
        fullName: true,
        headline: true,
        bio: true,
        skills: true,
        hourlyRate: true,
        createdAt: true,
        reviewsReceived: {
          select: { rating: true, comment: true, createdAt: true, author: { select: { id: true, fullName: true } } },
          orderBy: { createdAt: "desc" },
          take: 10,
        },
      },
    });
    if (!user) throw new NotFoundException("User not found");
    return user;
  }

  async createOffer(fromUserId: string, dto: {
    toUserId: string;
    projectId?: string;
    message?: string;
    amount?: number;
  }) {
    if (fromUserId === dto.toUserId) throw new BadRequestException("Cannot send offer to yourself");

    const toUser = await prisma.user.findUnique({ where: { id: dto.toUserId } });
    if (!toUser) throw new NotFoundException("Recipient not found");

    return prisma.offer.create({
      data: {
        fromUserId,
        toUserId: dto.toUserId,
        projectId: dto.projectId,
        message: dto.message,
        amount: dto.amount,
      },
    });
  }

  async respondToOffer(userId: string, offerId: string, action: "ACCEPTED" | "DECLINED") {
    const offer = await prisma.offer.findUnique({ where: { id: offerId } });
    if (!offer) throw new NotFoundException("Offer not found");
    if (offer.toUserId !== userId) throw new ForbiddenException();
    if (offer.status !== "PENDING") throw new BadRequestException("Offer already resolved");

    return prisma.offer.update({
      where: { id: offerId },
      data: { status: action },
    });
  }

  async withdrawOffer(userId: string, offerId: string) {
    const offer = await prisma.offer.findUnique({ where: { id: offerId } });
    if (!offer) throw new NotFoundException("Offer not found");
    if (offer.fromUserId !== userId) throw new ForbiddenException();
    if (offer.status !== "PENDING") throw new BadRequestException("Offer already resolved");

    return prisma.offer.update({ where: { id: offerId }, data: { status: "WITHDRAWN" } });
  }

  async listMyOffers(userId: string) {
    const [sent, received] = await Promise.all([
      prisma.offer.findMany({
        where: { fromUserId: userId },
        include: { toUser: { select: { id: true, fullName: true, headline: true } } },
        orderBy: { createdAt: "desc" },
      }),
      prisma.offer.findMany({
        where: { toUserId: userId },
        include: { fromUser: { select: { id: true, fullName: true, headline: true } } },
        orderBy: { createdAt: "desc" },
      }),
    ]);
    return { sent, received };
  }

  async createReview(authorId: string, dto: {
    subjectId: string;
    rating: number;
    comment?: string;
    projectId?: string;
  }) {
    if (authorId === dto.subjectId) throw new BadRequestException("Cannot review yourself");
    if (dto.rating < 1 || dto.rating > 5) throw new BadRequestException("Rating must be 1–5");

    return prisma.review.create({
      data: {
        authorId,
        subjectId: dto.subjectId,
        rating: dto.rating,
        comment: dto.comment,
        projectId: dto.projectId,
      },
    });
  }
}
