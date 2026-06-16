import { Body, Controller, Get, Param, Post, Query, UseGuards } from "@nestjs/common";
import { MessagingService } from "./messaging.service";
import { AuthGuard } from "../../common/guards/auth.guard";
import { CurrentUser } from "../../common/decorators/current-user.decorator";
import type { SessionUser } from "@bildfie/types";

@Controller("projects/:projectId/messages")
@UseGuards(AuthGuard)
export class MessagingController {
  constructor(private readonly svc: MessagingService) {}

  @Get()
  list(
    @CurrentUser() user: SessionUser,
    @Param("projectId") projectId: string,
    @Query("page") page?: string,
    @Query("pageSize") pageSize?: string,
  ) {
    return this.svc.list(user.id, projectId, {
      page: page ? Number(page) : undefined,
      pageSize: pageSize ? Number(pageSize) : undefined,
    });
  }

  @Post()
  send(
    @CurrentUser() user: SessionUser,
    @Param("projectId") projectId: string,
    @Body() body: { body: string },
  ) {
    return this.svc.send(user.id, projectId, body.body);
  }
}
