import { Body, Controller, Get, Param, Patch, Post, UseGuards } from "@nestjs/common";
import { ChangeOrdersService } from "./change-orders.service";
import { AuthGuard } from "../../common/guards/auth.guard";
import { CurrentUser } from "../../common/decorators/current-user.decorator";
import { CreateChangeOrderDto } from "./dto/create-change-order.dto";
import type { SessionUser } from "@bildfie/types";

@Controller("projects/:projectId/change-orders")
@UseGuards(AuthGuard)
export class ChangeOrdersController {
  constructor(private readonly svc: ChangeOrdersService) {}

  @Get()
  list(@CurrentUser() user: SessionUser, @Param("projectId") projectId: string) {
    return this.svc.list(user.id, projectId);
  }

  @Post()
  create(
    @CurrentUser() user: SessionUser,
    @Param("projectId") projectId: string,
    @Body() dto: CreateChangeOrderDto,
  ) {
    return this.svc.create(user.id, projectId, dto);
  }

  @Patch(":id/respond")
  respond(
    @CurrentUser() user: SessionUser,
    @Param("id") id: string,
    @Body() body: { action: "approve" | "reject" },
  ) {
    return this.svc.respond(user.id, id, body.action);
  }
}
