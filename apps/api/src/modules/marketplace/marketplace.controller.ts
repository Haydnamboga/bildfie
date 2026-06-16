import { Body, Controller, Get, Param, Post, Query, UseGuards } from "@nestjs/common";
import { MarketplaceService } from "./marketplace.service";
import { AuthGuard } from "../../common/guards/auth.guard";
import { CurrentUser } from "../../common/decorators/current-user.decorator";
import type { SessionUser } from "@bildfie/types";

@Controller("marketplace")
export class MarketplaceController {
  constructor(private readonly svc: MarketplaceService) {}

  @Get("professionals")
  search(
    @Query("q") q?: string,
    @Query("skill") skill?: string,
    @Query("minRate") minRate?: string,
    @Query("maxRate") maxRate?: string,
    @Query("page") page?: string,
    @Query("pageSize") pageSize?: string,
  ) {
    return this.svc.searchProfessionals({
      q,
      skill,
      minRate: minRate ? Number(minRate) : undefined,
      maxRate: maxRate ? Number(maxRate) : undefined,
      page: page ? Number(page) : undefined,
      pageSize: pageSize ? Number(pageSize) : undefined,
    });
  }

  @Get("professionals/:id")
  getProfile(@Param("id") id: string) {
    return this.svc.getProfile(id);
  }

  @Post("offers")
  @UseGuards(AuthGuard)
  createOffer(
    @CurrentUser() user: SessionUser,
    @Body() body: { toUserId: string; projectId?: string; message?: string; amount?: number },
  ) {
    return this.svc.createOffer(user.id, body);
  }

  @Post("offers/:id/accept")
  @UseGuards(AuthGuard)
  acceptOffer(@CurrentUser() user: SessionUser, @Param("id") id: string) {
    return this.svc.respondToOffer(user.id, id, "ACCEPTED");
  }

  @Post("offers/:id/decline")
  @UseGuards(AuthGuard)
  declineOffer(@CurrentUser() user: SessionUser, @Param("id") id: string) {
    return this.svc.respondToOffer(user.id, id, "DECLINED");
  }

  @Post("offers/:id/withdraw")
  @UseGuards(AuthGuard)
  withdrawOffer(@CurrentUser() user: SessionUser, @Param("id") id: string) {
    return this.svc.withdrawOffer(user.id, id);
  }

  @Get("offers")
  @UseGuards(AuthGuard)
  myOffers(@CurrentUser() user: SessionUser) {
    return this.svc.listMyOffers(user.id);
  }

  @Post("reviews")
  @UseGuards(AuthGuard)
  createReview(
    @CurrentUser() user: SessionUser,
    @Body() body: { subjectId: string; rating: number; comment?: string; projectId?: string },
  ) {
    return this.svc.createReview(user.id, body);
  }
}
