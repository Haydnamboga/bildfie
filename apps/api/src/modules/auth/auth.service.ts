import {
  BadRequestException,
  ConflictException,
  Injectable,
  NotFoundException,
  UnauthorizedException,
} from "@nestjs/common";
import { prisma } from "@bildfie/db";
import {
  hashPassword,
  comparePassword,
  signAccessToken,
  signRefreshToken,
  verifyRefreshToken,
  generateMfaSecret,
  getMfaUri,
  verifyMfaToken,
} from "@bildfie/auth/server";
import { hasRole } from "@bildfie/auth";
import type { AuthResponse, JwtPayload, SessionUser } from "@bildfie/types";
import type { RegisterDto } from "./dto/register.dto";
import type { LoginDto } from "./dto/login.dto";
import type { MfaEnableDto } from "./dto/mfa-enable.dto";

@Injectable()
export class AuthService {
  async register(dto: RegisterDto): Promise<AuthResponse> {
    const existing = await prisma.user.findUnique({ where: { email: dto.email } });
    if (existing) throw new ConflictException("Email already registered");

    const passwordHash = await hashPassword(dto.password);
    const user = await prisma.user.create({
      data: { email: dto.email, passwordHash, fullName: dto.fullName },
    });

    return this.issueTokens(user, false);
  }

  async login(dto: LoginDto): Promise<AuthResponse> {
    const user = await prisma.user.findUnique({ where: { email: dto.email } });
    if (!user) throw new UnauthorizedException("Invalid credentials");

    const valid = await comparePassword(dto.password, user.passwordHash);
    if (!valid) throw new UnauthorizedException("Invalid credentials");

    const needsMfa = user.mfaEnabled && hasRole(user.role as "USER" | "ADMIN" | "SUPER_ADMIN", "ADMIN");
    if (needsMfa) {
      if (!dto.mfaToken) throw new UnauthorizedException("MFA token required");
      if (!verifyMfaToken(user.mfaSecret!, dto.mfaToken)) {
        throw new UnauthorizedException("Invalid MFA token");
      }
    }

    return this.issueTokens(user, needsMfa);
  }

  async refresh(token: string): Promise<{ accessToken: string; refreshToken: string }> {
    let sub: string;
    try {
      ({ sub } = verifyRefreshToken(token));
    } catch {
      throw new UnauthorizedException("Invalid refresh token");
    }

    const stored = await prisma.refreshToken.findUnique({ where: { token } });
    if (!stored || stored.expiresAt < new Date()) {
      throw new UnauthorizedException("Refresh token expired or revoked");
    }

    const user = await prisma.user.findUnique({ where: { id: sub } });
    if (!user) throw new UnauthorizedException();

    await prisma.refreshToken.delete({ where: { token } });
    const result = await this.issueTokens(user, false);
    return { accessToken: result.accessToken, refreshToken: result.refreshToken };
  }

  async logout(refreshToken: string): Promise<void> {
    await prisma.refreshToken.deleteMany({ where: { token: refreshToken } });
  }

  async setupMfa(userId: string): Promise<{ secret: string; uri: string }> {
    const user = await prisma.user.findUnique({ where: { id: userId } });
    if (!user) throw new NotFoundException();

    const secret = generateMfaSecret();
    await prisma.user.update({ where: { id: userId }, data: { mfaSecret: secret } });

    return { secret, uri: getMfaUri(secret, user.email) };
  }

  async enableMfa(userId: string, dto: MfaEnableDto): Promise<void> {
    const user = await prisma.user.findUnique({ where: { id: userId } });
    if (!user || !user.mfaSecret) throw new BadRequestException("MFA not set up");

    if (!verifyMfaToken(user.mfaSecret, dto.token)) {
      throw new BadRequestException("Invalid MFA token");
    }

    await prisma.user.update({ where: { id: userId }, data: { mfaEnabled: true } });
  }

  private async issueTokens(
    user: { id: string; email: string; fullName: string; role: string },
    mfaVerified: boolean,
  ): Promise<AuthResponse> {
    const jwtPayload: JwtPayload = {
      sub: user.id,
      email: user.email,
      fullName: user.fullName,
      role: user.role as SessionUser["role"],
      mfaVerified,
    };

    const accessToken = signAccessToken(jwtPayload);
    const refreshToken = signRefreshToken(user.id);

    const expiresAt = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000);
    await prisma.refreshToken.create({
      data: { token: refreshToken, userId: user.id, expiresAt },
    });

    return {
      user: {
        id: user.id,
        email: user.email,
        fullName: user.fullName,
        role: user.role as SessionUser["role"],
        mfaVerified,
      },
      accessToken,
      refreshToken,
    };
  }
}
