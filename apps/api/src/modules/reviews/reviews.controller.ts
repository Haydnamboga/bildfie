import { Controller, Get, Param } from "@nestjs/common";
import { ReviewsService } from "./reviews.service";

@Controller("reviews")
export class ReviewsController {
  constructor(private readonly svc: ReviewsService) {}

  @Get("users/:userId")
  findBySubject(@Param("userId") userId: string) {
    return this.svc.findBySubject(userId);
  }
}
