// Shared TypeScript types / DTOs used by web + mobile + api.
// Keep these framework-agnostic — no React, no Nest imports here.

export type UserRole = "USER" | "ADMIN" | "SUPER_ADMIN";

export interface SessionUser {
  id: string;
  email: string;
  fullName: string;
  role: UserRole;
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
