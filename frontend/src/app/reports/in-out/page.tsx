'use client';

import React, { useState, useEffect } from 'react';
import Navbar from '@/components/Navbar';
import FilterBar from '@/components/ui/FilterBar';
import { fetchApi, InOutLogItem, PaginatedResponse, formatTime12h } from '@/lib/api';
import {
  FileSpreadsheet,
  Search,
  ChevronLeft,
  ChevronRight,
  ArrowUpDown,
  Filter,
  CheckCircle2,
  LogOut,
  LogIn,
} from 'lucide-react';

export default function InOutReportPage() {
  const [topFilters, setTopFilters] = useState<{ emp_id?: string; from_date?: string; to_date?: string }>({
    from_date: '2026-01-01',
    to_date: '2026-01-01',
  });
  const [globalSearch, setGlobalSearch] = useState('');
  const [columnFilters, setColumnFilters] = useState<{
    emp_id?: string;
    emp_name?: string;
    date_time?: string;
    in_out?: string;
  }>({});
  const [sortBy, setSortBy] = useState('date_time');
  const [sortDir, setSortDir] = useState<'asc' | 'desc'>('desc');
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(25);

  const [data, setData] = useState<PaginatedResponse<InOutLogItem> | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  // Fetch data on filters/page change
  useEffect(() => {
    let isMounted = true;
    setIsLoading(true);

    fetchApi<PaginatedResponse<InOutLogItem>>('/reports/in-out', {
      page,
      per_page: perPage,
      emp_id: topFilters.emp_id,
      from_date: topFilters.from_date,
      to_date: topFilters.to_date,
      search: globalSearch,
      sort_by: sortBy,
      sort_dir: sortDir,
      filters: columnFilters,
    })
      .then((res) => {
        if (isMounted) setData(res);
      })
      .catch((err) => console.error('Error fetching in-out report', err))
      .finally(() => {
        if (isMounted) setIsLoading(false);
      });

    return () => {
      isMounted = false;
    };
  }, [page, perPage, topFilters, globalSearch, columnFilters, sortBy, sortDir]);

  const handleSort = (column: string) => {
    if (sortBy === column) {
      setSortDir(sortDir === 'asc' ? 'desc' : 'asc');
    } else {
      setSortBy(column);
      setSortDir('asc');
    }
    setPage(1);
  };

  const updateColumnFilter = (column: string, value: string) => {
    setColumnFilters((prev) => ({ ...prev, [column]: value }));
    setPage(1);
  };

  return (
    <div className="min-h-screen flex flex-col">
      <Navbar />

      <main className="flex-1 max-w-7xl w-full mx-auto px-6 py-8">
        {/* Page Header */}
        <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
          <div>
            <div className="flex items-center gap-2.5">
              <span className="p-2 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
                <FileSpreadsheet className="h-6 w-6" />
              </span>
              <div>
                <h1 className="text-2xl font-black text-white tracking-tight">
                  Report 1: Employee In-Out Report
                </h1>
                <p className="text-xs text-slate-400">
                  Comprehensive attendance punch logs with multi-column filtering and global search.
                </p>
              </div>
            </div>
          </div>

          {/* Global Search Bar */}
          <div className="w-full sm:w-72 relative">
            <Search className="h-4 w-4 absolute left-3.5 top-3 text-slate-400" />
            <input
              type="text"
              placeholder="Search across all data..."
              value={globalSearch}
              onChange={(e) => {
                setGlobalSearch(e.target.value);
                setPage(1);
              }}
              className="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder:text-slate-500"
            />
          </div>
        </div>

        {/* Top Filters (Employee & Date Range) */}
        <FilterBar
          showEmployeeFilter={true}
          initialFromDate="2026-01-01"
          initialToDate="2026-01-01"
          onFilterChange={(newFilters) => {
            setTopFilters(newFilters);
            setPage(1);
          }}
        />

        {/* Data Table Panel */}
        <div className="glass-panel rounded-2xl border border-slate-800 shadow-2xl overflow-hidden">
          {/* Spec Header Banner */}
          <div className="flex flex-wrap items-center justify-between gap-3 px-6 py-4 bg-slate-900/95 border-b border-slate-800">
            <div>
              <h2 className="text-sm font-bold text-white tracking-wide uppercase">In-Out Report</h2>
              <p className="text-xs font-mono text-indigo-400 mt-0.5">
                From:{' '}
                <span className="text-slate-200">
                  {topFilters.from_date ? topFilters.from_date.split('-').reverse().join('-') : 'All'}
                </span>{' '}
                To:{' '}
                <span className="text-slate-200">
                  {topFilters.to_date ? topFilters.to_date.split('-').reverse().join('-') : 'All'}
                </span>
              </p>
            </div>
            {data?.meta && (
              <span className="text-xs text-slate-400 font-mono">
                Total Records: <strong className="text-indigo-300">{data.meta.total.toLocaleString()}</strong>
              </span>
            )}
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse text-sm">
              {/* Header */}
              <thead>
                <tr className="bg-slate-900/90 border-b border-slate-800 text-slate-300 font-semibold text-xs tracking-wider uppercase">
                  <th className="py-3.5 px-4 w-16 text-center">SL</th>
                  <th className="py-3.5 px-4">
                    <button
                      type="button"
                      onClick={() => handleSort('emp_id')}
                      className="flex items-center gap-1.5 hover:text-indigo-400 transition-colors cursor-pointer"
                    >
                      Employee ID
                      <ArrowUpDown className="h-3.5 w-3.5 text-slate-500" />
                    </button>
                  </th>
                  <th className="py-3.5 px-4">
                    <button
                      type="button"
                      onClick={() => handleSort('emp_name')}
                      className="flex items-center gap-1.5 hover:text-indigo-400 transition-colors cursor-pointer"
                    >
                      Employee Name
                      <ArrowUpDown className="h-3.5 w-3.5 text-slate-500" />
                    </button>
                  </th>
                  <th className="py-3.5 px-4">
                    <button
                      type="button"
                      onClick={() => handleSort('date_time')}
                      className="flex items-center gap-1.5 hover:text-indigo-400 transition-colors cursor-pointer"
                    >
                      Date Time
                      <ArrowUpDown className="h-3.5 w-3.5 text-slate-500" />
                    </button>
                  </th>
                  <th className="py-3.5 px-4">
                    <button
                      type="button"
                      onClick={() => handleSort('in_out')}
                      className="flex items-center gap-1.5 hover:text-indigo-400 transition-colors cursor-pointer"
                    >
                      In/Out Status
                      <ArrowUpDown className="h-3.5 w-3.5 text-slate-500" />
                    </button>
                  </th>
                </tr>

                {/* Individual Column Filter Row */}
                <tr className="bg-slate-900/50 border-b border-slate-800/80">
                  <th className="p-2 text-center text-slate-500 text-xs">
                    <Filter className="h-3.5 w-3.5 mx-auto" />
                  </th>
                  <th className="p-2">
                    <input
                      type="text"
                      placeholder="Filter ID..."
                      value={columnFilters.emp_id || ''}
                      onChange={(e) => updateColumnFilter('emp_id', e.target.value)}
                      className="w-full bg-slate-950/80 border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-300 focus:outline-none focus:border-indigo-500"
                    />
                  </th>
                  <th className="p-2">
                    <input
                      type="text"
                      placeholder="Filter Name..."
                      value={columnFilters.emp_name || ''}
                      onChange={(e) => updateColumnFilter('emp_name', e.target.value)}
                      className="w-full bg-slate-950/80 border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-300 focus:outline-none focus:border-indigo-500"
                    />
                  </th>
                  <th className="p-2">
                    <input
                      type="text"
                      placeholder="Filter YYYY-MM-DD..."
                      value={columnFilters.date_time || ''}
                      onChange={(e) => updateColumnFilter('date_time', e.target.value)}
                      className="w-full bg-slate-950/80 border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-300 focus:outline-none focus:border-indigo-500"
                    />
                  </th>
                  <th className="p-2">
                    <select
                      value={columnFilters.in_out || ''}
                      onChange={(e) => updateColumnFilter('in_out', e.target.value)}
                      className="w-full bg-slate-950/80 border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-300 focus:outline-none focus:border-indigo-500 cursor-pointer"
                    >
                      <option value="">All Status</option>
                      <option value="IN">IN</option>
                      <option value="OUT">OUT</option>
                    </select>
                  </th>
                </tr>
              </thead>

              {/* Body */}
              <tbody className="divide-y divide-slate-800/60">
                {isLoading ? (
                  <tr>
                    <td colSpan={5} className="py-16 text-center text-slate-400">
                      <div className="animate-spin inline-block h-6 w-6 border-2 border-indigo-500 border-t-transparent rounded-full mb-2"></div>
                      <p className="text-xs">Fetching server-side attendance logs...</p>
                    </td>
                  </tr>
                ) : data?.data && data.data.length > 0 ? (
                  data.data.map((row) => (
                    <tr
                      key={row.id}
                      className="hover:bg-slate-800/40 transition-colors group text-slate-200"
                    >
                      <td className="py-3 px-4 text-center text-xs font-mono text-slate-500">{row.sl}</td>
                      <td className="py-3 px-4 font-mono font-semibold text-xs text-indigo-300">
                        {row.emp_id}
                      </td>
                      <td className="py-3 px-4 font-medium text-slate-200">{row.emp_name}</td>
                      <td className="py-3 px-4">
                        {(() => {
                          const formatted = formatTime12h(row.date_time);
                          if (!formatted) return <span className="font-mono text-xs text-slate-300">{row.date_time}</span>;
                          return (
                            <div className="flex flex-col">
                              <span className="font-mono text-xs text-slate-200 font-medium inline-flex items-center gap-1.5">
                                {formatted.time}
                                <span className="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-800 text-indigo-300 border border-indigo-500/30">
                                  {formatted.ampm}
                                </span>
                              </span>
                              {formatted.date && (
                                <span className="text-[11px] text-slate-500 font-mono mt-0.5">
                                  {formatted.date}
                                </span>
                              )}
                            </div>
                          );
                        })()}
                      </td>
                      <td className="py-3 px-4">
                        <span
                          className={`inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold ${
                            row.in_out_status === 'IN'
                              ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30'
                              : 'bg-rose-500/15 text-rose-400 border border-rose-500/30'
                          }`}
                        >
                          {row.in_out_status === 'IN' ? (
                            <LogIn className="h-3 w-3" />
                          ) : (
                            <LogOut className="h-3 w-3" />
                          )}
                          {row.in_out_status}
                        </span>
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan={5} className="py-12 text-center text-slate-500 text-sm">
                      No matching records found.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>

          {/* Pagination Controls */}
          {data && data.meta && (
            <div className="p-4 bg-slate-900/80 border-t border-slate-800 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-400">
              <div>
                Showing <span className="font-semibold text-slate-200">{data.meta.from || 0}</span> to{' '}
                <span className="font-semibold text-slate-200">{data.meta.to || 0}</span> of{' '}
                <span className="font-semibold text-slate-200">{data.meta.total.toLocaleString()}</span> entries
              </div>

              <div className="flex items-center gap-2">
                <button
                  type="button"
                  disabled={data.meta.current_page <= 1}
                  onClick={() => setPage((p) => Math.max(p - 1, 1))}
                  className="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed text-slate-300 border border-slate-700 cursor-pointer transition-all"
                >
                  <ChevronLeft className="h-4 w-4" />
                  Previous
                </button>

                <span className="px-3 py-1.5 rounded-lg bg-indigo-600/20 text-indigo-400 font-semibold border border-indigo-500/30">
                  Page {data.meta.current_page} of {data.meta.last_page}
                </span>

                <button
                  type="button"
                  disabled={data.meta.current_page >= data.meta.last_page}
                  onClick={() => setPage((p) => p + 1)}
                  className="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed text-slate-300 border border-slate-700 cursor-pointer transition-all"
                >
                  Next
                  <ChevronRight className="h-4 w-4" />
                </button>
              </div>
            </div>
          )}
        </div>
      </main>
    </div>
  );
}
