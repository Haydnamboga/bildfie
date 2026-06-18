import type { ApiError, AuthResponse, Paginated, SessionUser } from "@bildfie/types";

export interface ApiClientOptions {
  baseUrl: string;
  getToken?: () => string | null | Promise<string | null>;
}

export class ApiClient {
  constructor(private readonly opts: ApiClientOptions) {}

  private async request<T>(path: string, init: RequestInit = {}): Promise<T> {
    const token = (await this.opts.getToken?.()) ?? null;
    const res = await fetch(`${this.opts.baseUrl}${path}`, {
      ...init,
      headers: {
        "Content-Type": "application/json",
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        ...init.headers,
      },
    });

    if (!res.ok) {
      const body = (await res.json().catch(() => ({}))) as Partial<ApiError>;
      throw Object.assign(new Error(body.message ?? res.statusText), {
        statusCode: res.status,
      });
    }
    return res.json() as Promise<T>;
  }

  // Auth
  register(body: { email: string; password: string; fullName: string }) {
    return this.request<AuthResponse>("/auth/register", { method: "POST", body: JSON.stringify(body) });
  }
  login(body: { email: string; password: string; mfaToken?: string }) {
    return this.request<AuthResponse>("/auth/login", { method: "POST", body: JSON.stringify(body) });
  }
  refresh(refreshToken: string) {
    return this.request<{ accessToken: string; refreshToken: string }>("/auth/refresh", {
      method: "POST",
      body: JSON.stringify({ refreshToken }),
    });
  }
  logout(refreshToken: string) {
    return this.request<void>("/auth/logout", { method: "POST", body: JSON.stringify({ refreshToken }) });
  }
  me() {
    return this.request<SessionUser>("/auth/me");
  }

  // Users
  getUser(id: string) {
    return this.request<SessionUser>(`/users/${id}`);
  }
  updateProfile(data: { fullName?: string; headline?: string; bio?: string; skills?: string[]; hourlyRate?: number }) {
    return this.request<SessionUser>("/users/me", { method: "PATCH", body: JSON.stringify(data) });
  }
  searchUsers(q: string) {
    return this.request<SessionUser[]>(`/users/search?q=${encodeURIComponent(q)}`);
  }

  // Marketplace
  searchProfessionals(params?: { q?: string; skill?: string; minRate?: number; maxRate?: number; category?: string; location?: string; page?: number }) {
    const qs = new URLSearchParams(Object.entries(params ?? {}).filter(([, v]) => v !== undefined).map(([k, v]) => [k, String(v)]));
    return this.request<Paginated<SessionUser>>(`/marketplace/professionals?${qs}`);
  }
  getProfessional(id: string) {
    return this.request<SessionUser>(`/marketplace/professionals/${id}`);
  }
  createOffer(body: { toUserId: string; projectId?: string; message?: string; amount?: number }) {
    return this.request<unknown>("/marketplace/offers", { method: "POST", body: JSON.stringify(body) });
  }
  acceptOffer(id: string) {
    return this.request<unknown>(`/marketplace/offers/${id}/accept`, { method: "POST" });
  }
  declineOffer(id: string) {
    return this.request<unknown>(`/marketplace/offers/${id}/decline`, { method: "POST" });
  }
  myOffers() {
    return this.request<unknown>("/marketplace/offers");
  }
  createReview(body: { subjectId: string; rating: number; comment?: string; projectId?: string }) {
    return this.request<unknown>("/marketplace/reviews", { method: "POST", body: JSON.stringify(body) });
  }

  // Projects
  createProject(body: { title: string; description?: string }) {
    return this.request<unknown>("/projects", { method: "POST", body: JSON.stringify(body) });
  }
  listProjects() {
    return this.request<unknown[]>("/projects");
  }
  getProject(id: string) {
    return this.request<unknown>(`/projects/${id}`);
  }
  updateProject(id: string, body: { title?: string; description?: string; status?: string }) {
    return this.request<unknown>(`/projects/${id}`, { method: "PATCH", body: JSON.stringify(body) });
  }
  deleteProject(id: string) {
    return this.request<void>(`/projects/${id}`, { method: "DELETE" });
  }

  // Team
  listTeam(projectId: string) {
    return this.request<unknown[]>(`/projects/${projectId}/team`);
  }
  inviteToTeam(projectId: string, body: { userId: string; roleLabel?: string }) {
    return this.request<unknown>(`/projects/${projectId}/team`, { method: "POST", body: JSON.stringify(body) });
  }
  removeFromTeam(projectId: string, memberId: string) {
    return this.request<void>(`/projects/${projectId}/team/${memberId}`, { method: "DELETE" });
  }

  // Tasks
  listTasks(projectId: string) {
    return this.request<unknown[]>(`/projects/${projectId}/tasks`);
  }
  createTask(projectId: string, body: { title: string; description?: string; milestoneId?: string; assigneeId?: string; dueDate?: string }) {
    return this.request<unknown>(`/projects/${projectId}/tasks`, { method: "POST", body: JSON.stringify(body) });
  }
  updateTask(projectId: string, taskId: string, body: Record<string, unknown>) {
    return this.request<unknown>(`/projects/${projectId}/tasks/${taskId}`, { method: "PATCH", body: JSON.stringify(body) });
  }
  deleteTask(projectId: string, taskId: string) {
    return this.request<void>(`/projects/${projectId}/tasks/${taskId}`, { method: "DELETE" });
  }

