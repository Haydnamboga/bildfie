import { Module } from "@nestjs/common";
import { APP_INTERCEPTOR } from "@nestjs/core";
import { HealthController } from "./health.controller";
import { AuditInterceptor } from "./common/interceptors/audit.interceptor";

import { AuditModule } from "./modules/audit/audit.module";
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

@Module({
  imports: [
    AuditModule,
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
  ],
  controllers: [HealthController],
  providers: [
    {
      provide: APP_INTERCEPTOR,
      useClass: AuditInterceptor,
    },
  ],
})
export class AppModule {}
