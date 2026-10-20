'use client';

import React, { useEffect, useState } from 'react';
import { Search, Calendar, User, RotateCcw, Filter } from 'lucide-react';
import { fetchApi, Employee } from '@/lib/api';

interface FilterBarProps {
  onFilterChange: (filters: { emp_id?: string; from_date?: string; to_date?: string }) => void;
  initialEmpId?: string;
  initialFromDate?: string;
  initialToDate?: string;
  showEmployeeFilter?: boolean;
}

export default function FilterBar({
  onFilterChange,
  initialEmpId = '',
  initialFromDate = '',
  initialToDate = '',
  showEmployeeFilter = true,
}: FilterBarProps) {
  const [empId, setEmpId] = useState(initialEmpId);
  const [fromDate, setFromDate] = useState(initialFromDate);
  const [toDate, setToDate] = useState(initialToDate);
  const [employees, setEmployees] = useState<Employee[]>([]);
  const [empSearch, setEmpSearch] = useState('');
  const [loadingEmployees, setLoadingEmployees] = useState(false);

  // Fetch initial employee list
  useEffect(() => {
    if (!showEmployeeFilter) return;

    let isMounted = true;
    setLoadingEmployees(true);

    fetchApi<{ data: Employee[] }>('/employees', { limit: 50, search: empSearch })
      .then((res) => {
        if (isMounted) {
          setEmployees(res.data || []);
        }
      })
      .catch((err) => console.error('Failed to load employees', err))
      .finally(() => {
        if (isMounted) setLoadingEmployees(false);
      });

    return () => {
      isMounted = false;
    };
  }, [empSearch, showEmployeeFilter]);

  const handleApply = (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    onFilterChange({
      emp_id: empId === 'all' ? '' : empId,
      from_date: fromDate,
      to_date: toDate,
    });
  };

  const handleReset = () => {
    setEmpId('');
    setFromDate('');
    setToDate('');
    onFilterChange({ emp_id: '', from_date: '', to_date: '' });
  };

  // Quick Preset Handlers for fast assessment testing
  const applyPreset = (preset: '30' | '10' | 'august' | 'september') => {
    let from = '';
    let to = '';

    if (preset === '30') {
      from = '2026-08-19';
      to = '2026-09-17';
    } else if (preset === '10') {
      from = '2026-09-08';
      to = '2026-09-17';
    } else if (preset === 'august') {
      from = '2026-08-01';
      to = '2026-08-31';
    } else if (preset === 'september') {
      from = '2026-09-01';
      to = '2026-09-17';
    }

    setFromDate(from);
    setToDate(to);
    onFilterChange({ emp_id: empId === 'all' ? '' : empId, from_date: from, to_date: to });
  };

  return (
    <div className="glass-card rounded-2xl p-5 border border-slate-800 shadow-xl mb-6">
      <form onSubmit={handleApply} className="flex flex-wrap items-end gap-4">
        {/* Employee Filter */}
        {showEmployeeFilter && (
          <div className="flex-1 min-w-[240px]">
            <label className="block text-xs font-semibold text-slate-400 mb-1.5 flex items-center gap-1.5">
              <User className="h-3.5 w-3.5 text-indigo-400" />
              Employee Filter
            </label>
            <div className="relative">
              <select
                value={empId}
                onChange={(e) => setEmpId(e.target.value)}
                className="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 cursor-pointer transition-all"
              >
                <option value="">All Employees (Default)</option>
                <option value="198276">198276 - Mr. ABC (Assessment Spec)</option>
                {employees.map((emp) => (
                  <option key={emp.id} value={emp.id}>
                    {emp.id} - {emp.emp_name} ({emp.designation})
                  </option>
                ))}
              </select>
            </div>
          </div>
        )}

        {/* Date Range: From */}
        <div className="w-[180px]">
          <label className="block text-xs font-semibold text-slate-400 mb-1.5 flex items-center gap-1.5">
            <Calendar className="h-3.5 w-3.5 text-indigo-400" />
            From Date
          </label>
          <input
            type="date"
            value={fromDate}
            onChange={(e) => setFromDate(e.target.value)}
            className="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 cursor-pointer transition-all"
          />
        </div>

        {/* Date Range: To */}
        <div className="w-[180px]">
          <label className="block text-xs font-semibold text-slate-400 mb-1.5 flex items-center gap-1.5">
            <Calendar className="h-3.5 w-3.5 text-indigo-400" />
            To Date
          </label>
          <input
            type="date"
            value={toDate}
            onChange={(e) => setToDate(e.target.value)}
            className="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 cursor-pointer transition-all"
          />
        </div>

        {/* Action Buttons */}
        <div className="flex items-center gap-2">
          <button
            type="submit"
            className="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white text-sm font-semibold shadow-lg shadow-indigo-600/30 transition-all cursor-pointer"
          >
            <Filter className="h-4 w-4" />
            Apply Filters
          </button>

          <button
            type="button"
            onClick={handleReset}
            className="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 active:scale-95 text-slate-300 text-sm font-medium border border-slate-700 transition-all cursor-pointer"
            title="Reset Filters"
          >
            <RotateCcw className="h-4 w-4" />
            Reset
          </button>
        </div>
      </form>

      {/* Quick Filter Presets */}
      <div className="mt-4 pt-3.5 border-t border-slate-800/80 flex items-center gap-2 flex-wrap">
        <span className="text-xs text-slate-400 font-medium mr-1">Quick Presets:</span>
        <button
          type="button"
          onClick={() => applyPreset('30')}
          className="text-xs px-2.5 py-1 rounded-lg bg-indigo-950/60 text-indigo-300 border border-indigo-800/40 hover:bg-indigo-900/60 cursor-pointer transition-all"
        >
          Default (Last 30 Days)
        </button>
        <button
          type="button"
          onClick={() => applyPreset('10')}
          className="text-xs px-2.5 py-1 rounded-lg bg-slate-800/60 text-slate-300 border border-slate-700 hover:bg-slate-700/60 cursor-pointer transition-all"
        >
          10 Days (10 Bars Test)
        </button>
        <button
          type="button"
          onClick={() => applyPreset('august')}
          className="text-xs px-2.5 py-1 rounded-lg bg-slate-800/60 text-slate-300 border border-slate-700 hover:bg-slate-700/60 cursor-pointer transition-all"
        >
          August 2026
        </button>
        <button
          type="button"
          onClick={() => applyPreset('september')}
          className="text-xs px-2.5 py-1 rounded-lg bg-slate-800/60 text-slate-300 border border-slate-700 hover:bg-slate-700/60 cursor-pointer transition-all"
        >
          September 2026
        </button>
      </div>
    </div>
  );
}