  // Milestones
  listMilestones(projectId: string) {
    return this.request<unknown[]>(`/projects/${projectId}/milestones`);
  }
  createMilestone(projectId: string, body: { title: string; amount: number; description?: string; dueDate?: string }) {
    return this.request<unknown>(`/projects/${projectId}/milestones`, { method: "POST", body: JSON.stringify(body) });
  }
  updateMilestone(projectId: string, milestoneId: string, body: Record<string, unknown>) {
    return this.request<unknown>(`/projects/${projectId}/milestones/${milestoneId}`, { method: "PATCH", body: JSON.stringify(body) });
  }
  deleteMilestone(projectId: string, milestoneId: string) {
    return this.request<void>(`/projects/${projectId}/milestones/${milestoneId}`, { method: "DELETE" });
  }

  // Messages
  listMessages(projectId: string, page?: number) {
    return this.request<unknown[]>(`/projects/${projectId}/messages${page ? `?page=${page}` : ""}`);
  }
  sendMessage(projectId: string, body: string) {
    return this.request<unknown>(`/projects/${projectId}/messages`, { method: "POST", body: JSON.stringify({ body }) });
  }

  // Jobs
  createJobPost(body: { title: string; description: string; category: string; location: string; budgetMin?: number; budgetMax?: number; dueDate?: string }) {
    return this.request<unknown>("/jobs", { method: "POST", body: JSON.stringify(body) });
  }
  listJobPosts(params?: { category?: string; location?: string; status?: string }) {
    const qs = new URLSearchParams(Object.entries(params ?? {}).filter(([, v]) => v !== undefined).map(([k, v]) => [k, String(v)]));
    return this.request<unknown[]>(`/jobs?${qs}`);
  }
  getJobPost(id: string) {
    return this.request<unknown>(`/jobs/${id}`);
  }
  getJobPostProposals(id: string) {
    return this.request<unknown[]>(`/jobs/${id}/proposals`);
  }
  updateJobPost(id: string, body: Record<string, unknown>) {
    return this.request<unknown>(`/jobs/${id}`, { method: "PATCH", body: JSON.stringify(body) });
  }
  deleteJobPost(id: string) {
    return this.request<void>(`/jobs/${id}`, { method: "DELETE" });
  }

  // Proposals
  submitProposal(jobId: string, body: { coverLetter: string; amount: number; timeline?: string }) {
    return this.request<unknown>(`/jobs/${jobId}/proposals`, { method: "POST", body: JSON.stringify(body) });
  }
  myProposals() {
    return this.request<unknown[]>("/proposals/mine");
  }
  respondToProposal(id: string, action: "shortlist" | "accept" | "reject") {
    return this.request<unknown>(`/proposals/${id}/respond`, { method: "PATCH", body: JSON.stringify({ action }) });
  }
  withdrawProposal(id: string) {
    return this.request<void>(`/proposals/${id}`, { method: "DELETE" });
  }

  // Portfolio
  getPortfolio(userId: string) {
    return this.request<unknown[]>(`/users/${userId}/portfolio`);
  }
  addPortfolioItem(body: { title: string; description?: string; imageUrl?: string; category?: string; completedAt?: string }) {
    return this.request<unknown>("/portfolio", { method: "POST", body: JSON.stringify(body) });
  }
  deletePortfolioItem(id: string) {
    return this.request<void>(`/portfolio/${id}`, { method: "DELETE" });
  }

  // Change orders
  listChangeOrders(projectId: string) {
    return this.request<unknown[]>(`/projects/${projectId}/change-orders`);
  }
  createChangeOrder(projectId: string, body: { title: string; description: string; amount: number }) {
    return this.request<unknown>(`/projects/${projectId}/change-orders`, { method: "POST", body: JSON.stringify(body) });
  }
  respondToChangeOrder(projectId: string, id: string, action: "approve" | "reject") {
    return this.request<unknown>(`/projects/${projectId}/change-orders/${id}/respond`, { method: "PATCH", body: JSON.stringify({ action }) });
  }

  // Daily logs
  listDailyLogs(projectId: string) {
    return this.request<unknown[]>(`/projects/${projectId}/daily-logs`);
  }
  createDailyLog(projectId: string, body: { logDate: string; weather?: string; workersCount?: number; notes: string }) {
    return this.request<unknown>(`/projects/${projectId}/daily-logs`, { method: "POST", body: JSON.stringify(body) });
  }

  // Milestone submission
  submitMilestone(projectId: string, milestoneId: string, notes?: string) {
    return this.request<unknown>(`/projects/${projectId}/milestones/${milestoneId}/submit`, { method: "POST", body: JSON.stringify({ notes }) });
  }
  approveMilestone(projectId: string, milestoneId: string) {
    return this.request<unknown>(`/projects/${projectId}/milestones/${milestoneId}/approve`, { method: "POST" });
  }
  requestMilestoneRevision(projectId: string, milestoneId: string) {
    return this.request<unknown>(`/projects/${projectId}/milestones/${milestoneId}/request-revision`, { method: "POST" });
  }

  health() {
    return this.request<{ status: string }>("/health");
  }
}

export function createApiClient(opts: ApiClientOptions): ApiClient {
  return new ApiClient(opts);
}
