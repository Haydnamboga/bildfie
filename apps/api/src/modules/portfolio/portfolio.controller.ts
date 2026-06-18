import { Body, Controller, Delete, Get, HttpCode, HttpStatus, Param, Post, UseGuards } from "@nestjs/common";
import { PortfolioService } from "./portfolio.service";
import { AuthGuard } from "../../common/guards/auth.guard";
import { CurrentUser } from "../../common/decorators/current-user.decorator";
import { CreatePortfolioItemDto } from "./dto/create-portfolio-item.dto";
import type { SessionUser } from "@bildfie/types";

@Controller()
export class PortfolioController {
  constructor(private readonly svc: PortfolioService) {}

  @Get("users/:userId/portfolio")
  getPortfolio(@Param("userId") userId: string) {
    return this.svc.getPortfolio(userId);
  }

  @Post("portfolio")
  @UseGuards(AuthGuard)
  addItem(@CurrentUser() user: SessionUser, @Body() dto: CreatePortfolioItemDto) {
    return this.svc.addItem(user.id, dto);
  }

  @Delete("portfolio/:id")
  @UseGuards(AuthGuard)
  @HttpCode(HttpStatus.NO_CONTENT)
  deleteItem(@CurrentUser() user: SessionUser, @Param("id") id: string) {
    return this.svc.deleteItem(user.id, id);
  }
}
