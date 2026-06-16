import * as jwt from "jsonwebtoken";
import * as bcrypt from "bcryptjs";
import { authenticator } from "otplib";
import type { JwtPayload } from "@bildfie/types";

function jwtSecret(): string {
  const s = process.env.JWT_SECRET;
  if (!s) throw new Error("JWT_SECRET not set");
  return s;
}

function refreshSecret(): string {
  const s = process.env.REFRESH_TOKEN_SECRET;
  if (!s) throw new Error("REFRESH_TOKEN_SECRET not set");
  return s;
}

export function signAccessToken(payload: JwtPayload): string {
  const { sub, ...rest } = payload;
  return jwt.sign(rest, jwtSecret(), {
    subject: sub,
    expiresIn: (process.env.JWT_EXPIRES_IN ?? "15m") as string,
  });
}

export function signRefreshToken(sub: string): string {
  return jwt.sign({}, refreshSecret(), {
    subject: sub,
    expiresIn: (process.env.REFRESH_TOKEN_EXPIRES_IN ?? "7d") as string,
  });
}

export function verifyAccessToken(token: string): JwtPayload {
  const decoded = jwt.verify(token, jwtSecret()) as jwt.JwtPayload;
  return {
    sub: decoded.sub as string,
    email: decoded.email as string,
    fullName: decoded.fullName as string,
    role: decoded.role,
    mfaVerified: decoded.mfaVerified as boolean,
  };
}

export function verifyRefreshToken(token: string): { sub: string } {
  const decoded = jwt.verify(token, refreshSecret()) as jwt.JwtPayload;
  return { sub: decoded.sub as string };
}

const BCRYPT_ROUNDS = 12;

export function hashPassword(plain: string): Promise<string> {
  return bcrypt.hash(plain, BCRYPT_ROUNDS);
}

export function comparePassword(plain: string, hash: string): Promise<boolean> {
  return bcrypt.compare(plain, hash);
}

export function generateMfaSecret(): string {
  return authenticator.generateSecret();
}

export function getMfaUri(secret: string, email: string): string {
  return authenticator.keyuri(email, process.env.MFA_ISSUER ?? "bildfie", secret);
}

export function verifyMfaToken(secret: string, token: string): boolean {
  return authenticator.verify({ token, secret });
}
