import { Injectable } from "@nestjs/common";
import { prisma } from "@bildfie/db";

@Injectable()
export class NotificationsService {
  async list(userId: string, unreadOnly = false) {
    return prisma.notification.findMany({
      where: { userId, ...(unreadOnly ? { read: false } : {}) },
      orderBy: { createdAt: "desc" },
      take: 50,
    });
  }

  async markRead(userId: string, notificationId: string) {
    return prisma.notification.updateMany({
      where: { id: notificationId, userId },
      data: { read: true },
    });
  }

  async markAllRead(userId: string) {
    return prisma.notification.updateMany({
      where: { userId, read: false },
      data: { read: true },
    });
  }

  async create(userId: string, type: string, title: string, body?: string) {
    return prisma.notification.create({
      data: { userId, type, title, body },
    });
  }
}
