import { Body, Controller, Delete, Get, Param, Patch, Post, Query, UseGuards } from "@nestjs/common";
import { JobsService } from "./jobs.service";
import { AuthGuard } from "../../common/guards/auth.guard";
import { CurrentUser } from "../../common/decorators/current-user.decorator";
import type { SessionUser } from "@bildfie/types";
import { CreateJobPostDto } from "./dto/create-job-post.dto";
import { UpdateJobPostDto } from "./dto/update-job-post.dto";
import { JobPostStatus, TradeCategory } from "@prisma/client";

@Controller("jobs")
export class JobsController {
  constructor(private readonly svc: JobsService) {}

  @Post()
  @UseGuards(AuthGuard)
  create(@CurrentUser() user: SessionUser, @Body() dto: CreateJobPostDto) {
    return this.svc.create(user.id, dto);
  }

  @Get()
  list(
    @Query("category") category?: TradeCategory,
    @Query("location") location?: string,
    @Query("status") status?: JobPostStatus,
  ) {
    return this.svc.list({ category, location, status });
  }

  @Get(":id")
  findById(@Param("id") id: string) {
    return this.svc.findById(id);
  }

  @Get(":id/proposals")
  @UseGuards(AuthGuard)
  findByIdForOwner(@CurrentUser() user: SessionUser, @Param("id") id: string) {
    return this.svc.findByIdForOwner(user.id, id);
  }

  @Patch(":id")
  @UseGuards(AuthGuard)
  update(@CurrentUser() user: SessionUser, @Param("id") id: string, @Body() dto: UpdateJobPostDto) {
    return this.svc.update(user.id, id, dto);
  }

  @Delete(":id")
  @UseGuards(AuthGuard)
  delete(@CurrentUser() user: SessionUser, @Param("id") id: string) {
    return this.svc.delete(user.id, id);
  }
}
