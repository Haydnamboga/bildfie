// Seed script — creates baseline accounts for local development.
// Replace the placeholder password hashing with @bildfie/auth once wired up.
import { PrismaClient, Role } from "@prisma/client";

const prisma = new PrismaClient();

async function main() {
  // NOTE: passwordHash here is a placeholder. Hash real passwords via @bildfie/auth.
  await prisma.user.upsert({
    where: { email: "superadmin@bildfie.com" },
    update: {},
    create: {
      email: "superadmin@bildfie.com",
      passwordHash: "REPLACE_WITH_HASH",
      fullName: "Super Admin",
      role: Role.SUPER_ADMIN,
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
