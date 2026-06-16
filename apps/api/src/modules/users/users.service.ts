import { Injectable, NotFoundException } from "@nestjs/common";
import { prisma } from "@bildfie/db";

const PUBLIC_SELECT = {
  id: true,
  email: true,
  fullName: true,
  role: true,
  headline: true,
  bio: true,
  skills: true,
  hourlyRate: true,
  emailVerified: true,
  mfaEnabled: true,
  createdAt: true,
} as const;

@Injectable()
export class UsersService {
  async findById(id: string) {
    const user = await prisma.user.findUnique({ where: { id }, select: PUBLIC_SELECT });
    if (!user) throw new NotFoundException("User not found");
    return user;
  }

  async search(q: string) {
    return prisma.user.findMany({
      where: {
        OR: [
          { fullName: { contains: q, mode: "insensitive" } },
          { headline: { contains: q, mode: "insensitive" } },
          { skills: { has: q } },
        ],
        role: "USER",
        emailVerified: true,
      },
      select: {
        id: true,
        fullName: true,
        headline: true,
        bio: true,
        skills: true,
        hourlyRate: true,
      },
      take: 20,
    });
  }

  async updateProfile(
    userId: string,
    data: {
      fullName?: string;
      headline?: string;
      bio?: string;
      skills?: string[];
      hourlyRate?: number;
    },
  ) {
    return prisma.user.update({
      where: { id: userId },
      data,
      select: PUBLIC_SELECT,
    });
  }
}
