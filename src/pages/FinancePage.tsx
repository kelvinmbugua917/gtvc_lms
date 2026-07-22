import React, { useState, useEffect } from 'react';
import {
  CreditCard,
  DollarSign,
  FileText,
  CheckCircle2,
  XCircle,
  AlertCircle,
  Plus,
  Search,
  Filter,
  Check,
  Building2,
  Clock,
  ShieldCheck,
  Receipt,
  Download,
  Printer,
  RefreshCw,
  UserCheck,
  UserX
} from 'lucide-react';
import { useLms } from '../context/LmsContext';
import {
  financeApi,
  FeeStructure,
  StudentFeeAccount,
  Invoice,
  Payment,
  StudentFinancialStatement
} from '../api/finance';

export const FinancePage: React.FC = () => {
  const { authUser: user } = useLms();
  const userRoles = user?.roles?.map((r: any) => typeof r === 'string' ? r : r.name) || [];
  const isFinanceStaff = userRoles.some(r => ['admin', 'super_admin', 'finance_officer', 'bursar', 'hod'].includes(r));
  const isStudent = userRoles.includes('student') && !isFinanceStaff;

  // Active Tab
  const [activeTab, setActiveTab] = useState<'dashboard' | 'fee_structures' | 'student_accounts' | 'invoices' | 'payments' | 'clearance'>(
    isStudent ? 'student_accounts' : 'dashboard'
  );

  // Loading & Data states
  const [loading, setLoading] = useState(false);
  const [feeStructures, setFeeStructures] = useState<FeeStructure[]>([]);
  const [studentAccounts, setStudentAccounts] = useState<StudentFeeAccount[]>([]);
  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [payments, setPayments] = useState<Payment[]>([]);
  const [studentStatement, setStudentStatement] = useState<StudentFinancialStatement | null>(null);

  // Filters & Searches
  const [searchQuery, setSearchQuery] = useState('');
  const [clearanceFilter, setClearanceFilter] = useState('');
  const [statusFilter, setStatusFilter] = useState('');

  // Modals
  const [showFeeStructureModal, setShowFeeStructureModal] = useState(false);
  const [showPaymentModal, setShowPaymentModal] = useState(false);
  const [showInvoiceModal, setShowInvoiceModal] = useState(false);
  const [showStatementModal, setShowStatementModal] = useState(false);

  // Form states
  const [fsForm, setFsForm] = useState({
    program_id: 1,
    academic_year_id: 1,
    intake_id: 1,
    term_semester: 1,
    total_amount: 35000,
    description: 'Term 1 Tuition & Practical Workshop Fees',
  });

  const [paymentForm, setPaymentForm] = useState({
    transaction_reference: '',
    student_id: user?.profile?.id || 1,
    amount: 15000,
    payment_method: 'mpesa' as const,
    invoice_id: undefined as number | undefined,
  });

  const [invoiceForm, setInvoiceForm] = useState({
    student_id: 1,
    amount: 35000,
    due_date: new Date(Date.now() + 30 * 86400000).toISOString().split('T')[0],
  });

  useEffect(() => {
    loadData();
  }, [activeTab, searchQuery, clearanceFilter, statusFilter]);

  const loadData = async () => {
    setLoading(true);
    try {
      if (isStudent) {
        const studentProfileId = user?.profile?.id || 1;
        const res = await financeApi.getStudentAccountById(studentProfileId);
        setStudentStatement(res);
      } else {
        if (activeTab === 'dashboard' || activeTab === 'student_accounts' || activeTab === 'clearance') {
          const accs = await financeApi.getStudentAccounts({ clearance_status: clearanceFilter, search: searchQuery });
          setStudentAccounts(accs);
        }
        if (activeTab === 'dashboard' || activeTab === 'fee_structures') {
          const structs = await financeApi.getFeeStructures();
          setFeeStructures(structs);
        }
        if (activeTab === 'dashboard' || activeTab === 'invoices') {
          const invs = await financeApi.getInvoices({ search: searchQuery, status: statusFilter });
          setInvoices(invs);
        }
        if (activeTab === 'dashboard' || activeTab === 'payments') {
          const pays = await financeApi.getPayments({ search: searchQuery, status: statusFilter });
          setPayments(pays);
        }
      }
    } catch (err) {
      console.error('Error loading financial records:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleCreateFeeStructure = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await financeApi.createFeeStructure(fsForm);
      setShowFeeStructureModal(false);
      loadData();
    } catch (err) {
      alert('Failed to create fee structure');
    }
  };

  const handleRecordPayment = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await financeApi.recordPayment(paymentForm);
      setShowPaymentModal(false);
      setPaymentForm({
        transaction_reference: '',
        student_id: user?.profile?.id || 1,
        amount: 15000,
        payment_method: 'mpesa',
        invoice_id: undefined,
      });
      loadData();
    } catch (err) {
      alert('Failed to record payment');
    }
  };

  const handleCreateInvoice = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await financeApi.createInvoice(invoiceForm);
      setShowInvoiceModal(false);
      loadData();
    } catch (err) {
      alert('Failed to issue invoice');
    }
  };

  const handleVerifyPayment = async (id: number, status: 'verified' | 'rejected') => {
    try {
      await financeApi.verifyPayment(id, status);
      loadData();
    } catch (err) {
      alert('Failed to update payment status');
    }
  };

  const handleUpdateClearance = async (studentId: number, status: 'cleared' | 'pending' | 'blocked_exam') => {
    try {
      await financeApi.updateClearance(studentId, status);
      loadData();
    } catch (err) {
      alert('Failed to update clearance status');
    }
  };

  // Metrics calculations for staff
  const totalBilled = studentAccounts.reduce((acc, a) => acc + (a.total_billed || 0), 0);
  const totalPaid = studentAccounts.reduce((acc, a) => acc + (a.total_paid || 0), 0);
  const totalOutstanding = studentAccounts.reduce((acc, a) => acc + (a.current_balance || 0), 0);
  const clearedStudents = studentAccounts.filter(a => a.clearance_status === 'cleared').length;

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <CreditCard className="w-7 h-7 text-emerald-600" />
            Financial & Fee Management Module
          </h1>
          <p className="text-sm text-slate-600 mt-1">
            Gilgil Technical and Vocational College — Fee Accounts, Invoices, M-Pesa Payment Ledgers & Exam Clearance
          </p>
        </div>

        {isFinanceStaff && (
          <div className="flex items-center gap-2">
            <button
              onClick={() => setShowFeeStructureModal(true)}
              className="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors"
            >
              <Plus className="w-4 h-4" />
              New Fee Structure
            </button>
            <button
              onClick={() => setShowInvoiceModal(true)}
              className="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors"
            >
              <FileText className="w-4 h-4" />
              Issue Invoice
            </button>
            <button
              onClick={() => setShowPaymentModal(true)}
              className="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm"
            >
              <Receipt className="w-4 h-4" />
              Record Payment
            </button>
          </div>
        )}

        {isStudent && (
          <button
            onClick={() => setShowPaymentModal(true)}
            className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm"
          >
            <Receipt className="w-4 h-4" />
            Submit M-Pesa / Bank Receipt
          </button>
        )}
      </div>

      {/* Tabs */}
      {isFinanceStaff && (
        <div className="border-b border-slate-200 flex gap-6 overflow-x-auto">
          {[
            { id: 'dashboard', label: 'Financial Overview', icon: DollarSign },
            { id: 'student_accounts', label: 'Student Fee Accounts', icon: UserCheck },
            { id: 'invoices', label: 'Invoices Ledger', icon: FileText },
            { id: 'payments', label: 'Payment Receipts', icon: Receipt },
            { id: 'fee_structures', label: 'Fee Structures', icon: Building2 },
            { id: 'clearance', label: 'Exam Fee Clearance', icon: ShieldCheck },
          ].map((tab) => {
            const Icon = tab.icon;
            const active = activeTab === tab.id;
            return (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id as any)}
                className={`flex items-center gap-2 pb-3 text-sm font-medium transition-colors border-b-2 whitespace-nowrap ${
                  active
                    ? 'border-emerald-600 text-emerald-700 font-semibold'
                    : 'border-transparent text-slate-600 hover:text-slate-900'
                }`}
              >
                <Icon className={`w-4 h-4 ${active ? 'text-emerald-600' : 'text-slate-400'}`} />
                {tab.label}
              </button>
            );
          })}
        </div>
      )}

      {/* Student Personal Financial View */}
      {isStudent && studentStatement && (
        <div className="space-y-6">
          {/* Summary Cards */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div className="p-4 bg-white rounded-xl border border-slate-200 shadow-sm">
              <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Invoiced</span>
              <p className="text-2xl font-bold text-slate-900 mt-1">
                KES {Number(studentStatement.account?.total_billed || 0).toLocaleString()}
              </p>
            </div>
            <div className="p-4 bg-white rounded-xl border border-slate-200 shadow-sm">
              <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Paid</span>
              <p className="text-2xl font-bold text-emerald-600 mt-1">
                KES {Number(studentStatement.account?.total_paid || 0).toLocaleString()}
              </p>
            </div>
            <div className="p-4 bg-white rounded-xl border border-slate-200 shadow-sm">
              <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Current Balance</span>
              <p className={`text-2xl font-bold mt-1 ${
                Number(studentStatement.account?.current_balance || 0) <= 0 ? 'text-emerald-600' : 'text-rose-600'
              }`}>
                KES {Number(studentStatement.account?.current_balance || 0).toLocaleString()}
              </p>
            </div>
            <div className="p-4 bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
              <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Exam Clearance</span>
              <div className="mt-1">
                {studentStatement.account?.clearance_status === 'cleared' ? (
                  <span className="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-emerald-800 bg-emerald-100 rounded-full">
                    <CheckCircle2 className="w-3.5 h-3.5" /> Cleared for Exams
                  </span>
                ) : (
                  <span className="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-amber-800 bg-amber-100 rounded-full">
                    <AlertCircle className="w-3.5 h-3.5" /> Pending Clearance
                  </span>
                )}
              </div>
            </div>
          </div>

          {/* Student Statement Section */}
          <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div className="p-4 border-b border-slate-200 flex items-center justify-between">
              <h3 className="font-semibold text-slate-900 flex items-center gap-2">
                <Receipt className="w-5 h-5 text-emerald-600" />
                Personal Financial Statement & Payment Ledger
              </h3>
              <button
                onClick={() => window.print()}
                className="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 border border-slate-200 px-3 py-1.5 rounded-md"
              >
                <Printer className="w-3.5 h-3.5" /> Print Statement
              </button>
            </div>

            <div className="p-6 space-y-6">
              {/* Invoices */}
              <div>
                <h4 className="text-sm font-semibold text-slate-800 mb-3 uppercase tracking-wider">Issued Invoices</h4>
                <div className="overflow-x-auto border border-slate-200 rounded-lg">
                  <table className="w-full text-left text-sm">
                    <thead className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase">
                      <tr>
                        <th className="py-2.5 px-4">Invoice #</th>
                        <th className="py-2.5 px-4">Due Date</th>
                        <th className="py-2.5 px-4">Amount</th>
                        <th className="py-2.5 px-4">Status</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-200">
                      {studentStatement.invoices?.length === 0 ? (
                        <tr><td colSpan={4} className="py-4 text-center text-slate-500">No invoices issued.</td></tr>
                      ) : (
                        studentStatement.invoices?.map((inv) => (
                          <tr key={inv.id}>
                            <td className="py-3 px-4 font-mono text-slate-900">{inv.invoice_number}</td>
                            <td className="py-3 px-4 text-slate-600">{inv.due_date}</td>
                            <td className="py-3 px-4 font-semibold text-slate-900">KES {Number(inv.amount).toLocaleString()}</td>
                            <td className="py-3 px-4">
                              <span className={`inline-flex px-2 py-0.5 text-xs font-medium rounded-full ${
                                inv.status === 'paid' ? 'bg-emerald-100 text-emerald-800' :
                                inv.status === 'partially_paid' ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800'
                              }`}>
                                {inv.status.replace('_', ' ')}
                              </span>
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              </div>

              {/* Payments */}
              <div>
                <h4 className="text-sm font-semibold text-slate-800 mb-3 uppercase tracking-wider">Payment Records</h4>
                <div className="overflow-x-auto border border-slate-200 rounded-lg">
                  <table className="w-full text-left text-sm">
                    <thead className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase">
                      <tr>
                        <th className="py-2.5 px-4">Reference Code</th>
                        <th className="py-2.5 px-4">Method</th>
                        <th className="py-2.5 px-4">Date</th>
                        <th className="py-2.5 px-4">Amount</th>
                        <th className="py-2.5 px-4">Verification</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-200">
                      {studentStatement.payments?.length === 0 ? (
                        <tr><td colSpan={5} className="py-4 text-center text-slate-500">No payment receipts recorded yet.</td></tr>
                      ) : (
                        studentStatement.payments?.map((pay) => (
                          <tr key={pay.id}>
                            <td className="py-3 px-4 font-mono font-bold text-slate-900">{pay.transaction_reference}</td>
                            <td className="py-3 px-4 uppercase text-slate-600">{pay.payment_method}</td>
                            <td className="py-3 px-4 text-slate-600">{pay.payment_date?.substring(0, 10)}</td>
                            <td className="py-3 px-4 font-semibold text-emerald-600">KES {Number(pay.amount).toLocaleString()}</td>
                            <td className="py-3 px-4">
                              <span className={`inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full ${
                                pay.status === 'verified' ? 'bg-emerald-100 text-emerald-800' :
                                pay.status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800'
                              }`}>
                                {pay.status === 'verified' && <CheckCircle2 className="w-3 h-3" />}
                                {pay.status}
                              </span>
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Staff View: Financial Overview Dashboard */}
      {isFinanceStaff && activeTab === 'dashboard' && (
        <div className="space-y-6">
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div className="p-5 bg-white rounded-xl border border-slate-200 shadow-sm">
              <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Invoiced</span>
              <p className="text-2xl font-extrabold text-slate-900 mt-2">
                KES {totalBilled.toLocaleString()}
              </p>
              <p className="text-xs text-slate-500 mt-1">Across all registered students</p>
            </div>
            <div className="p-5 bg-white rounded-xl border border-slate-200 shadow-sm">
              <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Verified Revenue</span>
              <p className="text-2xl font-extrabold text-emerald-600 mt-2">
                KES {totalPaid.toLocaleString()}
              </p>
              <p className="text-xs text-emerald-600 font-medium mt-1">Collected via M-Pesa & Bank</p>
            </div>
            <div className="p-5 bg-white rounded-xl border border-slate-200 shadow-sm">
              <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Outstanding</span>
              <p className="text-2xl font-extrabold text-rose-600 mt-2">
                KES {totalOutstanding.toLocaleString()}
              </p>
              <p className="text-xs text-rose-500 mt-1">Pending fee arrears</p>
            </div>
            <div className="p-5 bg-white rounded-xl border border-slate-200 shadow-sm">
              <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Exam Cleared Students</span>
              <p className="text-2xl font-extrabold text-blue-600 mt-2">
                {clearedStudents} / {studentAccounts.length}
              </p>
              <p className="text-xs text-blue-600 font-medium mt-1">Qualified for exams</p>
            </div>
          </div>

          {/* Recent Payments Ledger preview */}
          <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="font-semibold text-slate-900 text-lg flex items-center gap-2">
                <Receipt className="w-5 h-5 text-emerald-600" />
                Recent Payment Verifications
              </h3>
              <button
                onClick={() => setActiveTab('payments')}
                className="text-xs font-semibold text-emerald-700 hover:underline"
              >
                View All Payments &rarr;
              </button>
            </div>
            <div className="overflow-x-auto border border-slate-200 rounded-lg">
              <table className="w-full text-left text-sm">
                <thead className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase">
                  <tr>
                    <th className="py-2.5 px-4">M-Pesa / Bank Ref</th>
                    <th className="py-2.5 px-4">Student</th>
                    <th className="py-2.5 px-4">Method</th>
                    <th className="py-2.5 px-4">Amount</th>
                    <th className="py-2.5 px-4">Status</th>
                    <th className="py-2.5 px-4">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-200">
                  {payments.slice(0, 5).map((pay) => (
                    <tr key={pay.id} className="hover:bg-slate-50">
                      <td className="py-3 px-4 font-mono font-bold text-slate-900">{pay.transaction_reference}</td>
                      <td className="py-3 px-4">
                        <div className="font-medium text-slate-900">{pay.first_name} {pay.last_name}</div>
                        <div className="text-xs text-slate-500">{pay.admission_number}</div>
                      </td>
                      <td className="py-3 px-4 uppercase text-slate-600">{pay.payment_method}</td>
                      <td className="py-3 px-4 font-semibold text-emerald-600">KES {Number(pay.amount).toLocaleString()}</td>
                      <td className="py-3 px-4">
                        <span className={`inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-medium rounded-full ${
                          pay.status === 'verified' ? 'bg-emerald-100 text-emerald-800' :
                          pay.status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800'
                        }`}>
                          {pay.status}
                        </span>
                      </td>
                      <td className="py-3 px-4">
                        {pay.status === 'pending' && (
                          <div className="flex gap-2">
                            <button
                              onClick={() => handleVerifyPayment(pay.id, 'verified')}
                              className="px-2 py-1 text-xs font-semibold text-white bg-emerald-600 rounded hover:bg-emerald-700"
                            >
                              Verify
                            </button>
                            <button
                              onClick={() => handleVerifyPayment(pay.id, 'rejected')}
                              className="px-2 py-1 text-xs font-semibold text-rose-700 bg-rose-100 rounded hover:bg-rose-200"
                            >
                              Reject
                            </button>
                          </div>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* Staff View: Student Fee Accounts */}
      {isFinanceStaff && activeTab === 'student_accounts' && (
        <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div className="relative flex-1">
              <Search className="w-4 h-4 text-slate-400 absolute left-3 top-3" />
              <input
                type="text"
                placeholder="Search student name or admission number..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
              />
            </div>
            <select
              value={clearanceFilter}
              onChange={(e) => setClearanceFilter(e.target.value)}
              className="px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700"
            >
              <option value="">All Clearance Statuses</option>
              <option value="cleared">Cleared</option>
              <option value="pending">Pending</option>
              <option value="blocked_exam">Blocked from Exam</option>
            </select>
          </div>

          <div className="overflow-x-auto border border-slate-200 rounded-lg">
            <table className="w-full text-left text-sm">
              <thead className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase">
                <tr>
                  <th className="py-2.5 px-4">Admission #</th>
                  <th className="py-2.5 px-4">Student Name</th>
                  <th className="py-2.5 px-4">Program</th>
                  <th className="py-2.5 px-4">Total Billed</th>
                  <th className="py-2.5 px-4">Total Paid</th>
                  <th className="py-2.5 px-4">Balance</th>
                  <th className="py-2.5 px-4">Exam Clearance</th>
                  <th className="py-2.5 px-4">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-200">
                {studentAccounts.map((acc) => (
                  <tr key={acc.student_id} className="hover:bg-slate-50">
                    <td className="py-3 px-4 font-mono font-medium text-slate-900">{acc.admission_number}</td>
                    <td className="py-3 px-4 font-medium text-slate-900">{acc.first_name} {acc.last_name}</td>
                    <td className="py-3 px-4 text-slate-600">{acc.program_code || acc.department_name || 'GTVC'}</td>
                    <td className="py-3 px-4 font-medium text-slate-900">KES {Number(acc.total_billed).toLocaleString()}</td>
                    <td className="py-3 px-4 font-semibold text-emerald-600">KES {Number(acc.total_paid).toLocaleString()}</td>
                    <td className={`py-3 px-4 font-bold ${Number(acc.current_balance) <= 0 ? 'text-emerald-600' : 'text-rose-600'}`}>
                      KES {Number(acc.current_balance).toLocaleString()}
                    </td>
                    <td className="py-3 px-4">
                      <span className={`inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full ${
                        acc.clearance_status === 'cleared' ? 'bg-emerald-100 text-emerald-800' :
                        acc.clearance_status === 'blocked_exam' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800'
                      }`}>
                        {acc.clearance_status.replace('_', ' ')}
                      </span>
                    </td>
                    <td className="py-3 px-4">
                      <select
                        value={acc.clearance_status}
                        onChange={(e) => handleUpdateClearance(acc.student_id, e.target.value as any)}
                        className="text-xs py-1 px-2 border border-slate-200 rounded bg-white text-slate-700"
                      >
                        <option value="cleared">Set Cleared</option>
                        <option value="pending">Set Pending</option>
                        <option value="blocked_exam">Set Blocked</option>
                      </select>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Staff View: Fee Structures */}
      {isFinanceStaff && activeTab === 'fee_structures' && (
        <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
          <div className="flex justify-between items-center">
            <h3 className="font-semibold text-slate-900 text-lg">Institutional Fee Structures</h3>
            <button
              onClick={() => setShowFeeStructureModal(true)}
              className="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700"
            >
              <Plus className="w-4 h-4" /> Add Fee Structure
            </button>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {feeStructures.map((fs) => (
              <div key={fs.id} className="p-4 rounded-xl border border-slate-200 bg-slate-50 flex flex-col justify-between space-y-3">
                <div>
                  <div className="flex justify-between items-start">
                    <span className="text-xs font-bold text-emerald-700 uppercase bg-emerald-100 px-2 py-0.5 rounded">
                      {fs.program_code || 'GTVC Program'}
                    </span>
                    <span className="text-xs text-slate-500 font-mono">Term {fs.term_semester}</span>
                  </div>
                  <h4 className="font-bold text-slate-900 mt-2">{fs.program_name}</h4>
                  <p className="text-xs text-slate-600 mt-1">{fs.description}</p>
                </div>
                <div className="pt-3 border-t border-slate-200 flex items-center justify-between">
                  <span className="text-xs text-slate-500">{fs.academic_year_name} / {fs.intake_name}</span>
                  <span className="text-lg font-extrabold text-slate-900">KES {Number(fs.total_amount).toLocaleString()}</span>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Staff View: Invoices Ledger */}
      {isFinanceStaff && activeTab === 'invoices' && (
        <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="font-semibold text-slate-900 text-lg">Invoices Ledger</h3>
            <button
              onClick={() => setShowInvoiceModal(true)}
              className="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
            >
              <Plus className="w-4 h-4" /> Issue Invoice
            </button>
          </div>

          <div className="overflow-x-auto border border-slate-200 rounded-lg">
            <table className="w-full text-left text-sm">
              <thead className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase">
                <tr>
                  <th className="py-2.5 px-4">Invoice #</th>
                  <th className="py-2.5 px-4">Student</th>
                  <th className="py-2.5 px-4">Due Date</th>
                  <th className="py-2.5 px-4">Amount</th>
                  <th className="py-2.5 px-4">Status</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-200">
                {invoices.map((inv) => (
                  <tr key={inv.id} className="hover:bg-slate-50">
                    <td className="py-3 px-4 font-mono font-bold text-slate-900">{inv.invoice_number}</td>
                    <td className="py-3 px-4">
                      <div className="font-medium text-slate-900">{inv.first_name} {inv.last_name}</div>
                      <div className="text-xs text-slate-500">{inv.admission_number}</div>
                    </td>
                    <td className="py-3 px-4 text-slate-600">{inv.due_date}</td>
                    <td className="py-3 px-4 font-semibold text-slate-900">KES {Number(inv.amount).toLocaleString()}</td>
                    <td className="py-3 px-4">
                      <span className={`inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full ${
                        inv.status === 'paid' ? 'bg-emerald-100 text-emerald-800' :
                        inv.status === 'partially_paid' ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800'
                      }`}>
                        {inv.status.replace('_', ' ')}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Staff View: Payments Ledger */}
      {isFinanceStaff && activeTab === 'payments' && (
        <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="font-semibold text-slate-900 text-lg">M-Pesa & Bank Receipts Ledger</h3>
            <button
              onClick={() => setShowPaymentModal(true)}
              className="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700"
            >
              <Plus className="w-4 h-4" /> Manual Payment Entry
            </button>
          </div>

          <div className="overflow-x-auto border border-slate-200 rounded-lg">
            <table className="w-full text-left text-sm">
              <thead className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase">
                <tr>
                  <th className="py-2.5 px-4">Reference Code</th>
                  <th className="py-2.5 px-4">Student</th>
                  <th className="py-2.5 px-4">Method</th>
                  <th className="py-2.5 px-4">Amount</th>
                  <th className="py-2.5 px-4">Date</th>
                  <th className="py-2.5 px-4">Status</th>
                  <th className="py-2.5 px-4">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-200">
                {payments.map((pay) => (
                  <tr key={pay.id} className="hover:bg-slate-50">
                    <td className="py-3 px-4 font-mono font-bold text-slate-900">{pay.transaction_reference}</td>
                    <td className="py-3 px-4">
                      <div className="font-medium text-slate-900">{pay.first_name} {pay.last_name}</div>
                      <div className="text-xs text-slate-500">{pay.admission_number}</div>
                    </td>
                    <td className="py-3 px-4 uppercase text-slate-600">{pay.payment_method}</td>
                    <td className="py-3 px-4 font-semibold text-emerald-600">KES {Number(pay.amount).toLocaleString()}</td>
                    <td className="py-3 px-4 text-slate-600">{pay.payment_date?.substring(0, 10)}</td>
                    <td className="py-3 px-4">
                      <span className={`inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full ${
                        pay.status === 'verified' ? 'bg-emerald-100 text-emerald-800' :
                        pay.status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800'
                      }`}>
                        {pay.status}
                      </span>
                    </td>
                    <td className="py-3 px-4">
                      {pay.status === 'pending' && (
                        <div className="flex gap-2">
                          <button
                            onClick={() => handleVerifyPayment(pay.id, 'verified')}
                            className="px-2.5 py-1 text-xs font-semibold text-white bg-emerald-600 rounded hover:bg-emerald-700"
                          >
                            Verify
                          </button>
                          <button
                            onClick={() => handleVerifyPayment(pay.id, 'rejected')}
                            className="px-2.5 py-1 text-xs font-semibold text-rose-700 bg-rose-100 rounded hover:bg-rose-200"
                          >
                            Reject
                          </button>
                        </div>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Staff View: Exam Clearance */}
      {isFinanceStaff && activeTab === 'clearance' && (
        <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="font-semibold text-slate-900 text-lg flex items-center gap-2">
                <ShieldCheck className="w-5 h-5 text-emerald-600" />
                Exam Fee Clearance Verification
              </h3>
              <p className="text-xs text-slate-500">Configurable clearance threshold rules & manual finance overrides</p>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {studentAccounts.map((acc) => (
              <div key={acc.student_id} className="p-4 rounded-xl border border-slate-200 bg-slate-50 flex flex-col justify-between">
                <div>
                  <div className="flex justify-between items-start">
                    <span className="font-mono text-xs font-bold text-slate-700">{acc.admission_number}</span>
                    <span className={`inline-flex px-2 py-0.5 text-xs font-semibold rounded-full ${
                      acc.clearance_status === 'cleared' ? 'bg-emerald-100 text-emerald-800' :
                      acc.clearance_status === 'blocked_exam' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800'
                    }`}>
                      {acc.clearance_status.replace('_', ' ')}
                    </span>
                  </div>
                  <h4 className="font-bold text-slate-900 mt-2">{acc.first_name} {acc.last_name}</h4>
                  <p className="text-xs text-slate-500 mt-1">{acc.program_name || 'GTVC Program'}</p>
                  
                  <div className="mt-3 pt-3 border-t border-slate-200 space-y-1 text-xs">
                    <div className="flex justify-between">
                      <span className="text-slate-500">Billed:</span>
                      <span className="font-semibold text-slate-900">KES {Number(acc.total_billed).toLocaleString()}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-slate-500">Paid:</span>
                      <span className="font-semibold text-emerald-600">KES {Number(acc.total_paid).toLocaleString()}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-slate-500">Balance:</span>
                      <span className={`font-extrabold ${Number(acc.current_balance) <= 0 ? 'text-emerald-600' : 'text-rose-600'}`}>
                        KES {Number(acc.current_balance).toLocaleString()}
                      </span>
                    </div>
                  </div>
                </div>

                <div className="mt-4 pt-3 border-t border-slate-200 flex gap-2">
                  <button
                    onClick={() => handleUpdateClearance(acc.student_id, 'cleared')}
                    className="flex-1 py-1.5 text-xs font-semibold text-white bg-emerald-600 rounded hover:bg-emerald-700"
                  >
                    Approve Clearance
                  </button>
                  <button
                    onClick={() => handleUpdateClearance(acc.student_id, 'blocked_exam')}
                    className="flex-1 py-1.5 text-xs font-semibold text-rose-700 bg-rose-100 rounded hover:bg-rose-200"
                  >
                    Block Exam
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Record Payment Modal */}
      {showPaymentModal && (
        <div className="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
            <h3 className="font-bold text-slate-900 text-lg">Record M-Pesa / Bank Payment</h3>
            <form onSubmit={handleRecordPayment} className="space-y-3">
              <div>
                <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">M-Pesa / Transaction Reference Code</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. QFH89324JK"
                  value={paymentForm.transaction_reference}
                  onChange={(e) => setPaymentForm({ ...paymentForm, transaction_reference: e.target.value })}
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-emerald-500"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">Amount Paid (KES)</label>
                <input
                  type="number"
                  required
                  value={paymentForm.amount}
                  onChange={(e) => setPaymentForm({ ...paymentForm, amount: Number(e.target.value) })}
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm font-semibold focus:ring-2 focus:ring-emerald-500"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">Payment Method</label>
                <select
                  value={paymentForm.payment_method}
                  onChange={(e) => setPaymentForm({ ...paymentForm, payment_method: e.target.value as any })}
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm"
                >
                  <option value="mpesa">M-Pesa Express / Paybill</option>
                  <option value="bank_transfer">KCB / Equity Bank Transfer</option>
                  <option value="cheque">Bankers Cheque</option>
                  <option value="cash">Cash Counter Receipt</option>
                </select>
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowPaymentModal(false)}
                  className="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700"
                >
                  Submit Payment
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Fee Structure Modal */}
      {showFeeStructureModal && (
        <div className="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
            <h3 className="font-bold text-slate-900 text-lg">Create Program Fee Structure</h3>
            <form onSubmit={handleCreateFeeStructure} className="space-y-3">
              <div>
                <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">Program ID</label>
                <input
                  type="number"
                  value={fsForm.program_id}
                  onChange={(e) => setFsForm({ ...fsForm, program_id: Number(e.target.value) })}
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">Academic Year ID / Intake ID</label>
                <div className="grid grid-cols-2 gap-2">
                  <input
                    type="number"
                    value={fsForm.academic_year_id}
                    onChange={(e) => setFsForm({ ...fsForm, academic_year_id: Number(e.target.value) })}
                    className="px-3 py-2 border border-slate-200 rounded-lg text-sm"
                  />
                  <input
                    type="number"
                    value={fsForm.intake_id}
                    onChange={(e) => setFsForm({ ...fsForm, intake_id: Number(e.target.value) })}
                    className="px-3 py-2 border border-slate-200 rounded-lg text-sm"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">Term / Semester</label>
                <input
                  type="number"
                  value={fsForm.term_semester}
                  onChange={(e) => setFsForm({ ...fsForm, term_semester: Number(e.target.value) })}
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">Total Fee Amount (KES)</label>
                <input
                  type="number"
                  value={fsForm.total_amount}
                  onChange={(e) => setFsForm({ ...fsForm, total_amount: Number(e.target.value) })}
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm font-semibold"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">Description</label>
                <input
                  type="text"
                  value={fsForm.description}
                  onChange={(e) => setFsForm({ ...fsForm, description: e.target.value })}
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm"
                />
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowFeeStructureModal(false)}
                  className="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700"
                >
                  Create Fee Structure
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Issue Invoice Modal */}
      {showInvoiceModal && (
        <div className="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
            <h3 className="font-bold text-slate-900 text-lg">Issue Student Fee Invoice</h3>
            <form onSubmit={handleCreateInvoice} className="space-y-3">
              <div>
                <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">Student Profile ID</label>
                <input
                  type="number"
                  required
                  value={invoiceForm.student_id}
                  onChange={(e) => setInvoiceForm({ ...invoiceForm, student_id: Number(e.target.value) })}
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">Invoice Amount (KES)</label>
                <input
                  type="number"
                  required
                  value={invoiceForm.amount}
                  onChange={(e) => setInvoiceForm({ ...invoiceForm, amount: Number(e.target.value) })}
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm font-semibold"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">Due Date</label>
                <input
                  type="date"
                  required
                  value={invoiceForm.due_date}
                  onChange={(e) => setInvoiceForm({ ...invoiceForm, due_date: e.target.value })}
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm"
                />
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowInvoiceModal(false)}
                  className="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
                >
                  Issue Invoice
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
