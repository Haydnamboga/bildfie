import { Body, Controller, Get, Param, Patch, Query, UseGuards } from "@nestjs/common";
import { AdminService } from "./admin.service";
import { AuthGuard } from "../../common/guards/auth.guard";
import { RolesGuard } from "../../common/guards/roles.guard";
import { Roles } from "../../common/decorators/roles.decorator";
import { CurrentUser } from "../../common/decorators/current-user.decorator";
import type { SessionUser } from "@bildfie/types";

@Controller("admin")
@UseGuards(AuthGuard, RolesGuard)
@Roles("ADMIN")
export class AdminController {
  constructor(private readonly svc: AdminService) {}

  @Get("stats")
  stats() {
    return this.svc.getStats();
  }

  @Get("users")
  listUsers(
    @Query("page") page?: string,
    @Query("pageSize") pageSize?: string,
    @Query("role") role?: string,
    @Query("q") q?: string,
  ) {
    return this.svc.listUsers({
      page: page ? Number(page) : undefined,
      pageSize: pageSize ? Number(pageSize) : undefined,
      role,
      q,
    });
  }

  @Patch("users/:id/role")
  changeRole(
    @CurrentUser() actor: SessionUser,
    @Param("id") id: string,
    @Body() body: { role: string },
  ) {
    return this.svc.changeUserRole(actor, id, body.role);
  }

  @Get("projects")
  listProjects(
    @Query("page") page?: string,
    @Query("pageSize") pageSize?: string,
    @Query("status") status?: string,
  ) {
    return this.svc.listProjects({
      page: page ? Number(page) : undefined,
      pageSize: pageSize ? Number(pageSize) : undefined,
      status,
    });
  }

  @Get("audit")
  auditLogs(
    @Query("page") page?: string,
    @Query("pageSize") pageSize?: string,
    @Query("actorId") actorId?: string,
  ) {
    return this.svc.getAuditLogs({
      page: page ? Number(page) : undefined,
      pageSize: pageSize ? Number(pageSize) : undefined,
      actorId,
    });
  }
}
