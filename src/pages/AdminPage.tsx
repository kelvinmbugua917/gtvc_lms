import React, { useState, useEffect } from 'react';
import {
  Shield,
  Users,
  Settings,
  Activity,
  UserPlus,
  Search,
  CheckCircle,
  XCircle,
  AlertTriangle,
  Lock,
  Eye,
  Key,
  Database,
  Sliders,
  FileCode2
} from 'lucide-react';
import { adminApi, AdminUser, Role, Permission, SystemSettingItem, AuditLogItem } from '../api/admin';

export const AdminPage: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'users' | 'roles' | 'settings' | 'audit'>('users');
  const [loading, setLoading] = useState(false);

  // Data states
  const [users, setUsers] = useState<AdminUser[]>([]);
  const [roles, setRoles] = useState<Role[]>([]);
  const [permissions, setPermissions] = useState<Permission[]>([]);
  const [settings, setSettings] = useState<SystemSettingItem[]>([]);
  const [auditLogs, setAuditLogs] = useState<AuditLogItem[]>([]);

  // Filters & Search
  const [searchQuery, setSearchQuery] = useState('');
  const [roleFilter, setRoleFilter] = useState('');
  const [statusFilter, setStatusFilter] = useState('');

  // Modals
  const [showCreateUserModal, setShowCreateUserModal] = useState(false);
  const [showAssignRoleModal, setShowAssignRoleModal] = useState(false);
  const [selectedUser, setSelectedUser] = useState<AdminUser | null>(null);

  // User Form
  const [userForm, setUserForm] = useState({
    email: '',
    first_name: '',
    last_name: '',
    password: '',
    role_id: 2,
    status: 'active',
  });

  const [selectedRoleIds, setSelectedRoleIds] = useState<number[]>([]);

  useEffect(() => {
    loadData();
  }, [activeTab, searchQuery, roleFilter, statusFilter]);

  const loadData = async () => {
    setLoading(true);
    try {
      if (activeTab === 'users') {
        const res = await adminApi.getUsers({ search: searchQuery, role: roleFilter, status: statusFilter });
        setUsers(res);
      } else if (activeTab === 'roles') {
        const res = await adminApi.getRoles();
        setRoles(res.roles);
        setPermissions(res.permissions);
      } else if (activeTab === 'settings') {
        const res = await adminApi.getSettings();
        setSettings(res);
      } else if (activeTab === 'audit') {
        const res = await adminApi.getAuditLogs({ search: searchQuery });
        setAuditLogs(res);
      }
    } catch (err) {
      console.error('Error loading admin records:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleCreateUser = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await adminApi.createUser(userForm);
      setShowCreateUserModal(false);
      setUserForm({ email: '', first_name: '', last_name: '', password: '', role_id: 2, status: 'active' });
      loadData();
    } catch (err) {
      alert('Failed to create user account');
    }
  };

  const handleAssignRoles = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedUser) return;
    try {
      await adminApi.assignRoles(selectedUser.id, selectedRoleIds);
      setShowAssignRoleModal(false);
      loadData();
    } catch (err) {
      alert('Failed to assign user roles');
    }
  };

  const handleSettingChange = (key: string, value: string) => {
    setSettings(prev => prev.map(s => s.setting_key === key ? { ...s, setting_value: value } : s));
  };

  const handleSaveSettings = async () => {
    try {
      const map: Record<string, string> = {};
      settings.forEach(s => {
        map[s.setting_key] = s.setting_value;
      });
      await adminApi.updateSettings(map);
      alert('System settings saved successfully');
    } catch (err) {
      alert('Failed to save settings');
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <Shield className="w-7 h-7 text-indigo-600" />
            Administrative & System Control Center
          </h1>
          <p className="text-sm text-slate-600 mt-1">
            User Administration, Role-Based Access Controls (RBAC), Clearance Rules & Audit Trails
          </p>
        </div>

        {activeTab === 'users' && (
          <button
            onClick={() => setShowCreateUserModal(true)}
            className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm"
          >
            <UserPlus className="w-4 h-4" /> Add User Account
          </button>
        )}

        {activeTab === 'settings' && (
          <button
            onClick={handleSaveSettings}
            className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 shadow-sm"
          >
            <Settings className="w-4 h-4" /> Save System Settings
          </button>
        )}
      </div>

      {/* Admin Tabs */}
      <div className="border-b border-slate-200 flex gap-6 overflow-x-auto">
        {[
          { id: 'users', label: 'User Administration', icon: Users },
          { id: 'roles', label: 'RBAC Roles & Permissions', icon: Lock },
          { id: 'settings', label: 'System Configuration', icon: Sliders },
          { id: 'audit', label: 'Audit Trail & Logs', icon: Activity },
        ].map((tab) => {
          const Icon = tab.icon;
          const active = activeTab === tab.id;
          return (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id as any)}
              className={`flex items-center gap-2 pb-3 text-sm font-medium transition-colors border-b-2 whitespace-nowrap ${
                active
                  ? 'border-indigo-600 text-indigo-700 font-semibold'
                  : 'border-transparent text-slate-600 hover:text-slate-900'
              }`}
            >
              <Icon className={`w-4 h-4 ${active ? 'text-indigo-600' : 'text-slate-400'}`} />
              {tab.label}
            </button>
          );
        })}
      </div>

      {/* User Administration Tab */}
      {activeTab === 'users' && (
        <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div className="relative flex-1">
              <Search className="w-4 h-4 text-slate-400 absolute left-3 top-3" />
              <input
                type="text"
                placeholder="Search user name or email..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              />
            </div>
            <select
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value)}
              className="px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700"
            >
              <option value="">All Account Statuses</option>
              <option value="active">Active</option>
              <option value="suspended">Suspended</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div className="overflow-x-auto border border-slate-200 rounded-lg">
            <table className="w-full text-left text-sm">
              <thead className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase">
                <tr>
                  <th className="py-2.5 px-4">User</th>
                  <th className="py-2.5 px-4">Email Address</th>
                  <th className="py-2.5 px-4">Assigned Roles</th>
                  <th className="py-2.5 px-4">Account Status</th>
                  <th className="py-2.5 px-4">Created Date</th>
                  <th className="py-2.5 px-4">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-200">
                {users.map((u) => (
                  <tr key={u.id} className="hover:bg-slate-50">
                    <td className="py-3 px-4 font-semibold text-slate-900">{u.first_name} {u.last_name}</td>
                    <td className="py-3 px-4 font-mono text-slate-600">{u.email}</td>
                    <td className="py-3 px-4">
                      <div className="flex flex-wrap gap-1">
                        {u.roles?.map((r) => (
                          <span key={r.id} className="px-2 py-0.5 text-xs font-medium text-indigo-800 bg-indigo-50 border border-indigo-200 rounded">
                            {r.name}
                          </span>
                        ))}
                      </div>
                    </td>
                    <td className="py-3 px-4">
                      <span className={`inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-medium rounded-full ${
                        u.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'
                      }`}>
                        {u.status}
                      </span>
                    </td>
                    <td className="py-3 px-4 text-slate-500 text-xs">{u.created_at?.substring(0, 10)}</td>
                    <td className="py-3 px-4">
                      <button
                        onClick={() => {
                          setSelectedUser(u);
                          setSelectedRoleIds(u.roles?.map(r => r.id) || []);
                          setShowAssignRoleModal(true);
                        }}
                        className="px-2.5 py-1 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded"
                      >
                        Manage Roles
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Roles & Permissions Matrix Tab */}
      {activeTab === 'roles' && (
        <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-6">
          <div>
            <h3 className="font-semibold text-slate-900 text-lg flex items-center gap-2">
              <Lock className="w-5 h-5 text-indigo-600" />
              Role-Based Access Control (RBAC) System Matrix
            </h3>
            <p className="text-xs text-slate-500 mt-1">
              Defined roles and granular capability permissions for GTVC institutional staff and students.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {roles.map((r) => (
              <div key={r.id} className="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                <div className="flex items-center justify-between">
                  <span className="font-bold text-slate-900 uppercase text-xs tracking-wider font-mono">{r.name}</span>
                  <span className="text-xs text-slate-400">ID #{r.id}</span>
                </div>
                <p className="text-xs text-slate-600">{r.description || 'Institutional User Role'}</p>
              </div>
            ))}
          </div>

          <div className="border-t border-slate-200 pt-4">
            <h4 className="font-semibold text-slate-900 text-sm mb-3 uppercase tracking-wider">System Permissions Catalog</h4>
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
              {permissions.map((p) => (
                <div key={p.id} className="p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                  <div className="font-bold text-indigo-900">{p.name}</div>
                  <div className="text-slate-500 text-[11px] uppercase tracking-wide font-mono mt-0.5">{p.module}</div>
                  <div className="text-slate-600 mt-1">{p.description}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {/* System Settings Tab */}
      {activeTab === 'settings' && (
        <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-6">
          <div>
            <h3 className="font-semibold text-slate-900 text-lg flex items-center gap-2">
              <Sliders className="w-5 h-5 text-indigo-600" />
              Academic & Institutional System Configuration
            </h3>
            <p className="text-xs text-slate-500 mt-1">
              Configure institution-wide fee clearance threshold rules, college details, and policies.
            </p>
          </div>

          <div className="space-y-4 max-w-2xl">
            {settings.map((s) => (
              <div key={s.setting_key} className="p-4 border border-slate-200 rounded-lg bg-slate-50 space-y-2">
                <label className="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                  {s.setting_key.replace('_', ' ')}
                </label>
                <input
                  type="text"
                  value={s.setting_value || ''}
                  onChange={(e) => handleSettingChange(s.setting_key, e.target.value)}
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white font-mono focus:ring-2 focus:ring-indigo-500"
                />
                <p className="text-xs text-slate-500">{s.description}</p>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Audit Trail Tab */}
      {activeTab === 'audit' && (
        <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="font-semibold text-slate-900 text-lg flex items-center gap-2">
              <Activity className="w-5 h-5 text-indigo-600" />
              Institutional Security & Financial Audit Logs
            </h3>
          </div>

          <div className="overflow-x-auto border border-slate-200 rounded-lg">
            <table className="w-full text-left text-sm">
              <thead className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase">
                <tr>
                  <th className="py-2.5 px-4">Timestamp</th>
                  <th className="py-2.5 px-4">User</th>
                  <th className="py-2.5 px-4">Security Action</th>
                  <th className="py-2.5 px-4">IP Address</th>
                  <th className="py-2.5 px-4">Details</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-200">
                {auditLogs.map((log) => (
                  <tr key={log.id} className="hover:bg-slate-50">
                    <td className="py-3 px-4 font-mono text-xs text-slate-500">{log.created_at}</td>
                    <td className="py-3 px-4">
                      {log.email ? (
                        <div>
                          <div className="font-medium text-slate-900">{log.first_name} {log.last_name}</div>
                          <div className="text-xs font-mono text-slate-500">{log.email}</div>
                        </div>
                      ) : (
                        <span className="text-slate-400 italic">System / Unauthenticated</span>
                      )}
                    </td>
                    <td className="py-3 px-4">
                      <span className="px-2 py-0.5 text-xs font-bold font-mono text-indigo-900 bg-indigo-50 border border-indigo-200 rounded">
                        {log.action}
                      </span>
                    </td>
                    <td className="py-3 px-4 font-mono text-xs text-slate-600">{log.ip_address || '127.0.0.1'}</td>
                    <td className="py-3 px-4 font-mono text-xs text-slate-600 max-w-xs truncate">
                      {log.details_json ? JSON.stringify(log.details_json) : '-'}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Create User Modal */}
      {showCreateUserModal && (
        <div className="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
            <h3 className="font-bold text-slate-900 text-lg">Create GTVC User Account</h3>
            <form onSubmit={handleCreateUser} className="space-y-3">
              <div className="grid grid-cols-2 gap-2">
                <div>
                  <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">First Name</label>
                  <input
                    type="text"
                    required
                    value={userForm.first_name}
                    onChange={(e) => setUserForm({ ...userForm, first_name: e.target.value })}
                    className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">Last Name</label>
                  <input
                    type="text"
                    required
                    value={userForm.last_name}
                    onChange={(e) => setUserForm({ ...userForm, last_name: e.target.value })}
                    className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">Email Address</label>
                <input
                  type="email"
                  required
                  value={userForm.email}
                  onChange={(e) => setUserForm({ ...userForm, email: e.target.value })}
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">Initial Password</label>
                <input
                  type="password"
                  required
                  value={userForm.password}
                  onChange={(e) => setUserForm({ ...userForm, password: e.target.value })}
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">Role</label>
                <select
                  value={userForm.role_id}
                  onChange={(e) => setUserForm({ ...userForm, role_id: Number(e.target.value) })}
                  className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm"
                >
                  <option value={2}>Student</option>
                  <option value={1}>Super Admin</option>
                  <option value={3}>Lecturer</option>
                  <option value={4}>Finance Officer</option>
                  <option value={5}>HOD</option>
                </select>
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowCreateUserModal(false)}
                  className="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700"
                >
                  Create Account
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Assign Role Modal */}
      {showAssignRoleModal && selectedUser && (
        <div className="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
            <h3 className="font-bold text-slate-900 text-lg">Manage Roles: {selectedUser.first_name} {selectedUser.last_name}</h3>
            <form onSubmit={handleAssignRoles} className="space-y-3">
              <div className="space-y-2">
                {roles.map((r) => {
                  const isChecked = selectedRoleIds.includes(r.id);
                  return (
                    <label key={r.id} className="flex items-center gap-2 p-2 rounded hover:bg-slate-50 cursor-pointer">
                      <input
                        type="checkbox"
                        checked={isChecked}
                        onChange={(e) => {
                          if (e.target.checked) {
                            setSelectedRoleIds([...selectedRoleIds, r.id]);
                          } else {
                            setSelectedRoleIds(selectedRoleIds.filter(id => id !== r.id));
                          }
                        }}
                        className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                      />
                      <div>
                        <span className="font-medium text-slate-900 text-sm uppercase font-mono">{r.name}</span>
                        <p className="text-xs text-slate-500">{r.description}</p>
                      </div>
                    </label>
                  );
                })}
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowAssignRoleModal(false)}
                  className="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700"
                >
                  Save Roles
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
