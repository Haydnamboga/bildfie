import { Body, Controller, Get, Param, Patch, Query, UseGuards } from "@nestjs/common";
import { SystemService } from "./system.service";
import { AuthGuard } from "../../common/guards/auth.guard";
import { RolesGuard } from "../../common/guards/roles.guard";
import { Roles } from "../../common/decorators/roles.decorator";

@Controller("system")
@UseGuards(AuthGuard, RolesGuard)
@Roles("SUPER_ADMIN")
export class SystemController {
  constructor(private readonly svc: SystemService) {}

  @Get("health")
  health() {
    return this.svc.getHealth();
  }

  @Get("admins")
  listAdmins() {
    return this.svc.listAdmins();
  }

  @Patch("admins/:id/promote")
  promote(@Param("id") id: string) {
    return this.svc.promoteToSuperAdmin(id);
  }

  @Get("audit")
  auditLog(@Query("page") page?: string, @Query("pageSize") pageSize?: string) {
    return this.svc.getFullAuditLog({
      page: page ? Number(page) : undefined,
      pageSize: pageSize ? Number(pageSize) : undefined,
    });
  }
}
