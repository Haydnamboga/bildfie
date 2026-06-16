import { Body, Controller, Get, Param, Patch, Query, UseGuards } from "@nestjs/common";
import { UsersService } from "./users.service";
import { AuthGuard } from "../../common/guards/auth.guard";
import { CurrentUser } from "../../common/decorators/current-user.decorator";
import type { SessionUser } from "@bildfie/types";

@Controller("users")
@UseGuards(AuthGuard)
export class UsersController {
  constructor(private readonly usersService: UsersService) {}

  @Get("search")
  search(@Query("q") q: string) {
    return this.usersService.search(q ?? "");
  }

  @Get("me")
  getMe(@CurrentUser() user: SessionUser) {
    return this.usersService.findById(user.id);
  }

  @Patch("me")
  updateMe(
    @CurrentUser() user: SessionUser,
    @Body() body: Parameters<UsersService["updateProfile"]>[1],
  ) {
    return this.usersService.updateProfile(user.id, body);
  }

  @Get(":id")
  findById(@Param("id") id: string) {
    return this.usersService.findById(id);
  }
}
