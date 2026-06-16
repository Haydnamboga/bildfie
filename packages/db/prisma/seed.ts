import { PrismaClient, Role } from "@prisma/client";
import { hashPassword } from "@bildfie/auth/server";

const prisma = new PrismaClient();

async function main() {
  const superAdminHash = await hashPassword("ChangeMe123!");
  await prisma.user.upsert({
    where: { email: "superadmin@bildfie.com" },
    update: {},
    create: {
      email: "superadmin@bildfie.com",
      passwordHash: superAdminHash,
      fullName: "Super Admin",
      role: Role.SUPER_ADMIN,
      emailVerified: true,
    },
  });

  const adminHash = await hashPassword("ChangeMe123!");
  await prisma.user.upsert({
    where: { email: "admin@bildfie.com" },
    update: {},
    create: {
      email: "admin@bildfie.com",
      passwordHash: adminHash,
      fullName: "Admin User",
      role: Role.ADMIN,
      emailVerified: true,
    },
  });

  console.log("Seed complete.");
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
