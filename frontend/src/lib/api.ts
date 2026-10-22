export function getApiBaseUrl(): string {
  const envUrl = process.env.NEXT_PUBLIC_API_URL;
  if (typeof window !== 'undefined') {
    const isRemoteHost = window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1';
    if (isRemoteHost && (!envUrl || envUrl.includes('localhost') || envUrl.includes('127.0.0.1'))) {
      return 'https://workpulse-api-6g14.onrender.com/api';
    }
  }
  return envUrl || 'http://localhost:8000/api';
}

const API_BASE_URL = getApiBaseUrl();

export interface Employee {
  id: string | number;
  emp_id: string;
  emp_name: string;
  designation: string;
  card_id?: string;
}

export interface InOutLogItem {
  sl: number;
  id: number;
  emp_id: string;
  emp_name: string;
  date: string;
  date_time: string;
  date_time_12h?: string | null;
  in_out: string;
  in_out_status: 'IN' | 'OUT';
  terminal_id: string;
  attendance_type: string;
}

export interface WorkingHourItem {
  sl: number;
  emp_id: string;
  emp_name: string;
  designation: string;
  date: string;
  login_time: string | null;
  login_time_12h?: string | null;
  logout_time: string | null;
  logout_time_12h?: string | null;
  working_hour: string;
  working_hours_decimal: number;
  working_seconds: number;
}

export function formatTime12h(dateTimeStr?: string | null): { date: string; time: string; ampm: 'AM' | 'PM' } | null {
  if (!dateTimeStr) return null;
  const parts = dateTimeStr.trim().split(' ');
  const datePart = parts.length > 1 ? parts[0] : '';
  const timePart = parts.length > 1 ? parts[1] : parts[0];

  const timeMatch = timePart.split(':');
  if (timeMatch.length < 2) return null;

  let hour = parseInt(timeMatch[0], 10);
  const min = timeMatch[1];
  const sec = timeMatch[2] || '00';

  if (isNaN(hour)) return null;

  const ampm: 'AM' | 'PM' = hour >= 12 ? 'PM' : 'AM';
  hour = hour % 12;
  hour = hour ? hour : 12;
  const formattedHour = String(hour).padStart(2, '0');

  return {
    date: datePart,
    time: `${formattedHour}:${min}:${sec}`,
    ampm,
  };
}

export interface PaginatedResponse<T> {
  status: string;
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
  };
}

export interface DashboardDailyBar {
  date: string;
  formatted_date: string;
  short_date: string;
  total_working_hours: number;
  total_active_employees: number;
}

export interface DashboardAnalyticsResponse {
  status: string;
  data: {
    date_range: {
      from_date: string;
      to_date: string;
      total_days: number;
    };
    summary: {
      grand_total_hours: number;
      average_daily_hours: number;
      peak_day_hours: number;
    };
    bars_count: number;
    chart_data: DashboardDailyBar[];
    refreshed_at: string;
  };
}

export async function fetchApi<T>(endpoint: string, params: Record<string, any> = {}): Promise<T> {
  const query = new URLSearchParams();

  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      if (typeof value === 'object') {
        Object.entries(value).forEach(([subKey, subVal]) => {
          if (subVal !== undefined && subVal !== null && subVal !== '') {
            query.append(`filters[${subKey}]`, String(subVal));
          }
        });
      } else {
        query.append(key, String(value));
      }
    }
  });

  const queryString = query.toString();
  const baseUrl = getApiBaseUrl();
  const url = `${baseUrl}${endpoint}${queryString ? `?${queryString}` : ''}`;

  const res = await fetch(url, {
    headers: {
      'Accept': 'application/json',
    },
    cache: 'no-store',
  });

  if (!res.ok) {
    throw new Error(`API error: ${res.status} ${res.statusText}`);
  }

  return res.json();
}
