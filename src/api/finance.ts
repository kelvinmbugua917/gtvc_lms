import { apiClient } from './client';

export interface FeeStructure {
  id: number;
  program_id: number;
  program_name?: string;
  program_code?: string;
  academic_year_id: number;
  academic_year_name?: string;
  intake_id: number;
  intake_name?: string;
  intake_code?: string;
  term_semester: number;
  total_amount: number;
  description?: string;
  created_at: string;
}

export interface StudentFeeAccount {
  id?: number;
  student_id: number;
  admission_number?: string;
  first_name?: string;
  last_name?: string;
  email?: string;
  program_name?: string;
  program_code?: string;
  department_name?: string;
  total_billed: number;
  total_paid: number;
  current_balance: number;
  clearance_status: 'cleared' | 'pending' | 'blocked_exam';
  updated_at?: string;
}

export interface Invoice {
  id: number;
  invoice_number: string;
  student_id: number;
  admission_number?: string;
  first_name?: string;
  last_name?: string;
  email?: string;
  fee_structure_id?: number;
  fee_structure_description?: string;
  amount: number;
  due_date: string;
  status: 'unpaid' | 'partially_paid' | 'paid' | 'cancelled';
  created_at: string;
}

export interface Payment {
  id: number;
  transaction_reference: string;
  student_id: number;
  admission_number?: string;
  first_name?: string;
  last_name?: string;
  email?: string;
  invoice_id?: number;
  invoice_number?: string;
  amount: number;
  payment_method: 'mpesa' | 'bank_transfer' | 'cheque' | 'cash';
  payment_date: string;
  verified_by_user_id?: number;
  verifier_first_name?: string;
  verifier_last_name?: string;
  status: 'pending' | 'verified' | 'rejected';
}

export interface StudentFinancialStatement {
  account: StudentFeeAccount;
  invoices: Invoice[];
  payments: Payment[];
}

export const financeApi = {
  getFeeStructures: async (params?: { program_id?: number; academic_year_id?: number; intake_id?: number }) => {
    const query = new URLSearchParams(params as any).toString();
    return apiClient.get<FeeStructure[]>(`/api/v1/finance/fee-structures${query ? `?${query}` : ''}`);
  },

  createFeeStructure: async (data: Partial<FeeStructure>) => {
    return apiClient.post<{ message: string; id: number }>('/api/v1/finance/fee-structures', data);
  },

  updateFeeStructure: async (id: number, data: Partial<FeeStructure>) => {
    return apiClient.put<{ message: string; id: number }>(`/api/v1/finance/fee-structures/${id}`, data);
  },

  deleteFeeStructure: async (id: number) => {
    return apiClient.delete<{ message: string; id: number }>(`/api/v1/finance/fee-structures/${id}`);
  },

  getStudentAccounts: async (params?: { clearance_status?: string; search?: string }) => {
    const query = new URLSearchParams(params as any).toString();
    return apiClient.get<StudentFeeAccount[]>(`/api/v1/finance/student-accounts${query ? `?${query}` : ''}`);
  },

  getStudentAccountById: async (studentId: number) => {
    return apiClient.get<StudentFinancialStatement>(`/api/v1/finance/student-accounts/${studentId}`);
  },

  getInvoices: async (params?: { student_id?: number; status?: string; search?: string }) => {
    const query = new URLSearchParams(params as any).toString();
    return apiClient.get<Invoice[]>(`/api/v1/finance/invoices${query ? `?${query}` : ''}`);
  },

  createInvoice: async (data: { student_id: number; fee_structure_id?: number; amount: number; due_date: string }) => {
    return apiClient.post<{ message: string; id: number }>('/api/v1/finance/invoices', data);
  },

  getPayments: async (params?: { student_id?: number; status?: string; payment_method?: string; search?: string }) => {
    const query = new URLSearchParams(params as any).toString();
    return apiClient.get<Payment[]>(`/api/v1/finance/payments${query ? `?${query}` : ''}`);
  },

  recordPayment: async (data: {
    transaction_reference: string;
    student_id: number;
    invoice_id?: number;
    amount: number;
    payment_method?: string;
    payment_date?: string;
  }) => {
    return apiClient.post<{ message: string; id: number; status: string }>('/api/v1/finance/payments', data);
  },

  verifyPayment: async (paymentId: number, status: 'verified' | 'rejected' = 'verified') => {
    return apiClient.post<{ message: string; id: number }>(`/api/v1/finance/payments/${paymentId}/verify`, { status });
  },

  updateClearance: async (studentId: number, clearance_status: 'cleared' | 'pending' | 'blocked_exam') => {
    return apiClient.post<{ message: string; student_id: number; status: string }>('/api/v1/finance/clearance/update', {
      student_id: studentId,
      clearance_status,
    });
  },
};
