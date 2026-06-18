import { Body, Controller, Get, Param, Post, UseGuards } from "@nestjs/common";
import { DailyLogsService } from "./daily-logs.service";
import { AuthGuard } from "../../common/guards/auth.guard";
import { CurrentUser } from "../../common/decorators/current-user.decorator";
import { CreateDailyLogDto } from "./dto/create-daily-log.dto";
import type { SessionUser } from "@bildfie/types";

@Controller("projects/:projectId/daily-logs")
@UseGuards(AuthGuard)
export class DailyLogsController {
  constructor(private readonly svc: DailyLogsService) {}

  @Get()
  list(@CurrentUser() user: SessionUser, @Param("projectId") projectId: string) {
    return this.svc.list(user.id, projectId);
  }

  @Post()
  create(
    @CurrentUser() user: SessionUser,
    @Param("projectId") projectId: string,
    @Body() dto: CreateDailyLogDto,
  ) {
    return this.svc.create(user.id, projectId, dto);
  }
}
