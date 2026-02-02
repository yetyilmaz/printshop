/**
 * UI Components Library
 * Переиспользуемые компоненты и стили для соответствия принципу DRY
 */

import React from "react";

// ============= Стили =============
export const STYLES = {
  // Кнопки
  button: {
    primary: "px-4 py-2 bg-black text-white rounded-xl hover:bg-gray-900 disabled:bg-gray-400",
    secondary: "px-4 py-2 border rounded-xl text-sm hover:bg-gray-50",
    small: "px-3 py-1 text-xs rounded-xl",
    danger: "px-3 py-1 bg-red-600 text-white rounded-xl hover:bg-red-700 text-sm",
  },

  // Контейнеры
  container: {
    card: "rounded-2xl border p-6",
    cardSmall: "rounded-2xl border p-4",
    section: "space-y-6",
    subsection: "space-y-4",
    form: "space-y-3",
  },

  // Текст
  text: {
    label: "text-sm font-medium",
    secondary: "text-xs text-gray-600",
    muted: "text-gray-600",
    error: "text-red-600",
    success: "text-green-600",
  },

  // Формы
  form: {
    input: "w-full border rounded-xl p-2",
    textarea: "w-full border rounded-xl p-2 min-h-16",
    select: "w-full border rounded-xl p-2",
  },

  // Таблицы
  table: {
    cell: "p-3 border",
    headerCell: "text-left p-3 border",
    row: "border-b hover:bg-gray-50",
  },

  // Модали
  modal: {
    overlay: "fixed inset-0 bg-black/50 flex items-center justify-center z-50",
    content: "bg-white rounded-2xl p-6 max-w-lg w-full",
    header: "flex justify-between items-center mb-4",
  },

  // Alert'ы
  alert: {
    error: "text-red-600 bg-red-50 p-4 rounded-xl",
    success: "text-green-600 bg-green-50 p-4 rounded-xl",
    warning: "text-yellow-600 bg-yellow-50 p-4 rounded-xl",
    info: "text-blue-600 bg-blue-50 p-4 rounded-xl",
  },

  // Состояния
  badge: {
    primary: "px-2 py-1 rounded text-xs bg-blue-100 text-blue-800",
    success: "px-2 py-1 rounded text-xs bg-green-100 text-green-800",
    warning: "px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800",
    danger: "px-2 py-1 rounded text-xs bg-red-100 text-red-800",
  },
};

// ============= Компоненты =============

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: "primary" | "secondary" | "danger";
  size?: "default" | "small";
}

export const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ variant = "primary", size = "default", className = "", ...props }, ref) => {
    const variantClass =
      variant === "primary"
        ? STYLES.button.primary
        : variant === "danger"
          ? STYLES.button.danger
          : STYLES.button.secondary;

    const sizeClass = size === "small" ? STYLES.button.small : "";

    return (
      <button
        ref={ref}
        className={`${variantClass} ${sizeClass} ${className}`.trim()}
        {...props}
      />
    );
  }
);
Button.displayName = "Button";

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
}

export const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ label, error, className = "", ...props }, ref) => {
    return (
      <div className="flex flex-col gap-1">
        {label && <label className={STYLES.text.label}>{label}</label>}
        <input
          ref={ref}
          className={`${STYLES.form.input} ${error ? "border-red-500" : ""} ${className}`.trim()}
          {...props}
        />
        {error && <span className={STYLES.text.error}>{error}</span>}
      </div>
    );
  }
);
Input.displayName = "Input";

interface TextareaProps extends React.TextareaHTMLAttributes<HTMLTextAreaElement> {
  label?: string;
  error?: string;
}

export const Textarea = React.forwardRef<HTMLTextAreaElement, TextareaProps>(
  ({ label, error, className = "", ...props }, ref) => {
    return (
      <div className="flex flex-col gap-1">
        {label && <label className={STYLES.text.label}>{label}</label>}
        <textarea
          ref={ref}
          className={`${STYLES.form.textarea} ${error ? "border-red-500" : ""} ${className}`.trim()}
          {...props}
        />
        {error && <span className={STYLES.text.error}>{error}</span>}
      </div>
    );
  }
);
Textarea.displayName = "Textarea";

interface SelectProps extends React.SelectHTMLAttributes<HTMLSelectElement> {
  label?: string;
  error?: string;
  options?: Array<{ value: string; label: string }>;
}

