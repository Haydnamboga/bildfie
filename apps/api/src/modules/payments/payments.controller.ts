import { Body, Controller, Get, HttpCode, HttpStatus, Param, Post, UseGuards, Headers, RawBodyRequest, Req } from "@nestjs/common";
import { PaymentsService } from "./payments.service";
import { AuthGuard } from "../../common/guards/auth.guard";
import { CurrentUser } from "../../common/decorators/current-user.decorator";
import type { SessionUser } from "@bildfie/types";
import type { Request } from "express";

@Controller("payments")
export class PaymentsController {
  constructor(private readonly svc: PaymentsService) {}

  @Post("milestones/:milestoneId/mpesa")
  @UseGuards(AuthGuard)
  initiateMpesa(
    @CurrentUser() user: SessionUser,
    @Param("milestoneId") milestoneId: string,
    @Body() body: { phoneNumber: string },
  ) {
    return this.svc.initiateMpesa(user.id, milestoneId, body.phoneNumber);
  }

  @Post("milestones/:milestoneId/stripe")
  @UseGuards(AuthGuard)
  initiateStripe(
    @CurrentUser() user: SessionUser,
    @Param("milestoneId") milestoneId: string,
  ) {
    return this.svc.initiateStripe(user.id, milestoneId);
  }

  @Post(":paymentId/release")
  @UseGuards(AuthGuard)
  release(@CurrentUser() user: SessionUser, @Param("paymentId") paymentId: string) {
    return this.svc.releasePayment(user.id, paymentId);
  }

  @Get("milestones/:milestoneId")
  @UseGuards(AuthGuard)
  getPayment(@CurrentUser() user: SessionUser, @Param("milestoneId") milestoneId: string) {
    return this.svc.getPayment(user.id, milestoneId);
  }

  @Post("callbacks/mpesa")
  @HttpCode(HttpStatus.OK)
  mpesaCallback(@Body() body: Record<string, unknown>) {
    return this.svc.handleMpesaCallback(body);
  }

  @Post("callbacks/stripe")
  @HttpCode(HttpStatus.OK)
  stripeCallback(
    @Req() req: RawBodyRequest<Request>,
    @Headers("stripe-signature") sig: string,
  ) {
    return this.svc.handleStripeWebhook(req.rawBody?.toString() ?? "", sig);
  }
}
