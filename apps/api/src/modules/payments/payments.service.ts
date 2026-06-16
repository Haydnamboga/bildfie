import { Injectable, NotFoundException, ForbiddenException, BadRequestException } from "@nestjs/common";
import { prisma } from "@bildfie/db";

@Injectable()
export class PaymentsService {
  async initiateMpesa(userId: string, milestoneId: string, phoneNumber: string) {
    const milestone = await this.assertMilestoneOwner(userId, milestoneId);
    if (milestone.payment) throw new BadRequestException("Payment already initiated for this milestone");

    const payment = await prisma.payment.create({
      data: {
        milestoneId,
        provider: "MPESA",
        amount: milestone.amount,
        currency: "KES",
        status: "PENDING",
      },
    });

    // TODO: call Safaricom Daraja STK Push API here with phoneNumber
    // const daraja = await darajaService.stkPush({ amount, phoneNumber, reference: payment.id });
    // await prisma.payment.update({ where: { id: payment.id }, data: { providerRef: daraja.CheckoutRequestID } });

    return { paymentId: payment.id, status: payment.status, message: "STK push initiated (sandbox)" };
  }

  async initiateStripe(userId: string, milestoneId: string) {
    const milestone = await this.assertMilestoneOwner(userId, milestoneId);
    if (milestone.payment) throw new BadRequestException("Payment already initiated for this milestone");

    const payment = await prisma.payment.create({
      data: {
        milestoneId,
        provider: "STRIPE",
        amount: milestone.amount,
        currency: "USD",
        status: "PENDING",
      },
    });

    // TODO: create Stripe PaymentIntent and return clientSecret
    // const intent = await stripe.paymentIntents.create({ amount: ..., currency: 'usd' });
    // await prisma.payment.update({ where: { id: payment.id }, data: { providerRef: intent.id } });

    return { paymentId: payment.id, status: payment.status, clientSecret: null };
  }

  async handleMpesaCallback(body: Record<string, unknown>) {
    const resultCode = (body as { Body?: { stkCallback?: { ResultCode?: number; CheckoutRequestID?: string } } })
      ?.Body?.stkCallback?.ResultCode;
    const checkoutRequestId = (body as { Body?: { stkCallback?: { CheckoutRequestID?: string } } })
      ?.Body?.stkCallback?.CheckoutRequestID;

    if (!checkoutRequestId) return { received: true };

    const payment = await prisma.payment.findFirst({ where: { providerRef: checkoutRequestId } });
    if (!payment) return { received: true };

    await prisma.payment.update({
      where: { id: payment.id },
      data: { status: resultCode === 0 ? "HELD_IN_ESCROW" : "FAILED" },
    });

    return { received: true };
  }

  async handleStripeWebhook(rawBody: string, signature: string) {
    // TODO: verify Stripe webhook signature and process events
    // const event = stripe.webhooks.constructEvent(rawBody, signature, process.env.STRIPE_WEBHOOK_SECRET!);
    return { received: true };
  }

  async getPayment(userId: string, milestoneId: string) {
    const milestone = await prisma.milestone.findUnique({
      where: { id: milestoneId },
      include: { project: { include: { team: true } }, payment: true },
    });
    if (!milestone) throw new NotFoundException("Milestone not found");
    const isMember =
      milestone.project.ownerId === userId ||
      milestone.project.team.some((m) => m.userId === userId);
    if (!isMember) throw new ForbiddenException();
    return milestone.payment;
  }

  async releasePayment(userId: string, paymentId: string) {
    const payment = await prisma.payment.findUnique({
      where: { id: paymentId },
      include: { milestone: { include: { project: true } } },
    });
    if (!payment) throw new NotFoundException("Payment not found");
    if (payment.milestone.project.ownerId !== userId) throw new ForbiddenException();
    if (payment.status !== "HELD_IN_ESCROW") throw new BadRequestException("Payment is not in escrow");

    return prisma.payment.update({
      where: { id: paymentId },
      data: { status: "RELEASED" },
    });
  }

  private async assertMilestoneOwner(userId: string, milestoneId: string) {
    const milestone = await prisma.milestone.findUnique({
      where: { id: milestoneId },
      include: { project: true, payment: true },
    });
    if (!milestone) throw new NotFoundException("Milestone not found");
    if (milestone.project.ownerId !== userId) throw new ForbiddenException();
    return milestone;
  }
}