export const Select = React.forwardRef<HTMLSelectElement, SelectProps>(
  ({ label, error, options = [], className = "", ...props }, ref) => {
    return (
      <div className="flex flex-col gap-1">
        {label && <label className={STYLES.text.label}>{label}</label>}
        <select
          ref={ref}
          className={`${STYLES.form.select} ${error ? "border-red-500" : ""} ${className}`.trim()}
          {...props}
        >
          {options.map((opt) => (
            <option key={opt.value} value={opt.value}>
              {opt.label}
            </option>
          ))}
        </select>
        {error && <span className={STYLES.text.error}>{error}</span>}
      </div>
    );
  }
);
Select.displayName = "Select";

interface CardProps extends React.HTMLAttributes<HTMLDivElement> {
  size?: "default" | "small";
}

export const Card = React.forwardRef<HTMLDivElement, CardProps>(
  ({ size = "default", className = "", ...props }, ref) => {
    const sizeClass = size === "small" ? STYLES.container.cardSmall : STYLES.container.card;
    return <div ref={ref} className={`${sizeClass} ${className}`.trim()} {...props} />;
  }
);
Card.displayName = "Card";

interface AlertProps extends React.HTMLAttributes<HTMLDivElement> {
  variant?: "error" | "success" | "warning" | "info";
}

export const Alert = ({ variant = "info", className = "", ...props }: AlertProps) => {
  const variantClass =
    variant === "error"
      ? STYLES.alert.error
      : variant === "success"
        ? STYLES.alert.success
        : variant === "warning"
          ? STYLES.alert.warning
          : STYLES.alert.info;

  return <div className={`${variantClass} ${className}`.trim()} {...props} />;
};

interface BadgeProps extends React.HTMLAttributes<HTMLSpanElement> {
  variant?: "primary" | "success" | "warning" | "danger";
}

export const Badge = ({ variant = "primary", className = "", ...props }: BadgeProps) => {
  const variantClass =
    variant === "success"
      ? STYLES.badge.success
      : variant === "warning"
        ? STYLES.badge.warning
        : variant === "danger"
          ? STYLES.badge.danger
          : STYLES.badge.primary;

  return <span className={`${variantClass} ${className}`.trim()} {...props} />;
};

// ============= Вспомогательные компоненты =============

interface LoadingProps {
  text?: string;
}

export const Loading = ({ text = "Загрузка..." }: LoadingProps) => (
  <div className={STYLES.text.muted}>{text}</div>
);

interface EmptyStateProps {
  text?: string;
}

export const EmptyState = ({ text = "Нет данных" }: EmptyStateProps) => (
  <div className={`${STYLES.container.card} ${STYLES.text.muted} text-center`}>
    {text}
  </div>
);

interface TabProps {
  tabs: Array<{ id: string; label: string; count?: number }>;
  activeTab: string;
  onTabChange: (tabId: string) => void;
}

export const Tabs = ({ tabs, activeTab, onTabChange }: TabProps) => (
  <div className="flex gap-4 border-b">
    {tabs.map((tab) => (
      <button
        key={tab.id}
        onClick={() => onTabChange(tab.id)}
        className={`px-4 py-2 font-medium ${
          activeTab === tab.id ? "border-b-2 border-black" : "text-gray-600"
        }`}
      >
        {tab.label}
        {tab.count !== undefined && ` (${tab.count})`}
      </button>
    ))}
  </div>
);

interface FormActionsProps {
  onSubmit?: () => void;
  onCancel?: () => void;
  submitText?: string;
  cancelText?: string;
  loading?: boolean;
  variant?: "primary" | "secondary";
}

export const FormActions = ({
  onSubmit,
  onCancel,
  submitText = "Сохранить",
  cancelText = "Отмена",
  loading = false,
  variant = "primary",
}: FormActionsProps) => (
  <div className="flex gap-2">
    <Button
      onClick={onSubmit}
      disabled={loading}
      variant={variant}
      className="flex-1"
    >
      {loading ? "Отправка..." : submitText}
    </Button>
    {onCancel && (
      <Button
        onClick={onCancel}
        variant="secondary"
        className="flex-1"
        disabled={loading}
      >
        {cancelText}
      </Button>
    )}
  </div>
);

interface SectionProps extends React.HTMLAttributes<HTMLDivElement> {
  title?: string;
}

export const Section = ({ title, className = "", ...props }: SectionProps) => (
  <Card className={className} {...props}>
    {title && <h2 className="text-lg font-semibold mb-4">{title}</h2>}
    {props.children}
  </Card>
);
