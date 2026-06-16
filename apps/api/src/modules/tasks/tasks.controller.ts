import { Body, Controller, Delete, Get, HttpCode, HttpStatus, Param, Patch, Post, UseGuards } from "@nestjs/common";
import { TasksService } from "./tasks.service";
import { AuthGuard } from "../../common/guards/auth.guard";
import { CurrentUser } from "../../common/decorators/current-user.decorator";
import type { SessionUser } from "@bildfie/types";

@Controller("projects/:projectId/tasks")
@UseGuards(AuthGuard)
export class TasksController {
  constructor(private readonly svc: TasksService) {}

  @Post()
  create(
    @CurrentUser() user: SessionUser,
    @Param("projectId") projectId: string,
    @Body() body: { title: string; description?: string; milestoneId?: string; assigneeId?: string; dueDate?: string },
  ) {
    return this.svc.create(user.id, projectId, body);
  }

  @Get()
  findAll(@CurrentUser() user: SessionUser, @Param("projectId") projectId: string) {
    return this.svc.findAll(user.id, projectId);
  }

  @Patch(":taskId")
  update(
    @CurrentUser() user: SessionUser,
    @Param("projectId") projectId: string,
    @Param("taskId") taskId: string,
    @Body() body: { title?: string; description?: string; status?: string; assigneeId?: string; milestoneId?: string; dueDate?: string },
  ) {
    return this.svc.update(user.id, projectId, taskId, body);
  }

  @Delete(":taskId")
  @HttpCode(HttpStatus.NO_CONTENT)
  remove(
    @CurrentUser() user: SessionUser,
    @Param("projectId") projectId: string,
    @Param("taskId") taskId: string,
  ) {
    return this.svc.remove(user.id, projectId, taskId);
  }
}
