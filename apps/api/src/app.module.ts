import { Module } from "@nestjs/common";
import { HealthController } from "./health.controller";

// Business-capability modules (build out per §10 order).
import { AuthModule } from "./modules/auth/auth.module";
import { UsersModule } from "./modules/users/users.module";
import { MarketplaceModule } from "./modules/marketplace/marketplace.module";
import { ProjectsModule } from "./modules/projects/projects.module";
import { TeamsModule } from "./modules/teams/teams.module";
import { TasksModule } from "./modules/tasks/tasks.module";
import { MilestonesModule } from "./modules/milestones/milestones.module";
import { PaymentsModule } from "./modules/payments/payments.module";
import { MessagingModule } from "./modules/messaging/messaging.module";
import { ReviewsModule } from "./modules/reviews/reviews.module";
import { NotificationsModule } from "./modules/notifications/notifications.module";
import { AdminModule } from "./modules/admin/admin.module";
import { SystemModule } from "./modules/system/system.module";
import { AuditModule } from "./modules/audit/audit.module";

@Module({
  imports: [
    AuthModule,
    UsersModule,
    MarketplaceModule,
    ProjectsModule,
    TeamsModule,
    TasksModule,
    MilestonesModule,
    PaymentsModule,
    MessagingModule,
    ReviewsModule,
    NotificationsModule,
    AdminModule,
    SystemModule,
    AuditModule,
  ],
  controllers: [HealthController],
})
export class AppModule {}
