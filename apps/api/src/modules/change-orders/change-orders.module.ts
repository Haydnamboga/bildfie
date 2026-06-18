import { Module } from "@nestjs/common";
import { ChangeOrdersController } from "./change-orders.controller";
import { ChangeOrdersService } from "./change-orders.service";

@Module({
  controllers: [ChangeOrdersController],
  providers: [ChangeOrdersService],
})
export class ChangeOrdersModule {}
