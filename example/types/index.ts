/**
 * Type definitions for the application
 */

// ============= Orders =============

export interface OrderCustomer {
  name: string;
  phone: string;
}

export interface OrderHistory {
  at: string;
  status: string;
  note?: string;
}

// Auto-calculated order
export interface AutoCalcOrder {
  id: string;
  publicNumber: string;
  type: "AUTO_CALC";
  createdAt: string;
  status: string;
  paymentStatus: string;
  upload: {
    originalName: string;
    storedPath: string;
    sizeBytes: number;
  };
  calc: {
    volumeMm3: number;
    gramsOne: number;
    hoursOne: number;
    total: number;
    discount: number;
    breakdown: {
      materialCost: number;
      machineCost: number;
      setupFee: number;
      supportsFee: number;
      subtotal: number;
    };
  };
  params: {
    material: string;
    quality: string;
    infill: number;
    qty: number;
    supports: boolean;
    rush: boolean;
  };
  customer: OrderCustomer;
  adminNotes: string;
  history: OrderHistory[];
}

// Manual review order
export interface ManualReviewOrder {
  id: string;
  publicNumber: string;
  type: "MANUAL_REVIEW";
  createdAt: string;
  status: string;
  purpose: string;
  deadline?: string;
  description: string;
  fileIds: string[];
  itemType?: string;
  approxSize?: string;
  userPriority?: string;
  customer: OrderCustomer;
  adminNotes: string;
  history: OrderHistory[];
}

export type Order = AutoCalcOrder | ManualReviewOrder;

// ============= Portfolio =============

export interface PortfolioCategory {
  id: string;
  name: string;
  description: string;
}

export interface PortfolioProject {
  id: string;
  title: string;
  description: string;
  category: string;
  images: string[];
  glbModel?: string;
  featured: boolean;
  createdAt: string;
}

export interface Portfolio {
  projects: PortfolioProject[];
  categories: PortfolioCategory[];
}

// ============= Pricing =============

export interface Material {
  density: number;
  pricePerGram: number;
  ratePerHour: number;
  setupFee: number;
}

export interface QualitySetting {
  layer: number;
  timeCoeff: number;
  priceCoeff: number;
  baseHours: number;
}

export interface PricingConfig {
  materials: Record<string, Material>;
  qualities: Record<string, QualitySetting>;
}

// ============= API Requests =============

export interface CalcRequest {
  bbox: { x: number; y: number; z: number };
  material: string;
  quality: "DRAFT" | "STANDARD" | "FINE";
  infill: number;
  qty: number;
  supports: boolean;
  rush: boolean;
}

export interface CreateOrderRequest {
  type: "AUTO_CALC" | "MANUAL_REVIEW";
  upload?: any;
  calc?: any;
  params?: any;
  customer?: OrderCustomer;
  purpose?: string;
  deadline?: string;
  description?: string;
  fileIds?: string[];
  itemType?: string;
  approxSize?: string;
  userPriority?: string;
  phone?: string;
  name?: string;
}

// ============= API Responses =============

export interface ApiResponse<T = any> {
  data?: T;
  error?: string;
}

export interface CalculateResponse {
  volumeMm3: number;
  gramsOne: number;
  hoursOne: number;
  total: number;
  discount: number;
  breakdown: {
    materialCost: number;
    machineCost: number;
    setupFee: number;
    supportsFee: number;
    subtotal: number;
  };
  note: string;
}

export interface CreateOrderResponse {
  publicNumber: string;
  payUrl?: string;
  statusUrl: string;
}

export interface UploadResponse {
  fileIds: string[];
}

// ============= UI State =============

export interface FormState<T> {
  values: T;
  errors: Record<string, string>;
  touched: Record<string, boolean>;
}

export interface AsyncState<T, E = string> {
  data: T | null;
  loading: boolean;
  error: E | null;
}

export interface LoadingState {
  loading: boolean;
  error: string | null;
  success: any | null;
}

// ============= Utility Types =============

export type OrderStatus =
  | "WAITING_PAYMENT"
  | "PAID"
  | "IN_PROGRESS"
  | "COMPLETED"
  | "PENDING_REVIEW";

export type PaymentStatus = "WAITING_PAYMENT" | "PAID";

export type QualityType = "DRAFT" | "STANDARD" | "FINE";

export type MaterialType = "PETG" | "ASA" | "PA" | "COPA";

export type PortfolioCategoryKey = "podium" | "figurine" | "prototype" | "custom";

// ============= Component Props =============

export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: "primary" | "secondary" | "danger";
  size?: "default" | "small";
}

export interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
}

export interface SelectProps extends React.SelectHTMLAttributes<HTMLSelectElement> {
  label?: string;
  error?: string;
  options?: Array<{ value: string; label: string }>;
}

export interface CardProps extends React.HTMLAttributes<HTMLDivElement> {
  size?: "default" | "small";
}

export interface AlertProps extends React.HTMLAttributes<HTMLDivElement> {
  variant?: "error" | "success" | "warning" | "info";
}

export interface TabsProps {
  tabs: Array<{ id: string; label: string; count?: number }>;
  activeTab: string;
  onTabChange: (tabId: string) => void;
}
