import { Controller, Get, Param, Patch, Query, UseGuards } from "@nestjs/common";
import { NotificationsService } from "./notifications.service";
import { AuthGuard } from "../../common/guards/auth.guard";
import { CurrentUser } from "../../common/decorators/current-user.decorator";
import type { SessionUser } from "@bildfie/types";

@Controller("notifications")
@UseGuards(AuthGuard)
export class NotificationsController {
  constructor(private readonly svc: NotificationsService) {}

  @Get()
  list(@CurrentUser() user: SessionUser, @Query("unread") unread?: string) {
    return this.svc.list(user.id, unread === "true");
  }

  @Patch(":id/read")
  markRead(@CurrentUser() user: SessionUser, @Param("id") id: string) {
    return this.svc.markRead(user.id, id);
  }

  @Patch("read-all")
  markAllRead(@CurrentUser() user: SessionUser) {
    return this.svc.markAllRead(user.id);
  }
}
