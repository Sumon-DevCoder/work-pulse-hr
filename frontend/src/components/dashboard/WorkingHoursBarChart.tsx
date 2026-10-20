'use client';

import React, { useEffect, useState, useRef } from 'react';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ChartOptions,
} from 'chart.js';
import { Bar } from 'react-chartjs-2';
import { DashboardDailyBar } from '@/lib/api';
import { Clock, Users, Flame, RefreshCw } from 'lucide-react';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

interface WorkingHoursBarChartProps {
  chartData: DashboardDailyBar[];
  summary: {
    grand_total_hours: number;
    average_daily_hours: number;
    peak_day_hours: number;
  };
  dateRange: {
    from_date: string;
    to_date: string;
    total_days: number;
  };
  isLoading: boolean;
  onRefresh: () => void;
  lastRefreshed?: string;
}

export default function WorkingHoursBarChart({
  chartData,
  summary,
  dateRange,
  isLoading,
  onRefresh,
  lastRefreshed,
}: WorkingHoursBarChartProps) {
  const [countdown, setCountdown] = useState(30);

  // 30-Second countdown visualizer
  useEffect(() => {
    setCountdown(30);
    const interval = setInterval(() => {
      setCountdown((prev) => (prev > 1 ? prev - 1 : 30));
    }, 1000);

    return () => clearInterval(interval);
  }, [lastRefreshed]);

  const labels = chartData.map((d) => d.short_date);
  const values = chartData.map((d) => d.total_working_hours);

  const data = {
    labels,
    datasets: [
      {
        label: 'Total Working Hours',
        data: values,
        backgroundColor: (context: any) => {
          const ctx = context.chart.ctx;
          const gradient = ctx.createLinearGradient(0, 0, 0, 350);
          gradient.addColorStop(0, '#6366f1'); // Indigo 500
          gradient.addColorStop(1, '#06b6d4'); // Cyan 500
          return gradient;
        },
        hoverBackgroundColor: '#818cf8',
        borderRadius: 8,
        borderSkipped: false,
        barPercentage: chartData.length > 40 ? 0.9 : 0.75,
        categoryPercentage: 0.85,
      },
    ],
  };

  const options: ChartOptions<'bar'> = {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
      duration: 500,
    },
    plugins: {
      legend: {
        display: false,
      },
      tooltip: {
        backgroundColor: 'rgba(15, 23, 42, 0.95)',
        titleColor: '#f8fafc',
        bodyColor: '#cbd5e1',
        padding: 12,
        borderColor: 'rgba(99, 102, 241, 0.4)',
        borderWidth: 1,
        cornerRadius: 10,
        displayColors: false,
        callbacks: {
          title: (items) => {
            const index = items[0].dataIndex;
            return chartData[index]?.formatted_date || items[0].label;
          },
          label: (context) => {
            const index = context.dataIndex;
            const item = chartData[index];
            const hours = item?.total_working_hours || 0;
            const activeEmployees = item?.total_active_employees || 0;
            return [
              `Total Working Hours: ${hours.toLocaleString()} hrs`,
              `Active Employees: ${activeEmployees.toLocaleString()}`,
            ];
          },
        },
      },
    },
    scales: {
      x: {
        grid: {
          display: false,
        },
        ticks: {
          color: '#94a3b8',
          font: {
            size: chartData.length > 30 ? 10 : 12,
            weight: 500,
          },
          maxRotation: chartData.length > 20 ? 45 : 0,
          autoSkip: chartData.length > 60,
        },
      },
      y: {
        grid: {
          color: 'rgba(148, 163, 184, 0.1)',
        },
        ticks: {
          color: '#94a3b8',
          font: {
            size: 11,
          },
          callback: (value) => `${value}h`,
        },
      },
    },
  };

  // Dynamically calculate width if date range is large (e.g. 120 days) for smooth horizontal scrolling
  const minWidth = Math.max(chartData.length * 28, 800);

  return (
    <div className="glass-panel rounded-2xl p-6 border border-slate-800 shadow-2xl">
      {/* Top Header & Metrics */}
      <div className="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
          <div className="flex items-center gap-3">
            <h2 className="text-xl font-bold text-white tracking-tight">
              Daily Total Working Hours Analytics
            </h2>
            <span className="text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
              {chartData.length} Bars dynamically rendered ({dateRange.total_days} Days)
            </span>
          </div>
          <p className="text-xs text-slate-400 mt-1">
            Range: <span className="text-slate-200 font-medium">{dateRange.from_date}</span> to{' '}
            <span className="text-slate-200 font-medium">{dateRange.to_date}</span>
          </p>
        </div>

        {/* 30-Second Refresh Controller */}
        <div className="flex items-center gap-3">
          <div className="text-right">
            <div className="text-[11px] text-slate-400 font-medium">Auto-Refresh</div>
            <div className="text-xs font-bold text-emerald-400 flex items-center gap-1 justify-end">
              <span className="relative flex h-2 w-2">
                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span className="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
              </span>
              Next in {countdown}s
            </div>
          </div>

          <button
            type="button"
            onClick={onRefresh}
            disabled={isLoading}
            className="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-200 text-xs font-semibold border border-slate-700 transition-all cursor-pointer shadow-sm"
            title="Refresh now"
          >
            <RefreshCw className={`h-3.5 w-3.5 ${isLoading ? 'animate-spin text-indigo-400' : 'text-slate-300'}`} />
            Refresh
          </button>
        </div>
      </div>

      {/* KPI Cards Row */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div className="glass-card rounded-xl p-4 border border-slate-800 flex items-center gap-3.5">
          <div className="h-10 w-10 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
            <Clock className="h-5 w-5" />
          </div>
          <div>
            <div className="text-xs text-slate-400 font-medium">Grand Total Hours</div>
            <div className="text-lg font-bold text-white tracking-tight">
              {summary.grand_total_hours.toLocaleString()} <span className="text-xs text-slate-400 font-normal">hrs</span>
            </div>
          </div>
        </div>

        <div className="glass-card rounded-xl p-4 border border-slate-800 flex items-center gap-3.5">
          <div className="h-10 w-10 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400">
            <Users className="h-5 w-5" />
          </div>
          <div>
            <div className="text-xs text-slate-400 font-medium">Daily Average Hours</div>
            <div className="text-lg font-bold text-white tracking-tight">
              {summary.average_daily_hours.toLocaleString()} <span className="text-xs text-slate-400 font-normal">hrs/day</span>
            </div>
          </div>
        </div>

        <div className="glass-card rounded-xl p-4 border border-slate-800 flex items-center gap-3.5">
          <div className="h-10 w-10 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
            <Flame className="h-5 w-5" />
          </div>
          <div>
            <div className="text-xs text-slate-400 font-medium">Peak Day Total</div>
            <div className="text-lg font-bold text-white tracking-tight">
              {summary.peak_day_hours.toLocaleString()} <span className="text-xs text-slate-400 font-normal">hrs</span>
            </div>
          </div>
        </div>
      </div>

      {/* Chart Canvas Area */}
      <div className="relative overflow-x-auto pb-2">
        <div style={{ minWidth: `${minWidth}px`, height: '380px' }}>
          {chartData.length > 0 ? (
            <Bar data={data} options={options} />
          ) : (
            <div className="h-full flex items-center justify-center text-slate-500 text-sm">
              No punch log data available for the selected range.
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
