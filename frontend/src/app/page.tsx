'use client';

import React, { useState } from 'react';
import useSWR from 'swr';
import Navbar from '@/components/Navbar';
import FilterBar from '@/components/ui/FilterBar';
import WorkingHoursBarChart from '@/components/dashboard/WorkingHoursBarChart';
import { fetchApi, DashboardAnalyticsResponse } from '@/lib/api';
import { BarChart3, FileSpreadsheet, Clock, ArrowRight } from 'lucide-react';
import Link from 'next/link';

export default function DashboardPage() {
  const [filters, setFilters] = useState<{ from_date?: string; to_date?: string }>({});

  // SWR Hook with automatic 30-second live polling (refreshInterval: 30000)
  const { data, error, isLoading, mutate } = useSWR<DashboardAnalyticsResponse>(
    ['/dashboard/analytics', filters],
    ([endpoint, params]) => fetchApi<DashboardAnalyticsResponse>(endpoint as string, params as Record<string, any>),
    {
      refreshInterval: 30000, // Exactly 30 seconds as specified in requirement
      revalidateOnFocus: true,
      dedupingInterval: 5000,
    }
  );

  const analytics = data?.data;

  return (
    <div className="min-h-screen flex flex-col">
      <Navbar lastRefreshed={analytics?.refreshed_at} />

      <main className="flex-1 max-w-7xl w-full mx-auto px-6 py-8">
        {/* Page Title */}
        <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
          <div>
            <h1 className="text-2xl font-black text-white tracking-tight flex items-center gap-2.5">
              <BarChart3 className="h-7 w-7 text-indigo-400" />
              WorkPulse Analytics Dashboard
            </h1>
            <p className="text-sm text-slate-400 mt-1">
              Dynamic real-time visualization of daily working hours with automated 30s polling.
            </p>
          </div>

          <div className="flex items-center gap-3">
            <Link
              href="/reports/in-out"
              className="flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900/80 hover:bg-slate-800 text-xs font-semibold text-slate-300 border border-slate-700/80 transition-all cursor-pointer"
            >
              <FileSpreadsheet className="h-4 w-4 text-indigo-400" />
              View In-Out Report
            </Link>

            <Link
              href="/reports/working-hours"
              className="flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900/80 hover:bg-slate-800 text-xs font-semibold text-slate-300 border border-slate-700/80 transition-all cursor-pointer"
            >
              <Clock className="h-4 w-4 text-cyan-400" />
              View Working Hours Report
            </Link>
          </div>
        </div>

        {/* Date Range Filter Bar */}
        <FilterBar
          showEmployeeFilter={false}
          onFilterChange={(newFilters) => {
            setFilters({
              from_date: newFilters.from_date,
              to_date: newFilters.to_date,
            });
          }}
        />

        {/* Dynamic Bar Chart */}
        {analytics ? (
          <WorkingHoursBarChart
            chartData={analytics.chart_data}
            summary={analytics.summary}
            dateRange={analytics.date_range}
            isLoading={isLoading}
            onRefresh={() => mutate()}
            lastRefreshed={analytics.refreshed_at}
          />
        ) : (
          <div className="glass-panel rounded-2xl p-16 text-center border border-slate-800">
            <div className="animate-spin inline-block h-8 w-8 border-4 border-indigo-500 border-t-transparent rounded-full mb-4"></div>
            <p className="text-slate-400 text-sm">Aggregating attendance records from MySQL...</p>
          </div>
        )}

        {/* Assessment Task Navigation Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
          <Link
            href="/reports/in-out"
            className="group glass-card rounded-2xl p-6 border border-slate-800 hover:border-indigo-500/50 hover:bg-slate-800/40 transition-all duration-300 cursor-pointer shadow-lg"
          >
            <div className="flex items-start justify-between">
              <div className="h-12 w-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 mb-4 group-hover:scale-110 transition-all">
                <FileSpreadsheet className="h-6 w-6" />
              </div>
              <span className="text-slate-500 group-hover:text-indigo-400 transition-colors">
                <ArrowRight className="h-5 w-5 transform group-hover:translate-x-1 transition-transform" />
              </span>
            </div>
            <h3 className="text-lg font-bold text-white group-hover:text-indigo-300 transition-colors">
              Report 1: Attendance In-Out Log
            </h3>
            <p className="text-xs text-slate-400 mt-2 leading-relaxed">
              Employee and date-wise attendance log table featuring global search, individual column-wise filters, and full pagination over 1.1M+ machine punch records.
            </p>
          </Link>

          <Link
            href="/reports/working-hours"
            className="group glass-card rounded-2xl p-6 border border-slate-800 hover:border-cyan-500/50 hover:bg-slate-800/40 transition-all duration-300 cursor-pointer shadow-lg"
          >
            <div className="flex items-start justify-between">
              <div className="h-12 w-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 mb-4 group-hover:scale-110 transition-all">
                <Clock className="h-6 w-6" />
              </div>
              <span className="text-slate-500 group-hover:text-cyan-400 transition-colors">
                <ArrowRight className="h-5 w-5 transform group-hover:translate-x-1 transition-transform" />
              </span>
            </div>
            <h3 className="text-lg font-bold text-white group-hover:text-cyan-300 transition-colors">
              Report 2: Total Working Hours Summary
            </h3>
            <p className="text-xs text-slate-400 mt-2 leading-relaxed">
              Consolidated employee daily punch summary computing first login time, last logout time, and calculated total duration with column filters and sorting.
            </p>
          </Link>
        </div>
      </main>
    </div>
  );
}
