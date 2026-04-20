// @ts-ignore - vitest types will be available after npm install
import { vi } from 'vitest';
import '@testing-library/jest-dom';

// Mock window.matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation((query: string) => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(),
    removeListener: vi.fn(),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  })),
});

// Mock CustomEvent
global.CustomEvent = class CustomEvent extends Event {
  detail: any;
  constructor(type: string, eventInitDict?: CustomEventInit) {
    super(type, eventInitDict);
    this.detail = eventInitDict?.detail;
  }
} as any;
