export type UserRole = "USER" | "ADMIN" | "SUPER_ADMIN";

export interface SessionUser {
  id: string;
  email: string;
  fullName: string;
  role: UserRole;
  mfaVerified?: boolean;
}

export interface ApiError {
  statusCode: number;
  message: string;
  error?: string;
}

export type Paginated<T> = {
  data: T[];
  total: number;
  page: number;
  pageSize: number;
};

export interface JwtPayload {
  sub: string;
  email: string;
  fullName: string;
  role: UserRole;
  mfaVerified: boolean;
}

export interface TokenPair {
  accessToken: string;
  refreshToken: string;
}

export interface AuthResponse {
  user: SessionUser;
  accessToken: string;
  refreshToken: string;
}
