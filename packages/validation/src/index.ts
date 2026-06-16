// Zod schemas shared by front + back. The same schema validates a form
// in the browser and the request body in NestJS.
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
