import type { Currency } from '../types/order';

/**
 * Currency formatting utilities
 */

// Currency symbols and locales
const currencyConfig: Record<Currency, { symbol: string; locale: string }> = {
  EUR: { symbol: '€', locale: 'de-DE' },
  CZK: { symbol: 'Kč', locale: 'cs-CZ' },
  USD: { symbol: '$', locale: 'en-US' },
};

/**
 * Format currency amount with proper symbol and locale
 */
export function formatCurrency(amount: number, currency: Currency): string {
  const config = currencyConfig[currency];
  if (!config) {
    return `${amount} ${currency}`;
  }

  try {
    return new Intl.NumberFormat(config.locale, {
      style: 'currency',
      currency: currency,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(amount);
  } catch {
    // Fallback formatting if Intl.NumberFormat fails
    return `${config.symbol}${amount.toFixed(2)}`;
  }
}

/**
 * Format currency amount as plain number with currency suffix
 */
export function formatCurrencyPlain(amount: number, currency: Currency): string {
  return `${amount.toFixed(2)} ${currency}`;
}

/**
 * Get currency symbol only
 */
export function getCurrencySymbol(currency: Currency): string {
  return currencyConfig[currency]?.symbol || currency;
}

/**
 * Parse currency string to number
 */
export function parseCurrency(value: string, currency: Currency): number {
  const config = currencyConfig[currency];
  if (!config) {
    return parseFloat(value) || 0;
  }

  // Remove currency symbols and spaces, then parse
  const cleanValue = value
    .replace(new RegExp(`[${config.symbol}\\s]`, 'g'), '')
    .replace(',', '.');
  
  return parseFloat(cleanValue) || 0;
}

/**
 * Calculate total with tax
 */
export function calculateTotalWithTax(subtotal: number, taxRate: number): number {
  return subtotal * (1 + taxRate / 100);
}

/**
 * Calculate tax amount
 */
export function calculateTaxAmount(subtotal: number, taxRate: number): number {
  return subtotal * (taxRate / 100);
}

/**
 * Format percentage
 */
export function formatPercentage(value: number): string {
  return `${value.toFixed(1)}%`;
}