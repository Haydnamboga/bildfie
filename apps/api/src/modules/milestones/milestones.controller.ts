import { Body, Controller, Delete, Get, HttpCode, HttpStatus, Param, Patch, Post, UseGuards } from "@nestjs/common";
import { MilestonesService } from "./milestones.service";
import { AuthGuard } from "../../common/guards/auth.guard";
import { CurrentUser } from "../../common/decorators/current-user.decorator";
import type { SessionUser } from "@bildfie/types";

@Controller("projects/:projectId/milestones")
@UseGuards(AuthGuard)
export class MilestonesController {
  constructor(private readonly svc: MilestonesService) {}

  @Post()
  create(
    @CurrentUser() user: SessionUser,
    @Param("projectId") projectId: string,
    @Body() body: { title: string; description?: string; amount: number; dueDate?: string },
  ) {
    return this.svc.create(user.id, projectId, body);
  }

  @Get()
  findAll(@CurrentUser() user: SessionUser, @Param("projectId") projectId: string) {
    return this.svc.findAll(user.id, projectId);
  }

  @Patch(":milestoneId")
  update(
    @CurrentUser() user: SessionUser,
    @Param("projectId") projectId: string,
    @Param("milestoneId") milestoneId: string,
    @Body() body: { title?: string; description?: string; status?: string; amount?: number; dueDate?: string },
  ) {
    return this.svc.update(user.id, projectId, milestoneId, body);
  }

  @Delete(":milestoneId")
  @HttpCode(HttpStatus.NO_CONTENT)
  remove(
    @CurrentUser() user: SessionUser,
    @Param("projectId") projectId: string,
    @Param("milestoneId") milestoneId: string,
  ) {
    return this.svc.remove(user.id, projectId, milestoneId);
  }
}
