import { apiRequest, setCsrfToken, ApiResponse } from "./client";
import { AuthUser } from "../context/LmsContext";

export interface LoginRequest {
  email: string;
  password: string;
}

export interface CsrfTokenResponse {
  csrf_token: string;
}

/**
 * Fetch fresh CSRF token from PHP backend session
 */
export async function getCsrfTokenApi(): Promise<string> {
  const response = await apiRequest<CsrfTokenResponse>("/auth/csrf");
  const token = response.data?.csrf_token || "";
  if (token) {
    setCsrfToken(token);
  }
  return token;
}

/**
 * Authenticate user with PHP MVC Backend
 * POST /api/v1/auth/login
 */
export async function loginApi(credentials: LoginRequest): Promise<AuthUser> {
  // Ensure we have a valid CSRF token first
  try {
    await getCsrfTokenApi();
  } catch {
    // Proceed if CSRF fetch fails; apiRequest will attempt without token or rely on backend error
  }

  const response = await apiRequest<AuthUser>("/auth/login", {
    method: "POST",
    body: JSON.stringify(credentials),
  });

  return response.data;
}

/**
 * Fetch currently authenticated profile from PHP session
 * GET /api/v1/auth/me
 */
export async function getMeApi(): Promise<AuthUser> {
  const response = await apiRequest<AuthUser>("/auth/me");
  return response.data;
}

/**
 * Terminate PHP session and invalidate auth cookies
 * POST /api/v1/auth/logout
 */
export async function logoutApi(): Promise<void> {
  try {
    await apiRequest("/auth/logout", {
      method: "POST",
    });
  } finally {
    setCsrfToken(null);
  }
}
