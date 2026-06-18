import { Injectable, NotFoundException, ForbiddenException, BadRequestException } from "@nestjs/common";
import { prisma } from "@bildfie/db";
import { JobPostStatus, TradeCategory } from "@bildfie/db";
import type { CreateJobPostDto } from "./dto/create-job-post.dto";
import type { UpdateJobPostDto } from "./dto/update-job-post.dto";

@Injectable()
export class JobsService {
  async create(clientId: string, dto: CreateJobPostDto) {
    return prisma.jobPost.create({
      data: {
        title: dto.title,
        description: dto.description,
        category: dto.category,
        location: dto.location,
        budgetMin: dto.budgetMin,
        budgetMax: dto.budgetMax,
        dueDate: dto.dueDate ? new Date(dto.dueDate) : undefined,
        clientId,
      },
    });
  }

  async list(opts: { category?: TradeCategory; location?: string; status?: JobPostStatus }) {
    const { category, location, status } = opts;

    return prisma.jobPost.findMany({
      where: {
        status: status ?? JobPostStatus.OPEN,
        ...(category && { category }),
        ...(location && { location: { contains: location, mode: "insensitive" as const } }),
      },
      select: {
        id: true,
        title: true,
        description: true,
        category: true,
        location: true,
        budgetMin: true,
        budgetMax: true,
        dueDate: true,
        status: true,
        createdAt: true,
        client: {
          select: { id: true, fullName: true },
        },
        _count: {
          select: { proposals: true },
        },
      },
      orderBy: { createdAt: "desc" },
    });
  }

  async findById(id: string) {
    const job = await prisma.jobPost.findUnique({
      where: { id },
      select: {
        id: true,
        title: true,
        description: true,
        category: true,
        location: true,
        budgetMin: true,
        budgetMax: true,
        dueDate: true,
        status: true,
        createdAt: true,
        updatedAt: true,
        client: {
          select: { id: true, fullName: true },
        },
        _count: {
          select: { proposals: true },
        },
      },
    });
    if (!job) throw new NotFoundException("Job post not found");
    return job;
  }

  async findByIdForOwner(clientId: string, id: string) {
    const job = await prisma.jobPost.findUnique({
      where: { id },
      include: {
        proposals: {
          include: {
            pro: {
              select: { id: true, fullName: true, headline: true, hourlyRate: true },
            },
          },
          orderBy: { createdAt: "desc" },
        },
        client: {
          select: { id: true, fullName: true },
        },
      },
    });
    if (!job) throw new NotFoundException("Job post not found");
    if (job.clientId !== clientId) throw new ForbiddenException("You do not own this job post");
    return job;
  }

  async update(clientId: string, id: string, dto: UpdateJobPostDto) {
    const job = await prisma.jobPost.findUnique({ where: { id } });
    if (!job) throw new NotFoundException("Job post not found");
    if (job.clientId !== clientId) throw new ForbiddenException("You do not own this job post");

    return prisma.jobPost.update({
      where: { id },
      data: {
        ...(dto.title !== undefined && { title: dto.title }),
        ...(dto.description !== undefined && { description: dto.description }),
        ...(dto.category !== undefined && { category: dto.category }),
        ...(dto.location !== undefined && { location: dto.location }),
        ...(dto.budgetMin !== undefined && { budgetMin: dto.budgetMin }),
        ...(dto.budgetMax !== undefined && { budgetMax: dto.budgetMax }),
        ...(dto.dueDate !== undefined && { dueDate: new Date(dto.dueDate) }),
        ...(dto.status !== undefined && { status: dto.status }),
      },
    });
  }

  async close(clientId: string, id: string) {
    const job = await prisma.jobPost.findUnique({ where: { id } });
    if (!job) throw new NotFoundException("Job post not found");
    if (job.clientId !== clientId) throw new ForbiddenException("You do not own this job post");

    return prisma.jobPost.update({
      where: { id },
      data: { status: JobPostStatus.CLOSED },
    });
  }

  async delete(clientId: string, id: string) {
    const job = await prisma.jobPost.findUnique({ where: { id } });
    if (!job) throw new NotFoundException("Job post not found");
    if (job.clientId !== clientId) throw new ForbiddenException("You do not own this job post");

    await prisma.jobPost.delete({ where: { id } });
    return { success: true };
  }
}
