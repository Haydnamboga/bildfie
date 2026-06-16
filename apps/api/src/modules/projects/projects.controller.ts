import { Body, Controller, Delete, Get, HttpCode, HttpStatus, Param, Patch, Post, UseGuards } from "@nestjs/common";
import { ProjectsService } from "./projects.service";
import { AuthGuard } from "../../common/guards/auth.guard";
import { CurrentUser } from "../../common/decorators/current-user.decorator";
import type { SessionUser } from "@bildfie/types";

@Controller("projects")
@UseGuards(AuthGuard)
export class ProjectsController {
  constructor(private readonly svc: ProjectsService) {}

  @Post()
  create(@CurrentUser() user: SessionUser, @Body() body: { title: string; description?: string }) {
    return this.svc.create(user.id, body);
  }

  @Get()
  findAll(@CurrentUser() user: SessionUser) {
    return this.svc.findAll(user.id);
  }

  @Get(":id")
  findOne(@CurrentUser() user: SessionUser, @Param("id") id: string) {
    return this.svc.findOne(user.id, id);
  }

  @Patch(":id")
  update(
    @CurrentUser() user: SessionUser,
    @Param("id") id: string,
    @Body() body: { title?: string; description?: string; status?: string },
  ) {
    return this.svc.update(user.id, id, body);
  }

  @Delete(":id")
  @HttpCode(HttpStatus.NO_CONTENT)
  remove(@CurrentUser() user: SessionUser, @Param("id") id: string) {
    return this.svc.remove(user.id, id);
  }
}
