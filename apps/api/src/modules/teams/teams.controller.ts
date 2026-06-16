import { Body, Controller, Delete, Get, HttpCode, HttpStatus, Param, Post, UseGuards } from "@nestjs/common";
import { TeamsService } from "./teams.service";
import { AuthGuard } from "../../common/guards/auth.guard";
import { CurrentUser } from "../../common/decorators/current-user.decorator";
import type { SessionUser } from "@bildfie/types";

@Controller("projects/:projectId/team")
@UseGuards(AuthGuard)
export class TeamsController {
  constructor(private readonly svc: TeamsService) {}

  @Get()
  list(@CurrentUser() user: SessionUser, @Param("projectId") projectId: string) {
    return this.svc.list(user.id, projectId);
  }

  @Post()
  invite(
    @CurrentUser() user: SessionUser,
    @Param("projectId") projectId: string,
    @Body() body: { userId: string; roleLabel?: string },
  ) {
    return this.svc.invite(user.id, projectId, body);
  }

  @Delete(":memberId")
  @HttpCode(HttpStatus.NO_CONTENT)
  remove(
    @CurrentUser() user: SessionUser,
    @Param("projectId") projectId: string,
    @Param("memberId") memberId: string,
  ) {
    return this.svc.remove(user.id, projectId, memberId);
  }
}
