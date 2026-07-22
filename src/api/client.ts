/**
 * Centralized API Client for Gilgil TVC LMS
 * Handles Base API URL, credentials/cookies, CSRF double-submit tokens,
 * standard JSON requests/responses, and structured error handling.
 */

export class ApiError extends Error {
  public status: number;
  public data: any;

  constructor(message: string, status: number, data?: any) {
    super(message);
    this.name = "ApiError";
    this.status = status;
    this.data = data;
  }

  get isUnauthorized(): boolean {
    return this.status === 401;
  }

  get isForbidden(): boolean {
    return this.status === 403;
  }

  get isNotFound(): boolean {
    return this.status === 404;
  }

  get isValidationError(): boolean {
    return this.status === 422;
  }

  get isRateLimited(): boolean {
    return this.status === 429;
  }

  get isServerError(): boolean {
    return this.status >= 500;
  }
}

// Global CSRF Token cache for state-changing HTTP requests
let activeCsrfToken: string | null = null;

export function setCsrfToken(token: string | null): void {
  activeCsrfToken = token;
}

export function getCsrfToken(): string | null {
  return activeCsrfToken;
}

// Global listener for session expiration (401 Unauthorized)
type SessionExpiredHandler = () => void;
let sessionExpiredHandler: SessionExpiredHandler | null = null;

export function onSessionExpired(handler: SessionExpiredHandler): void {
  sessionExpiredHandler = handler;
}

// Resolve Base URL from environment or default to relative /api/v1
export const API_BASE_URL = import.meta.env.VITE_API_URL || "/api/v1";

export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data: T;
  timestamp?: string;
  errors?: Record<string, string[]>;
}

/**
 * Perform an authenticated JSON API request
 */
export async function apiRequest<T = any>(
  endpoint: string,
  options: RequestInit = {}
): Promise<ApiResponse<T>> {
  // Normalize URL path
  const normalizedEndpoint = endpoint.startsWith("/") ? endpoint : `/${endpoint}`;
  const url = normalizedEndpoint.startsWith("/api") ? normalizedEndpoint : `${API_BASE_URL}${normalizedEndpoint}`;

  const method = (options.method || "GET").toUpperCase();
  const headers = new Headers(options.headers || {});

  // Set default JSON Content-Type
  if (!headers.has("Content-Type") && options.body && typeof options.body === "string") {
    headers.set("Content-Type", "application/json");
  }

  headers.set("Accept", "application/json");

  // Attach CSRF token for state-changing HTTP methods
  if (["POST", "PUT", "DELETE", "PATCH"].includes(method) && activeCsrfToken) {
    headers.set("X-CSRF-Token", activeCsrfToken);
  }

  const fetchOptions: RequestInit = {
    ...options,
    method,
    headers,
    credentials: "include", // Ensure PHP PHPSESSID cookie is sent & received
  };

  let response: Response;
  try {
    response = await fetch(url, fetchOptions);
  } catch (err: any) {
    throw new ApiError(
      "Network error: Unable to connect to backend server. Please verify PHP/MySQL connection.",
      0,
      { originalError: err.message }
    );
  }

  let json: ApiResponse<T> | null = null;
  const contentType = response.headers.get("content-type");
  if (contentType && contentType.includes("application/json")) {
    try {
      json = await response.json();
    } catch {
      json = null;
    }
  }

  if (!response.ok) {
    const status = response.status;
    const errorMessage = json?.message || response.statusText || `HTTP Error ${status}`;

    if (status === 401 && sessionExpiredHandler) {
      sessionExpiredHandler();
    }

    throw new ApiError(errorMessage, status, json?.data || json);
  }

  if (json && json.success === false) {
    throw new ApiError(json.message || "API request failed", response.status, json.data);
  }

  return json || {
    success: true,
    message: "Request succeeded",
    data: {} as T,
  };
}

export const apiClient = {
  get: async <T = any>(endpoint: string): Promise<T> => {
    const res = await apiRequest<T>(endpoint, { method: "GET" });
    return res.data;
  },
  post: async <T = any>(endpoint: string, body?: any): Promise<T> => {
    const isFormData = typeof FormData !== "undefined" && body instanceof FormData;
    const res = await apiRequest<T>(endpoint, {
      method: "POST",
      body: isFormData ? body : (body !== undefined ? JSON.stringify(body) : undefined),
    });
    return res.data;
  },
  put: async <T = any>(endpoint: string, body?: any): Promise<T> => {
    const isFormData = typeof FormData !== "undefined" && body instanceof FormData;
    const res = await apiRequest<T>(endpoint, {
      method: "PUT",
      body: isFormData ? body : (body !== undefined ? JSON.stringify(body) : undefined),
    });
    return res.data;
  },
  delete: async <T = any>(endpoint: string): Promise<T> => {
    const res = await apiRequest<T>(endpoint, { method: "DELETE" });
    return res.data;
  },
};
