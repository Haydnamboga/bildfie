import { z } from "zod";

export const signupSchema = z.object({
  email: z.string().email(),
  password: z.string().min(8),
  fullName: z.string().min(1),
});
export type SignupInput = z.infer<typeof signupSchema>;

export const loginSchema = z.object({
  email: z.string().email(),
  password: z.string().min(1),
  mfaToken: z.string().optional(),
});
export type LoginInput = z.infer<typeof loginSchema>;

export const refreshSchema = z.object({
  refreshToken: z.string(),
});
export type RefreshInput = z.infer<typeof refreshSchema>;

export const changePasswordSchema = z.object({
  currentPassword: z.string().min(1),
  newPassword: z.string().min(8),
});
export type ChangePasswordInput = z.infer<typeof changePasswordSchema>;

export const updateProfileSchema = z.object({
  fullName: z.string().min(1).optional(),
  headline: z.string().max(120).optional(),
  bio: z.string().max(2000).optional(),
  skills: z.array(z.string()).optional(),
  hourlyRate: z.number().positive().optional(),
});
export type UpdateProfileInput = z.infer<typeof updateProfileSchema>;

export const mfaVerifySchema = z.object({
  token: z.string().length(6),
});
export type MfaVerifyInput = z.infer<typeof mfaVerifySchema>;

export const createProjectSchema = z.object({
  title: z.string().min(1),
  description: z.string().optional(),
});
export type CreateProjectInput = z.infer<typeof createProjectSchema>;

export const createOfferSchema = z.object({
  toUserId: z.string(),
  projectId: z.string().optional(),
  message: z.string().optional(),
  amount: z.number().positive().optional(),
});
export type CreateOfferInput = z.infer<typeof createOfferSchema>;

export const createTaskSchema = z.object({
  title: z.string().min(1),
  description: z.string().optional(),
  milestoneId: z.string().optional(),
  assigneeId: z.string().optional(),
  dueDate: z.string().datetime().optional(),
});
export type CreateTaskInput = z.infer<typeof createTaskSchema>;

export const createMilestoneSchema = z.object({
  title: z.string().min(1),
  description: z.string().optional(),
  amount: z.number().positive(),
  dueDate: z.string().datetime().optional(),
});
export type CreateMilestoneInput = z.infer<typeof createMilestoneSchema>;
