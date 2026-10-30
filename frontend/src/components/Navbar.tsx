'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { Activity, BarChart3, FileSpreadsheet, Clock, Menu, X } from 'lucide-react';

export default function Navbar({ lastRefreshed }: { lastRefreshed?: string }) {
  const pathname = usePathname();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const navItems = [
    { name: 'Dashboard', href: '/', icon: BarChart3 },
    { name: 'Report 1: In-Out Log', href: '/reports/in-out', icon: FileSpreadsheet },
    { name: 'Report 2: Working Hours', href: '/reports/working-hours', icon: Clock },
  ];

  return (
    <header className="sticky top-0 z-50 glass-panel border-b border-slate-800/80 px-4 sm:px-6 py-3 shadow-lg">
      <div className="max-w-7xl mx-auto flex items-center justify-between gap-3">
        {/* Brand Logo */}
        <Link href="/" className="flex items-center gap-2.5 sm:gap-3 group cursor-pointer shrink-0">
          <div className="h-9 w-9 sm:h-10 sm:w-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-cyan-400 flex items-center justify-center shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition-all duration-300">
            <Activity className="h-5 w-5 sm:h-6 sm:w-6 text-white animate-pulse" />
          </div>
          <div>
            <div className="flex items-center gap-1.5 sm:gap-2">
              <span className="text-lg sm:text-xl font-bold bg-gradient-to-r from-white via-slate-100 to-indigo-200 bg-clip-text text-transparent tracking-tight">
                WorkPulse
              </span>
              <span className="text-[10px] sm:text-xs font-semibold px-1.5 sm:px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                HR
              </span>
            </div>
            <p className="text-[10px] sm:text-[11px] text-slate-400 font-medium hidden sm:block">
              Enterprise Attendance & Analytics
            </p>
          </div>
        </Link>

        {/* Desktop Navigation Tabs */}
        <nav className="hidden lg:flex items-center gap-1.5 p-1 rounded-xl bg-slate-900/60 border border-slate-800">
          {navItems.map((item) => {
            const Icon = item.icon;
            const isActive = pathname === item.href;
            return (
              <Link
                key={item.href}
                href={item.href}
                className={`flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 cursor-pointer ${
                  isActive
                    ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30 font-semibold'
                    : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/50'
                }`}
              >
                <Icon className={`h-4 w-4 ${isActive ? 'text-white' : 'text-slate-400'}`} />
                {item.name}
              </Link>
            );
          })}
        </nav>

        {/* Right Section: Real-time Heartbeat Badge & Mobile Menu Button */}
        <div className="flex items-center gap-2 sm:gap-3">
          {/* Real-time Heartbeat Badge */}
          <div className="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs bg-slate-900/80 border border-slate-800 rounded-full px-2.5 sm:px-3.5 py-1 sm:py-1.5 text-slate-300 shadow-inner">
            <span className="relative flex h-2 w-2">
              <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span className="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span className="hidden sm:inline text-slate-400">Live Polling:</span>
            <span className="font-semibold text-emerald-400">30s<span className="hidden sm:inline"> Auto</span></span>
            {lastRefreshed && (
              <span className="hidden md:inline text-[11px] text-slate-500 border-l border-slate-800 pl-2">
                {new Date(lastRefreshed).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })}
              </span>
            )}
          </div>

          {/* Mobile Menu Hamburger Button */}
          <button
            type="button"
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
            className="lg:hidden p-2 rounded-xl bg-slate-900/80 border border-slate-800 text-slate-300 hover:text-white hover:bg-slate-800 transition-all cursor-pointer focus:outline-none"
            aria-label="Toggle navigation menu"
          >
            {mobileMenuOpen ? <X className="h-5 w-5 cursor-pointer" /> : <Menu className="h-5 w-5 cursor-pointer" />}
          </button>
        </div>
      </div>

      {/* Mobile Navigation Drawer / Dropdown */}
      {mobileMenuOpen && (
        <div className="lg:hidden pt-3 pb-2 border-t border-slate-800/80 mt-3 space-y-1">
          {navItems.map((item) => {
            const Icon = item.icon;
            const isActive = pathname === item.href;
            return (
              <Link
                key={item.href}
                href={item.href}
                onClick={() => setMobileMenuOpen(false)}
                className={`flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 cursor-pointer ${
                  isActive
                    ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30 font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                }`}
              >
                <Icon className={`h-4 w-4 ${isActive ? 'text-white' : 'text-slate-400'}`} />
                {item.name}
              </Link>
            );
          })}
        </div>
      )}
    </header>
  );
}
