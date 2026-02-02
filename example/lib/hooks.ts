/**
 * Custom React hooks for common patterns
 */

import { useState, useCallback } from "react";
import { getErrorMessage } from "./api";

/**
 * Hook for async operations with loading/error states
 */
export function useAsync<T, E = string>(
  asyncFunction: () => Promise<T>,
  immediate: boolean = true
) {
  const [state, setState] = useState<{
    data: T | null;
    loading: boolean;
    error: E | null;
  }>({
    data: null,
    loading: immediate,
    error: null,
  });

  const execute = useCallback(async () => {
    setState({ data: null, loading: true, error: null });
    try {
      const response = await asyncFunction();
      setState({ data: response, loading: false, error: null });
      return response;
    } catch (error: any) {
      const errorMessage = getErrorMessage(error);
      setState({ data: null, loading: false, error: errorMessage as E });
      throw error;
    }
  }, [asyncFunction]);

  return { ...state, execute };
}

/**
 * Hook for form state management
 */
export function useForm<T extends Record<string, any>>(initialValues: T) {
  const [values, setValues] = useState(initialValues);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [touched, setTouched] = useState<Record<string, boolean>>({});

  const handleChange = useCallback(
    (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
      const { name, value, type } = e.target;
      const fieldValue = type === "checkbox" ? (e.target as HTMLInputElement).checked : value;

      setValues((prev) => ({
        ...prev,
        [name]: fieldValue,
      }));

      if (touched[name]) {
        setErrors((prev) => {
          const newErrors = { ...prev };
          delete newErrors[name];
          return newErrors;
        });
      }
    },
    [touched]
  );

  const handleBlur = useCallback((e: React.FocusEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name } = e.target;
    setTouched((prev) => ({
      ...prev,
      [name]: true,
    }));
  }, []);

  const setFieldError = useCallback((name: string, error: string) => {
    setErrors((prev) => ({
      ...prev,
      [name]: error,
    }));
  }, []);

  const setFieldValue = useCallback((name: string, value: any) => {
    setValues((prev) => ({
      ...prev,
      [name]: value,
    }));
  }, []);

  const resetForm = useCallback(() => {
    setValues(initialValues);
    setErrors({});
    setTouched({});
  }, [initialValues]);

  return {
    values,
    errors,
    touched,
    handleChange,
    handleBlur,
    setFieldError,
    setFieldValue,
    resetForm,
    setValues,
  };
}

/**
 * Hook for managing loading states
 */
export function useLoading(initial: boolean = false) {
  const [loading, setLoading] = useState(initial);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<any>(null);

  const execute = useCallback(
    async (asyncFn: () => Promise<any>) => {
      setLoading(true);
      setError(null);
      setSuccess(null);
      try {
        const result = await asyncFn();
        setSuccess(result);
        return result;
      } catch (err: any) {
        const errorMessage = getErrorMessage(err);
        setError(errorMessage);
        throw err;
      } finally {
        setLoading(false);
      }
    },
    []
  );

  return { loading, error, success, execute, setError, setSuccess, setLoading };
}

/**
 * Hook for tab management
 */
export function useTab(initial: string) {
  const [activeTab, setActiveTab] = useState(initial);

  const selectTab = useCallback((tabId: string) => {
    setActiveTab(tabId);
  }, []);

  return { activeTab, selectTab };
}

/**
 * Hook for modal management
 */
export function useModal(initial: boolean = false) {
  const [isOpen, setIsOpen] = useState(initial);

  const open = useCallback(() => setIsOpen(true), []);
  const close = useCallback(() => setIsOpen(false), []);
  const toggle = useCallback(() => setIsOpen((prev) => !prev), []);

  return { isOpen, open, close, toggle };
}

/**
 * Hook for authentication
 */
export function useAuth() {
  const [isAuth, setIsAuth] = useState(false);
  const [password, setPassword] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const login = useCallback(
    async (password: string, onLogin: (password: string) => Promise<boolean>) => {
      setLoading(true);
      setError(null);
      try {
        const success = await onLogin(password);
        if (success) {
          setPassword(password);
          setIsAuth(true);
        } else {
          setError("Неверный пароль");
        }
      } catch (err: any) {
        setError(getErrorMessage(err));
      } finally {
        setLoading(false);
      }
    },
    []
  );

  const logout = useCallback(() => {
    setIsAuth(false);
    setPassword("");
    setError(null);
  }, []);

  return { isAuth, password, error, loading, login, logout };
}

/**
 * Hook for paginated data
 */
export function usePagination<T>(items: T[], itemsPerPage: number = 10) {
  const [currentPage, setCurrentPage] = useState(1);

  const totalPages = Math.ceil(items.length / itemsPerPage);
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const currentItems = items.slice(startIndex, endIndex);

  const goToPage = useCallback((page: number) => {
    const validPage = Math.max(1, Math.min(page, totalPages));
    setCurrentPage(validPage);
  }, [totalPages]);

  const nextPage = useCallback(() => goToPage(currentPage + 1), [currentPage, goToPage]);
  const prevPage = useCallback(() => goToPage(currentPage - 1), [currentPage, goToPage]);

  return {
    currentItems,
    currentPage,
    totalPages,
    goToPage,
    nextPage,
    prevPage,
  };
}

/**
 * Hook for filtering and searching
 */
export function useSearch<T>(items: T[], searchKey: keyof T) {
  const [searchTerm, setSearchTerm] = useState("");

  const filteredItems = items.filter((item) => {
    const value = String(item[searchKey]).toLowerCase();
    return value.includes(searchTerm.toLowerCase());
  });

  const clearSearch = useCallback(() => setSearchTerm(""), []);

  return {
    searchTerm,
    setSearchTerm,
    filteredItems,
    clearSearch,
  };
}
