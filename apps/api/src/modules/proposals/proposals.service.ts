import { Injectable, NotFoundException, ForbiddenException, BadRequestException, ConflictException } from "@nestjs/common";
import { prisma } from "@bildfie/db";
import { JobPostStatus, ProposalStatus } from "@prisma/client";
import type { CreateProposalDto } from "./dto/create-proposal.dto";

@Injectable()
export class ProposalsService {
  async submit(proId: string, jobPostId: string, dto: CreateProposalDto) {
    const job = await prisma.jobPost.findUnique({ where: { id: jobPostId } });
    if (!job) throw new NotFoundException("Job post not found");
    if (job.status !== JobPostStatus.OPEN) throw new BadRequestException("Job post is not open for proposals");

    const existing = await prisma.proposal.findFirst({
      where: { proId, jobPostId },
    });
    if (existing) throw new ConflictException("You have already submitted a proposal for this job");

    return prisma.proposal.create({
      data: {
        coverLetter: dto.coverLetter,
        amount: dto.amount,
        timeline: dto.timeline,
        proId,
        jobPostId,
      },
    });
  }

  async list(proId: string) {
    return prisma.proposal.findMany({
      where: { proId },
      include: {
        jobPost: {
          select: {
            id: true,
            title: true,
            category: true,
            location: true,
            status: true,
            budgetMin: true,
            budgetMax: true,
            client: { select: { id: true, fullName: true } },
          },
        },
      },
      orderBy: { createdAt: "desc" },
    });
  }

  async respond(clientId: string, proposalId: string, action: "shortlist" | "accept" | "reject") {
    const proposal = await prisma.proposal.findUnique({
      where: { id: proposalId },
      include: { jobPost: true },
    });
    if (!proposal) throw new NotFoundException("Proposal not found");
    if (proposal.jobPost.clientId !== clientId) throw new ForbiddenException("You do not own this job post");

    let newStatus: ProposalStatus;
    if (action === "shortlist") {
      newStatus = ProposalStatus.SHORTLISTED;
    } else if (action === "accept") {
      newStatus = ProposalStatus.ACCEPTED;
    } else if (action === "reject") {
      newStatus = ProposalStatus.REJECTED;
    } else {
      throw new BadRequestException("Invalid action. Must be 'shortlist', 'accept', or 'reject'");
    }

    if (action === "accept") {
      // Accept this proposal, reject all other PENDING proposals for the same job, and mark the job as AWARDED
      await prisma.$transaction([
        prisma.proposal.update({
          where: { id: proposalId },
          data: { status: ProposalStatus.ACCEPTED },
        }),
        prisma.proposal.updateMany({
          where: {
            jobPostId: proposal.jobPostId,
            id: { not: proposalId },
            status: ProposalStatus.PENDING,
          },
          data: { status: ProposalStatus.REJECTED },
        }),
        prisma.jobPost.update({
          where: { id: proposal.jobPostId },
          data: { status: JobPostStatus.AWARDED },
        }),
      ]);

      return prisma.proposal.findUnique({ where: { id: proposalId } });
    }

    return prisma.proposal.update({
      where: { id: proposalId },
      data: { status: newStatus },
    });
  }

  async withdraw(proId: string, proposalId: string) {
    const proposal = await prisma.proposal.findUnique({ where: { id: proposalId } });
    if (!proposal) throw new NotFoundException("Proposal not found");
    if (proposal.proId !== proId) throw new ForbiddenException("You do not own this proposal");
    if (proposal.status !== ProposalStatus.PENDING) {
      throw new BadRequestException("Only PENDING proposals can be withdrawn");
    }

    return prisma.proposal.update({
      where: { id: proposalId },
      data: { status: ProposalStatus.WITHDRAWN },
    });
  }
}
