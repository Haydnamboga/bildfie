import { Injectable, NotFoundException } from "@nestjs/common";
import { prisma } from "@bildfie/db";

@Injectable()
export class ReviewsService {
  async findBySubject(subjectId: string) {
    const user = await prisma.user.findUnique({ where: { id: subjectId } });
    if (!user) throw new NotFoundException("User not found");

    const reviews = await prisma.review.findMany({
      where: { subjectId },
      include: { author: { select: { id: true, fullName: true } } },
      orderBy: { createdAt: "desc" },
    });

    const avg =
      reviews.length > 0
        ? reviews.reduce((s, r) => s + r.rating, 0) / reviews.length
        : null;

    return { reviews, avgRating: avg, total: reviews.length };
  }
}
