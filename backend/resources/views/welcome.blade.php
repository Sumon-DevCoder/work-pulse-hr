<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorkPulse HR API — Enterprise RESTful Engine</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: radial-gradient(circle at 50% 0%, #1e293b 0%, #0f172a 60%, #020617 100%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2rem 1.5rem;
            overflow-x: hidden;
        }
        /* Strict cursor pointer on all interactive elements */
        button, a, [role="button"], .interactive-btn {
            cursor: pointer !important;
        }
        .container {
            max-width: 1040px;
            margin: 0 auto;
            width: 100%;
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(148, 163, 184, 0.15);
            border-radius: 1.25rem;
            padding: 2.5rem;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #3b82f6, #6366f1, #8b5cf6, transparent);
        }
        .header-badge-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }
        .logo-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.25rem;
            color: #fff;
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
        }
        .brand-title {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #fff;
        }
        .brand-subtitle {
            font-size: 0.8125rem;
            color: #94a3b8;
            font-weight: 500;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 9999px;
            color: #34d399;
            font-size: 0.8125rem;
            font-weight: 600;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 10px #10b981;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .hero {
            margin-bottom: 2.5rem;
        }
        .hero h1 {
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 0.75rem;
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero p {
            color: #94a3b8;
            font-size: 1.0625rem;
            line-height: 1.6;
            max-width: 680px;
        }
        .cta-box {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1.75rem;
        }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.9375rem;
            padding: 0.875rem 1.75rem;
            border-radius: 0.75rem;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
        }
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.2);
            color: #e2e8f0;
            font-weight: 600;
            font-size: 0.9375rem;
            padding: 0.875rem 1.5rem;
            border-radius: 0.75rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-secondary:hover {
            background: rgba(30, 41, 59, 0.9);
            border-color: rgba(148, 163, 184, 0.4);
            color: #ffffff;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2.5rem;
        }
        .metric-card {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
        }
        .metric-label {
            font-size: 0.8125rem;
            color: #94a3b8;
            font-weight: 500;
            margin-bottom: 0.375rem;
        }
        .metric-value {
            font-size: 1.625rem;
            font-weight: 800;
            color: #ffffff;
            font-feature-settings: "cv02", "cv03", "cv04", "cv11";
        }
        .metric-sub {
            font-size: 0.75rem;
            color: #3b82f6;
            margin-top: 0.25rem;
            font-weight: 600;
        }
        .endpoints-section h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .endpoint-list {
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
        }
        .endpoint-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 0.75rem;
            transition: all 0.2s ease;
            text-decoration: none;
            color: inherit;
        }
        .endpoint-row:hover {
            border-color: rgba(59, 130, 246, 0.4);
            background: rgba(30, 41, 59, 0.6);
            transform: translateX(4px);
        }
        .endpoint-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .badge-method {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
            padding: 0.25rem 0.625rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
        }
        .endpoint-uri {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.875rem;
            font-weight: 500;
            color: #e2e8f0;
        }
        .endpoint-desc {
            font-size: 0.8125rem;
            color: #94a3b8;
        }
        .endpoint-action {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            color: #38bdf8;
            font-size: 0.8125rem;
            font-weight: 600;
        }
        footer {
            text-align: center;
            color: #64748b;
            font-size: 0.8125rem;
            padding-top: 1rem;
        }
        footer a {
            color: #94a3b8;
            text-decoration: none;
        }
        footer a:hover {
            color: #38bdf8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <!-- Header Row -->
            <div class="header-badge-row">
                <div class="brand">
                    <div class="logo-icon">WP</div>
                    <div>
                        <div class="brand-title">WorkPulse HR</div>
                        <div class="brand-subtitle">Enterprise Attendance & High-Scale Analytics Engine</div>
                    </div>
                </div>
                <div class="status-pill">
                    <span class="status-dot"></span>
                    <span>API Online & Healthy</span>
                </div>
            </div>

            <!-- Hero Introduction -->
            <div class="hero">
                <h1>High-Performance RESTful API Service</h1>
                <p>
                    Backend engine powering the WorkPulse HR platform. Built with Laravel 13, running single-pass SQL aggregations over 1.14M records connected live to TiDB Cloud Serverless on AWS Tokyo.
                </p>
                <div class="cta-box">
                    <a href="https://work-pulse-hr-three.vercel.app" target="_blank" rel="noopener noreferrer" class="btn-primary">
                        <span>Launch Next.js Frontend App</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    </a>
                    <a href="/api/dashboard/analytics" target="_blank" class="btn-secondary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                        <span>View Analytics JSON Feed</span>
                    </a>
                </div>
            </div>

            <!-- Metrics Grid -->
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-label">Dataset Volume</div>
                    <div class="metric-value">1,136,436</div>
                    <div class="metric-sub">Attendance Punch Logs</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Database Infrastructure</div>
                    <div class="metric-value" style="font-size: 1.25rem; padding-top: 0.35rem;">TiDB Cloud</div>
                    <div class="metric-sub">Serverless Distributed MySQL</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Real-time Polling</div>
                    <div class="metric-value">30s</div>
                    <div class="metric-sub">Automated Background Sync</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">API Architecture</div>
                    <div class="metric-value" style="font-size: 1.25rem; padding-top: 0.35rem;">Headless REST</div>
                    <div class="metric-sub">Decoupled Next.js 16 + Laravel 13</div>
                </div>
            </div>

            <!-- Endpoints Section -->
            <div class="endpoints-section">
                <h2>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    Interactive API Endpoints
                </h2>
                <div class="endpoint-list">
                    <!-- Endpoint 1 -->
                    <a href="/api/dashboard/analytics" target="_blank" class="endpoint-row">
                        <div class="endpoint-left">
                            <span class="badge-method">GET</span>
                            <div>
                                <div class="endpoint-uri">/api/dashboard/analytics</div>
                                <div class="endpoint-desc">Dynamic bar chart feed matching date ranges (Default 30 days) with grand totals</div>
                            </div>
                        </div>
                        <div class="endpoint-action">
                            <span>Execute</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </div>
                    </a>

                    <!-- Endpoint 2 -->
                    <a href="/api/reports/in-out?page=1&per_page=10" target="_blank" class="endpoint-row">
                        <div class="endpoint-left">
                            <span class="badge-method">GET</span>
                            <div>
                                <div class="endpoint-uri">/api/reports/in-out</div>
                                <div class="endpoint-desc">Report 1: Paginated In-Out logs with column-level filters and global search</div>
                            </div>
                        </div>
                        <div class="endpoint-action">
                            <span>Execute</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </div>
                    </a>

                    <!-- Endpoint 3 -->
                    <a href="/api/reports/working-hours?page=1&per_page=10&from_date=2026-09-01&to_date=2026-09-07" target="_blank" class="endpoint-row">
                        <div class="endpoint-left">
                            <span class="badge-method">GET</span>
                            <div>
                                <div class="endpoint-uri">/api/reports/working-hours</div>
                                <div class="endpoint-desc">Report 2: First Login, Last Logout, and calculated Total Working Hours (X hrs Y mins)</div>
                            </div>
                        </div>
                        <div class="endpoint-action">
                            <span>Execute</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </div>
                    </a>

                    <!-- Endpoint 4 -->
                    <a href="/api/employees?limit=20" target="_blank" class="endpoint-row">
                        <div class="endpoint-left">
                            <span class="badge-method">GET</span>
                            <div>
                                <div class="endpoint-uri">/api/employees</div>
                                <div class="endpoint-desc">Searchable Employee directory for dropdown combobox filters</div>
                            </div>
                        </div>
                        <div class="endpoint-action">
                            <span>Execute</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <footer>
            WorkPulse HR Enterprise Assessment Platform &bull; Senior Laravel & Full-Stack Developer &bull; <a href="https://work-pulse-hr-three.vercel.app" target="_blank">Open Frontend UI</a>
        </footer>
    </div>
</body>
</html>
