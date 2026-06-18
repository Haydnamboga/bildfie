import { Body, Controller, Delete, Get, Param, Patch, Post, UseGuards } from "@nestjs/common";
import { ProposalsService } from "./proposals.service";
import { AuthGuard } from "../../common/guards/auth.guard";
import { CurrentUser } from "../../common/decorators/current-user.decorator";
import type { SessionUser } from "@bildfie/types";
import { CreateProposalDto } from "./dto/create-proposal.dto";
import { IsEnum } from "class-validator";

class RespondDto {
  @IsEnum(["shortlist", "accept", "reject"])
  action: "shortlist" | "accept" | "reject";
}

@Controller()
export class ProposalsController {
  constructor(private readonly svc: ProposalsService) {}

  @Post("jobs/:jobId/proposals")
  @UseGuards(AuthGuard)
  submit(
    @CurrentUser() user: SessionUser,
    @Param("jobId") jobId: string,
    @Body() dto: CreateProposalDto,
  ) {
    return this.svc.submit(user.id, jobId, dto);
  }

  @Get("proposals/mine")
  @UseGuards(AuthGuard)
  list(@CurrentUser() user: SessionUser) {
    return this.svc.list(user.id);
  }

  @Patch("proposals/:id/respond")
  @UseGuards(AuthGuard)
  respond(
    @CurrentUser() user: SessionUser,
    @Param("id") id: string,
    @Body() body: RespondDto,
  ) {
    return this.svc.respond(user.id, id, body.action);
  }

  @Delete("proposals/:id")
  @UseGuards(AuthGuard)
  withdraw(@CurrentUser() user: SessionUser, @Param("id") id: string) {
    return this.svc.withdraw(user.id, id);
  }
}
