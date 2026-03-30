'use client';

import { useState, useEffect } from 'react';

interface AuthState {
  isAuthenticated: boolean;
  loading: boolean;
}

export function useAuth(): AuthState {
  const [state, setState] = useState<AuthState>({ isAuthenticated: false, loading: true });

  useEffect(() => {
    // Check if the session cookie exists (simple check, no API call)
    const hasSession = document.cookie.includes('hv-logged-in=');
    setState({ isAuthenticated: hasSession, loading: false });
  }, []);

  return state;
}
